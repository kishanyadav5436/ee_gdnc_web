<?php

namespace Drupal\ee_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Controller for EE Events REST API and Admin UI.
 *
 * API endpoints:
 *   GET /api/events             — list all published events
 *   GET /api/events/{id}        — single event by ID
 *
 * Query params for list:
 *   ?upcoming=1     — only future events (start_date > now)
 *   ?type=workshop  — filter by event_type
 *   ?featured=1     — only featured events
 *   ?limit=10       — max results (default 20, max 100)
 */
class EeEventsController extends ControllerBase {

  private function json(mixed $data, int $status = 200): JsonResponse {
    $response = new JsonResponse($data, $status);
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    return $response;
  }

  private function formatEvent(object $row): array {
    return [
      'id'               => (int) $row->id,
      'title'            => $row->title,
      'description'      => $row->description,
      'event_type'       => $row->event_type,
      'start_date'       => (int) $row->start_date,
      'start_date_human' => date('d M Y', $row->start_date),
      'end_date'         => (int) $row->end_date,
      'end_date_human'   => $row->end_date ? date('d M Y', $row->end_date) : NULL,
      'venue'            => $row->venue,
      'organizer'        => $row->organizer,
      'registration_url' => $row->registration_url,
      'image_url'        => $row->image_url,
      'is_featured'      => (bool) $row->is_featured,
      'is_upcoming'      => $row->start_date > \Drupal::time()->getRequestTime(),
    ];
  }

  // ── Public JSON API ─────────────────────────────────────────────────────────

  /**
   * GET /api/events
   */
  public function list(Request $request): JsonResponse {
    $db    = Database::getConnection();
    $now   = \Drupal::time()->getRequestTime();
    $query = $db->select('ee_events', 'e')
      ->fields('e')
      ->condition('e.status', 1)
      ->orderBy('e.start_date', 'ASC');

    // Upcoming filter
    if ($request->query->get('upcoming') === '1') {
      $query->condition('e.start_date', $now, '>');
    }

    // Type filter
    $validTypes = ['workshop', 'seminar', 'webinar', 'competition', 'cultural', 'technical', 'other'];
    $type = $request->query->get('type');
    if ($type && in_array($type, $validTypes)) {
      $query->condition('e.event_type', $type);
    }

    // Featured filter
    if ($request->query->get('featured') === '1') {
      $query->condition('e.is_featured', 1);
    }

    $limit = min((int) ($request->query->get('limit', 20)), 100);
    $query->range(0, $limit);

    $rows   = $query->execute()->fetchAll();
    $events = array_map([$this, 'formatEvent'], $rows);

    return $this->json([
      'status' => 'ok',
      'count'  => count($events),
      'events' => $events,
    ]);
  }

  /**
   * GET /api/events/{id}
   */
  public function single(int $id, Request $request): JsonResponse {
    $row = Database::getConnection()
      ->select('ee_events', 'e')
      ->fields('e')
      ->condition('e.id', $id)
      ->condition('e.status', 1)
      ->execute()
      ->fetchObject();

    if (!$row) {
      return $this->json(['status' => 'error', 'message' => 'Event not found.'], 404);
    }
    return $this->json(['status' => 'ok', 'event' => $this->formatEvent($row)]);
  }

  // ── Admin UI ────────────────────────────────────────────────────────────────

  /**
   * Admin listing page.
   */
  public function adminList(): array {
    $rows = Database::getConnection()
      ->select('ee_events', 'e')
      ->fields('e')
      ->orderBy('e.start_date', 'DESC')
      ->execute()
      ->fetchAll();

    $header = ['ID', 'Title', 'Type', 'Start Date', 'Venue', 'Featured', 'Status', 'Operations'];

    $tableRows = [];
    foreach ($rows as $row) {
      $tableRows[] = [
        $row->id,
        $row->title,
        ucfirst($row->event_type),
        date('d M Y', $row->start_date),
        $row->venue ?: '—',
        $row->is_featured ? '⭐ Yes' : 'No',
        $row->status ? '✅ Published' : '⏸ Draft',
        [
          'data' => [
            '#type'  => 'operations',
            '#links' => [
              'edit'   => ['title' => $this->t('Edit'),   'url' => Url::fromRoute('ee_events.admin.edit',   ['id' => $row->id])],
              'delete' => ['title' => $this->t('Delete'), 'url' => Url::fromRoute('ee_events.admin.delete', ['id' => $row->id])],
            ],
          ],
        ],
      ];
    }

    $build['add_link'] = [
      '#type'  => 'link',
      '#title' => $this->t('+ Add Event'),
      '#url'   => Url::fromRoute('ee_events.admin.add'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];
    $build['table'] = [
      '#type'   => 'table',
      '#header' => $header,
      '#rows'   => $tableRows,
      '#empty'  => $this->t('No events found. Add one above.'),
    ];

    return $build;
  }

  /**
   * Delete an event.
   */
  public function delete(int $id, Request $request): RedirectResponse {
    Database::getConnection()->delete('ee_events')->condition('id', $id)->execute();
    $this->messenger()->addStatus($this->t('Event #@id deleted.', ['@id' => $id]));
    return new RedirectResponse(Url::fromRoute('ee_events.admin.list')->toString());
  }

}

<?php

namespace Drupal\ee_notices\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Controller for EE Notices REST API and Admin UI.
 *
 * API endpoints:
 *   GET /api/notices              — list all published notices
 *   GET /api/notices/{id}         — single notice by ID
 *
 * Admin routes:
 *   /admin/ee-gndec/notices       — list & manage notices
 *   /admin/ee-gndec/notices/{id}/delete — delete a notice
 */
class EeNoticesController extends ControllerBase {

  // ── Helpers ────────────────────────────────────────────────────────────────

  /**
   * Returns a JsonResponse with CORS headers for dev convenience.
   */
  private function json(mixed $data, int $status = 200): JsonResponse {
    $response = new JsonResponse($data, $status);
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    return $response;
  }

  /**
   * Fetch a single notice row from DB.
   */
  private function fetchOne(int $id): ?object {
    return Database::getConnection()
      ->select('ee_notices', 'n')
      ->fields('n')
      ->condition('n.id', $id)
      ->condition('n.status', 1)
      ->execute()
      ->fetchObject();
  }

  /**
   * Format a DB row as a clean API array.
   */
  private function formatNotice(object $row): array {
    return [
      'id'             => (int) $row->id,
      'title'          => $row->title,
      'body'           => $row->body,
      'category'       => $row->category,
      'attachment_url' => $row->attachment_url,
      'is_pinned'      => (bool) $row->is_pinned,
      'created'        => (int) $row->created,
      'created_human'  => date('d M Y', $row->created),
      'expires'        => (int) $row->expires,
    ];
  }

  // ── Public JSON API ─────────────────────────────────────────────────────────

  /**
   * GET /api/notices
   *
   * Query params:
   *   ?category=exam|event|general|result|urgent
   *   ?limit=10   (default 20, max 100)
   *   ?pinned=1   (only pinned notices)
   */
  public function list(Request $request): JsonResponse {
    $db    = Database::getConnection();
    $query = $db->select('ee_notices', 'n')
      ->fields('n')
      ->condition('n.status', 1)
      ->orderBy('n.is_pinned', 'DESC')
      ->orderBy('n.created', 'DESC');

    // Optional category filter
    $category = $request->query->get('category');
    if ($category && in_array($category, ['exam', 'event', 'general', 'result', 'urgent'])) {
      $query->condition('n.category', $category);
    }

    // Pinned only
    if ($request->query->get('pinned') === '1') {
      $query->condition('n.is_pinned', 1);
    }

    // Expire filter — exclude expired notices (expires > 0 and expires < now)
    $now = \Drupal::time()->getRequestTime();
    $orGroup = $query->orConditionGroup()
      ->condition('n.expires', 0)
      ->condition('n.expires', $now, '>');
    $query->condition($orGroup);

    // Limit
    $limit = min((int) ($request->query->get('limit', 20)), 100);
    $query->range(0, $limit);

    $rows = $query->execute()->fetchAll();

    $notices = array_map([$this, 'formatNotice'], $rows);

    return $this->json([
      'status'  => 'ok',
      'count'   => count($notices),
      'notices' => $notices,
    ]);
  }

  /**
   * GET /api/notices/{id}
   */
  public function single(int $id, Request $request): JsonResponse {
    $row = $this->fetchOne($id);
    if (!$row) {
      return $this->json(['status' => 'error', 'message' => 'Notice not found.'], 404);
    }
    return $this->json(['status' => 'ok', 'notice' => $this->formatNotice($row)]);
  }

  // ── Admin UI ────────────────────────────────────────────────────────────────

  /**
   * Admin listing page for all notices.
   */
  public function adminList(): array {
    $db   = Database::getConnection();
    $rows = $db->select('ee_notices', 'n')
      ->fields('n')
      ->orderBy('n.created', 'DESC')
      ->execute()
      ->fetchAll();

    $header = [
      'ID', 'Title', 'Category', 'Pinned', 'Status', 'Created', 'Operations',
    ];

    $tableRows = [];
    foreach ($rows as $row) {
      $tableRows[] = [
        $row->id,
        $row->title,
        ucfirst($row->category),
        $row->is_pinned ? '📌 Yes' : 'No',
        $row->status ? '✅ Published' : '⏸ Draft',
        date('d M Y', $row->created),
        [
          'data' => [
            '#type'  => 'operations',
            '#links' => [
              'edit' => [
                'title' => $this->t('Edit'),
                'url'   => Url::fromRoute('ee_notices.admin.edit', ['id' => $row->id]),
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url'   => Url::fromRoute('ee_notices.admin.delete', ['id' => $row->id]),
              ],
            ],
          ],
        ],
      ];
    }

    $build['add_link'] = [
      '#type'  => 'link',
      '#title' => $this->t('+ Add Notice'),
      '#url'   => Url::fromRoute('ee_notices.admin.add'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];

    $build['table'] = [
      '#type'   => 'table',
      '#header' => $header,
      '#rows'   => $tableRows,
      '#empty'  => $this->t('No notices found. Add one above.'),
    ];

    return $build;
  }

  /**
   * Delete a notice by ID and redirect back to admin list.
   */
  public function delete(int $id, Request $request): RedirectResponse {
    Database::getConnection()
      ->delete('ee_notices')
      ->condition('id', $id)
      ->execute();

    $this->messenger()->addStatus($this->t('Notice #@id has been deleted.', ['@id' => $id]));
    return new RedirectResponse(Url::fromRoute('ee_notices.admin.list')->toString());
  }

}

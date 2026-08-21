<?php

namespace Drupal\ee_labs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Controller for EE Labs & Facilities REST API and Admin UI.
 *
 * API endpoints:
 *   GET /api/labs            — list all active labs
 *   GET /api/labs/{id}       — single lab by ID
 *
 * Query params for list:
 *   ?type=teaching|research|computing|other
 *   ?limit=20
 */
class EeLabsController extends ControllerBase {

  private function json(mixed $data, int $status = 200): JsonResponse {
    $response = new JsonResponse($data, $status);
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    return $response;
  }

  private function formatLab(object $row): array {
    return [
      'id'               => (int) $row->id,
      'name'             => $row->name,
      'lab_code'         => $row->lab_code,
      'description'      => $row->description,
      'lab_type'         => $row->lab_type,
      'location'         => $row->location,
      'area_sqft'        => $row->area_sqft ? (float) $row->area_sqft : NULL,
      'capacity'         => $row->capacity ? (int) $row->capacity : NULL,
      'incharge'         => $row->incharge,
      'major_equipment'  => $row->major_equipment,
      'equipment_list'   => $row->major_equipment
        ? array_map('trim', explode(',', $row->major_equipment)) : [],
      'image_url'        => $row->image_url,
      'established_year' => $row->established_year ? (int) $row->established_year : NULL,
    ];
  }

  // ── Public API ──────────────────────────────────────────────────────────────

  /**
   * GET /api/labs
   */
  public function list(Request $request): JsonResponse {
    $db    = Database::getConnection();
    $query = $db->select('ee_labs', 'l')
      ->fields('l')
      ->condition('l.status', 1)
      ->orderBy('l.sort_order', 'ASC')
      ->orderBy('l.name', 'ASC');

    $validTypes = ['teaching', 'research', 'computing', 'other'];
    $type = $request->query->get('type');
    if ($type && in_array($type, $validTypes)) {
      $query->condition('l.lab_type', $type);
    }

    $limit = min((int) ($request->query->get('limit', 20)), 100);
    $query->range(0, $limit);

    $rows = $query->execute()->fetchAll();
    $labs = array_map([$this, 'formatLab'], $rows);

    return $this->json([
      'status' => 'ok',
      'count'  => count($labs),
      'labs'   => $labs,
    ]);
  }

  /**
   * GET /api/labs/{id}
   */
  public function single(int $id, Request $request): JsonResponse {
    $row = Database::getConnection()
      ->select('ee_labs', 'l')->fields('l')
      ->condition('l.id', $id)->condition('l.status', 1)
      ->execute()->fetchObject();

    if (!$row) {
      return $this->json(['status' => 'error', 'message' => 'Lab not found.'], 404);
    }
    return $this->json(['status' => 'ok', 'lab' => $this->formatLab($row)]);
  }

  // ── Admin UI ────────────────────────────────────────────────────────────────

  /**
   * Admin listing page.
   */
  public function adminList(): array {
    $rows = Database::getConnection()
      ->select('ee_labs', 'l')->fields('l')
      ->orderBy('l.sort_order', 'ASC')->execute()->fetchAll();

    $header = ['ID', 'Code', 'Lab Name', 'Type', 'Incharge', 'Capacity', 'Status', 'Operations'];

    $tableRows = [];
    foreach ($rows as $row) {
      $tableRows[] = [
        $row->id,
        $row->lab_code ?: '—',
        $row->name,
        ucfirst($row->lab_type),
        $row->incharge ?: '—',
        $row->capacity ? $row->capacity . ' students' : '—',
        $row->status ? '✅ Active' : '⏸ Inactive',
        [
          'data' => [
            '#type'  => 'operations',
            '#links' => [
              'edit'   => ['title' => $this->t('Edit'),   'url' => Url::fromRoute('ee_labs.admin.edit',   ['id' => $row->id])],
              'delete' => ['title' => $this->t('Delete'), 'url' => Url::fromRoute('ee_labs.admin.delete', ['id' => $row->id])],
            ],
          ],
        ],
      ];
    }

    $build['add_link'] = [
      '#type'  => 'link',
      '#title' => $this->t('+ Add Lab'),
      '#url'   => Url::fromRoute('ee_labs.admin.add'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];
    $build['table'] = [
      '#type'   => 'table',
      '#header' => $header,
      '#rows'   => $tableRows,
      '#empty'  => $this->t('No labs found. Add one above.'),
    ];

    return $build;
  }

  /**
   * Delete a lab.
   */
  public function delete(int $id, Request $request): RedirectResponse {
    Database::getConnection()->delete('ee_labs')->condition('id', $id)->execute();
    $this->messenger()->addStatus($this->t('Lab #@id deleted.', ['@id' => $id]));
    return new RedirectResponse(Url::fromRoute('ee_labs.admin.list')->toString());
  }

}

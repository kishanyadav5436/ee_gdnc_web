<?php

namespace Drupal\ee_research\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Controller for EE Research & Publications REST API and Admin UI.
 *
 * API endpoints:
 *   GET /api/research            — list publications
 *   GET /api/research/{id}       — single publication
 *
 * Query params:
 *   ?type=journal|conference|book_chapter|patent|project
 *   ?year=2024
 *   ?featured=1
 *   ?limit=20
 */
class EeResearchController extends ControllerBase {

  private function json(mixed $data, int $status = 200): JsonResponse {
    $response = new JsonResponse($data, $status);
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    return $response;
  }

  private function formatPublication(object $row): array {
    return [
      'id'               => (int) $row->id,
      'title'            => $row->title,
      'authors'          => $row->authors,
      'authors_list'     => $row->authors ? array_map('trim', explode(',', $row->authors)) : [],
      'abstract'         => $row->abstract,
      'publication_type' => $row->publication_type,
      'journal_name'     => $row->journal_name,
      'volume'           => $row->volume,
      'issue'            => $row->issue,
      'pages'            => $row->pages,
      'year'             => (int) $row->year,
      'doi'              => $row->doi,
      'doi_url'          => $row->doi ? 'https://doi.org/' . $row->doi : NULL,
      'url'              => $row->url,
      'keywords'         => $row->keywords,
      'keywords_list'    => $row->keywords ? array_map('trim', explode(',', $row->keywords)) : [],
      'is_featured'      => (bool) $row->is_featured,
      'citation'         => $this->buildCitation($row),
    ];
  }

  /**
   * Build a formatted citation string.
   */
  private function buildCitation(object $row): string {
    $parts = [];
    if ($row->authors) {
      $parts[] = $row->authors . '.';
    }
    $parts[] = '"' . $row->title . '."';
    if ($row->journal_name) {
      $parts[] = '<em>' . $row->journal_name . '</em>';
    }
    if ($row->volume) {
      $vol = $row->volume;
      if ($row->issue) {
        $vol .= '.' . $row->issue;
      }
      $parts[] = $vol;
    }
    $parts[] = '(' . $row->year . ')';
    if ($row->pages) {
      $parts[] = ': ' . $row->pages . '.';
    }
    if ($row->doi) {
      $parts[] = 'DOI: ' . $row->doi;
    }
    return implode(' ', $parts);
  }

  // ── Public API ──────────────────────────────────────────────────────────────

  /**
   * GET /api/research
   */
  public function list(Request $request): JsonResponse {
    $db    = Database::getConnection();
    $query = $db->select('ee_research', 'r')
      ->fields('r')
      ->condition('r.status', 1)
      ->orderBy('r.year', 'DESC')
      ->orderBy('r.created', 'DESC');

    $validTypes = ['journal', 'conference', 'book_chapter', 'patent', 'project'];
    $type = $request->query->get('type');
    if ($type && in_array($type, $validTypes)) {
      $query->condition('r.publication_type', $type);
    }

    $year = (int) $request->query->get('year');
    if ($year > 1990 && $year <= (int) date('Y') + 1) {
      $query->condition('r.year', $year);
    }

    if ($request->query->get('featured') === '1') {
      $query->condition('r.is_featured', 1);
    }

    $limit = min((int) ($request->query->get('limit', 20)), 100);
    $query->range(0, $limit);

    $rows = $query->execute()->fetchAll();
    $publications = array_map([$this, 'formatPublication'], $rows);

    return $this->json([
      'status'       => 'ok',
      'count'        => count($publications),
      'publications' => $publications,
    ]);
  }

  /**
   * GET /api/research/{id}
   */
  public function single(int $id, Request $request): JsonResponse {
    $row = Database::getConnection()
      ->select('ee_research', 'r')->fields('r')
      ->condition('r.id', $id)->condition('r.status', 1)
      ->execute()->fetchObject();

    if (!$row) {
      return $this->json(['status' => 'error', 'message' => 'Publication not found.'], 404);
    }
    return $this->json(['status' => 'ok', 'publication' => $this->formatPublication($row)]);
  }

  // ── Admin UI ────────────────────────────────────────────────────────────────

  /**
   * Admin listing page.
   */
  public function adminList(): array {
    $rows = Database::getConnection()
      ->select('ee_research', 'r')->fields('r')
      ->orderBy('r.year', 'DESC')->execute()->fetchAll();

    $header = ['ID', 'Title', 'Authors', 'Type', 'Year', 'Featured', 'Status', 'Operations'];

    $tableRows = [];
    foreach ($rows as $row) {
      $tableRows[] = [
        $row->id,
        strlen($row->title) > 60 ? substr($row->title, 0, 60) . '…' : $row->title,
        $row->authors ?: '—',
        ucfirst(str_replace('_', ' ', $row->publication_type)),
        $row->year,
        $row->is_featured ? '⭐ Yes' : 'No',
        $row->status ? '✅ Published' : '⏸ Draft',
        [
          'data' => [
            '#type'  => 'operations',
            '#links' => [
              'edit'   => ['title' => $this->t('Edit'),   'url' => Url::fromRoute('ee_research.admin.edit',   ['id' => $row->id])],
              'delete' => ['title' => $this->t('Delete'), 'url' => Url::fromRoute('ee_research.admin.delete', ['id' => $row->id])],
            ],
          ],
        ],
      ];
    }

    $build['add_link'] = [
      '#type'  => 'link',
      '#title' => $this->t('+ Add Publication'),
      '#url'   => Url::fromRoute('ee_research.admin.add'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];
    $build['table'] = [
      '#type'   => 'table',
      '#header' => $header,
      '#rows'   => $tableRows,
      '#empty'  => $this->t('No publications found. Add one above.'),
    ];

    return $build;
  }

  /**
   * Delete a publication.
   */
  public function delete(int $id, Request $request): RedirectResponse {
    Database::getConnection()->delete('ee_research')->condition('id', $id)->execute();
    $this->messenger()->addStatus($this->t('Publication #@id deleted.', ['@id' => $id]));
    return new RedirectResponse(Url::fromRoute('ee_research.admin.list')->toString());
  }

}

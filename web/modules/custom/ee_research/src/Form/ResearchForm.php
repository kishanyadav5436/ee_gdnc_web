<?php

namespace Drupal\ee_research\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

/**
 * Admin form to Add or Edit a Research Publication.
 */
class ResearchForm extends FormBase {

  public function getFormId(): string {
    return 'ee_research_research_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, int $id = NULL): array {
    $pub = NULL;
    if ($id) {
      $pub = Database::getConnection()
        ->select('ee_research', 'r')->fields('r')
        ->condition('r.id', $id)->execute()->fetchObject();
    }
    $form_state->set('research_id', $id);

    $form['title'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Paper / Publication Title'),
      '#required'      => TRUE,
      '#maxlength'     => 1024,
      '#default_value' => $pub ? $pub->title : '',
    ];

    $form['authors'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Authors (comma-separated)'),
      '#maxlength'     => 1024,
      '#default_value' => $pub ? $pub->authors : '',
      '#placeholder'   => 'Dr. A. Kumar, Prof. B. Singh, Er. C. Kaur',
    ];

    $form['abstract'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Abstract'),
      '#rows'          => 6,
      '#default_value' => $pub ? $pub->abstract : '',
    ];

    $form['publication_type'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Publication Type'),
      '#required'      => TRUE,
      '#options'       => [
        'journal'      => $this->t('Journal Article'),
        'conference'   => $this->t('Conference Paper'),
        'book_chapter' => $this->t('Book Chapter'),
        'patent'       => $this->t('Patent'),
        'project'      => $this->t('Research Project'),
      ],
      '#default_value' => $pub ? $pub->publication_type : 'journal',
    ];

    $form['journal_name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Journal / Conference / Publisher Name'),
      '#maxlength'     => 512,
      '#default_value' => $pub ? $pub->journal_name : '',
    ];

    $form['details'] = ['#type' => 'fieldset', '#title' => $this->t('Publication Details')];

    $form['details']['volume'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Volume'),
      '#size'          => 10,
      '#default_value' => $pub ? $pub->volume : '',
    ];

    $form['details']['issue'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Issue'),
      '#size'          => 10,
      '#default_value' => $pub ? $pub->issue : '',
    ];

    $form['details']['pages'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Pages'),
      '#size'          => 15,
      '#default_value' => $pub ? $pub->pages : '',
      '#placeholder'   => '123-131',
    ];

    $form['details']['year'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Year'),
      '#required'      => TRUE,
      '#min'           => 1990,
      '#max'           => (int) date('Y') + 2,
      '#default_value' => $pub ? (int) $pub->year : (int) date('Y'),
    ];

    $form['doi'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('DOI (Digital Object Identifier)'),
      '#maxlength'     => 256,
      '#default_value' => $pub ? $pub->doi : '',
      '#placeholder'   => '10.1016/j.example.2024.108898',
    ];

    $form['url'] = [
      '#type'          => 'url',
      '#title'         => $this->t('External Link / PDF URL (optional)'),
      '#maxlength'     => 1024,
      '#default_value' => $pub ? $pub->url : '',
    ];

    $form['keywords'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Keywords (comma-separated)'),
      '#maxlength'     => 512,
      '#default_value' => $pub ? $pub->keywords : '',
      '#placeholder'   => 'power systems, machine learning, renewable energy',
    ];

    $form['is_featured'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Feature this publication on the homepage'),
      '#default_value' => $pub ? $pub->is_featured : 0,
    ];

    $form['status'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Status'),
      '#required'      => TRUE,
      '#options'       => [1 => $this->t('Published'), 0 => $this->t('Draft')],
      '#default_value' => $pub ? (int) $pub->status : 1,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type'  => 'submit',
      '#value' => $id ? $this->t('Update Publication') : $this->t('Save Publication'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];
    $form['actions']['cancel'] = [
      '#type'  => 'link',
      '#title' => $this->t('Cancel'),
      '#url'   => Url::fromRoute('ee_research.admin.list'),
      '#attributes' => ['class' => ['button']],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $db  = Database::getConnection();
    $id  = $form_state->get('research_id');
    $now = \Drupal::time()->getRequestTime();

    $fields = [
      'title'            => $form_state->getValue('title'),
      'authors'          => $form_state->getValue('authors') ?: NULL,
      'abstract'         => $form_state->getValue('abstract') ?: NULL,
      'publication_type' => $form_state->getValue('publication_type'),
      'journal_name'     => $form_state->getValue('journal_name') ?: NULL,
      'volume'           => $form_state->getValue(['details', 'volume']) ?: NULL,
      'issue'            => $form_state->getValue(['details', 'issue']) ?: NULL,
      'pages'            => $form_state->getValue(['details', 'pages']) ?: NULL,
      'year'             => (int) $form_state->getValue(['details', 'year']),
      'doi'              => $form_state->getValue('doi') ?: NULL,
      'url'              => $form_state->getValue('url') ?: NULL,
      'keywords'         => $form_state->getValue('keywords') ?: NULL,
      'is_featured'      => (int) $form_state->getValue('is_featured'),
      'status'           => (int) $form_state->getValue('status'),
      'uid'              => (int) \Drupal::currentUser()->id(),
    ];

    if ($id) {
      $db->update('ee_research')->fields($fields)->condition('id', $id)->execute();
      $this->messenger()->addStatus($this->t('Publication updated successfully.'));
    }
    else {
      $fields['created'] = $now;
      $db->insert('ee_research')->fields($fields)->execute();
      $this->messenger()->addStatus($this->t('Publication added successfully.'));
    }

    $form_state->setRedirectUrl(Url::fromRoute('ee_research.admin.list'));
  }

}

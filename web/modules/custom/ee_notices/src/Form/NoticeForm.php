<?php

namespace Drupal\ee_notices\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Admin form to Add or Edit a Notice.
 *
 * Used at:
 *   /admin/ee-gndec/notices/add
 *   /admin/ee-gndec/notices/{id}/edit
 */
class NoticeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ee_notices_notice_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $id = NULL): array {
    // If an ID is given, load existing record for editing.
    $notice = NULL;
    if ($id) {
      $notice = Database::getConnection()
        ->select('ee_notices', 'n')
        ->fields('n')
        ->condition('n.id', $id)
        ->execute()
        ->fetchObject();
    }

    // Store the ID in form state for use in submitForm.
    $form_state->set('notice_id', $id);

    $form['title'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Notice Title'),
      '#required'      => TRUE,
      '#maxlength'     => 512,
      '#default_value' => $notice ? $notice->title : '',
      '#placeholder'   => 'e.g. Mid-Semester Examination Schedule',
    ];

    $form['body'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Notice Body'),
      '#rows'          => 6,
      '#default_value' => $notice ? $notice->body : '',
      '#placeholder'   => 'Full text of the notice…',
    ];

    $form['category'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Category'),
      '#required'      => TRUE,
      '#options'       => [
        'general' => $this->t('General'),
        'exam'    => $this->t('Examination'),
        'event'   => $this->t('Event'),
        'result'  => $this->t('Result'),
        'urgent'  => $this->t('Urgent'),
      ],
      '#default_value' => $notice ? $notice->category : 'general',
    ];

    $form['attachment_url'] = [
      '#type'          => 'url',
      '#title'         => $this->t('Attachment URL (optional)'),
      '#maxlength'     => 1024,
      '#default_value' => $notice ? $notice->attachment_url : '',
      '#placeholder'   => 'https://…/notice.pdf',
    ];

    $form['is_pinned'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Pin this notice to the top'),
      '#default_value' => $notice ? $notice->is_pinned : 0,
    ];

    $form['status'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Status'),
      '#required'      => TRUE,
      '#options'       => [
        1 => $this->t('Published'),
        0 => $this->t('Draft'),
      ],
      '#default_value' => $notice ? (int) $notice->status : 1,
    ];

    $form['expires'] = [
      '#type'          => 'date',
      '#title'         => $this->t('Expiry Date (optional)'),
      '#description'   => $this->t('Leave blank for notices that never expire.'),
      '#default_value' => ($notice && $notice->expires)
        ? date('Y-m-d', $notice->expires) : '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type'  => 'submit',
      '#value' => $id ? $this->t('Update Notice') : $this->t('Save Notice'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];
    $form['actions']['cancel'] = [
      '#type'       => 'link',
      '#title'      => $this->t('Cancel'),
      '#url'        => Url::fromRoute('ee_notices.admin.list'),
      '#attributes' => ['class' => ['button']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $db  = Database::getConnection();
    $id  = $form_state->get('notice_id');
    $now = \Drupal::time()->getRequestTime();

    $expiresRaw = $form_state->getValue('expires');
    $expires    = $expiresRaw ? strtotime($expiresRaw) : 0;

    $fields = [
      'title'          => $form_state->getValue('title'),
      'body'           => $form_state->getValue('body'),
      'category'       => $form_state->getValue('category'),
      'attachment_url' => $form_state->getValue('attachment_url') ?: NULL,
      'is_pinned'      => (int) $form_state->getValue('is_pinned'),
      'status'         => (int) $form_state->getValue('status'),
      'expires'        => $expires,
      'uid'            => (int) \Drupal::currentUser()->id(),
    ];

    if ($id) {
      // UPDATE existing notice
      $db->update('ee_notices')
        ->fields($fields)
        ->condition('id', $id)
        ->execute();
      $this->messenger()->addStatus($this->t('Notice updated successfully.'));
    }
    else {
      // INSERT new notice
      $fields['created'] = $now;
      $db->insert('ee_notices')->fields($fields)->execute();
      $this->messenger()->addStatus($this->t('Notice added successfully.'));
    }

    $form_state->setRedirectUrl(Url::fromRoute('ee_notices.admin.list'));
  }

}

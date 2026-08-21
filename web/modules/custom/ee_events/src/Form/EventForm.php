<?php

namespace Drupal\ee_events\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Admin form to Add or Edit an Event.
 */
class EventForm extends FormBase {

  public function getFormId(): string {
    return 'ee_events_event_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, int $id = NULL): array {
    $event = NULL;
    if ($id) {
      $event = Database::getConnection()
        ->select('ee_events', 'e')->fields('e')
        ->condition('e.id', $id)->execute()->fetchObject();
    }
    $form_state->set('event_id', $id);

    $form['title'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Event Title'),
      '#required'      => TRUE,
      '#maxlength'     => 512,
      '#default_value' => $event ? $event->title : '',
    ];

    $form['description'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Description'),
      '#rows'          => 5,
      '#default_value' => $event ? $event->description : '',
    ];

    $form['event_type'] = [
      '#type'    => 'select',
      '#title'   => $this->t('Event Type'),
      '#required' => TRUE,
      '#options' => [
        'workshop'    => $this->t('Workshop'),
        'seminar'     => $this->t('Seminar'),
        'webinar'     => $this->t('Webinar'),
        'competition' => $this->t('Competition'),
        'cultural'    => $this->t('Cultural'),
        'technical'   => $this->t('Technical'),
        'other'       => $this->t('Other'),
      ],
      '#default_value' => $event ? $event->event_type : 'other',
    ];

    $form['dates'] = ['#type' => 'fieldset', '#title' => $this->t('Date & Time')];

    $form['dates']['start_date'] = [
      '#type'          => 'date',
      '#title'         => $this->t('Start Date'),
      '#required'      => TRUE,
      '#default_value' => ($event && $event->start_date) ? date('Y-m-d', $event->start_date) : '',
    ];

    $form['dates']['end_date'] = [
      '#type'          => 'date',
      '#title'         => $this->t('End Date (optional)'),
      '#default_value' => ($event && $event->end_date) ? date('Y-m-d', $event->end_date) : '',
    ];

    $form['venue'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Venue'),
      '#maxlength'     => 512,
      '#default_value' => $event ? $event->venue : '',
    ];

    $form['organizer'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Organizer'),
      '#maxlength'     => 256,
      '#default_value' => $event ? $event->organizer : 'EE Department, GNDEC',
    ];

    $form['registration_url'] = [
      '#type'          => 'url',
      '#title'         => $this->t('Registration URL (optional)'),
      '#maxlength'     => 1024,
      '#default_value' => $event ? $event->registration_url : '',
    ];

    $form['image_url'] = [
      '#type'          => 'url',
      '#title'         => $this->t('Banner / Poster Image URL (optional)'),
      '#maxlength'     => 1024,
      '#default_value' => $event ? $event->image_url : '',
    ];

    $form['is_featured'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Feature this event on the homepage'),
      '#default_value' => $event ? $event->is_featured : 0,
    ];

    $form['status'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Status'),
      '#required'      => TRUE,
      '#options'       => [1 => $this->t('Published'), 0 => $this->t('Draft')],
      '#default_value' => $event ? (int) $event->status : 1,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type'  => 'submit',
      '#value' => $id ? $this->t('Update Event') : $this->t('Save Event'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];
    $form['actions']['cancel'] = [
      '#type'  => 'link',
      '#title' => $this->t('Cancel'),
      '#url'   => Url::fromRoute('ee_events.admin.list'),
      '#attributes' => ['class' => ['button']],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $db  = Database::getConnection();
    $id  = $form_state->get('event_id');
    $now = \Drupal::time()->getRequestTime();

    $startRaw = $form_state->getValue(['dates', 'start_date']);
    $endRaw   = $form_state->getValue(['dates', 'end_date']);

    $fields = [
      'title'            => $form_state->getValue('title'),
      'description'      => $form_state->getValue('description'),
      'event_type'       => $form_state->getValue('event_type'),
      'start_date'       => $startRaw ? strtotime($startRaw) : 0,
      'end_date'         => $endRaw   ? strtotime($endRaw)   : 0,
      'venue'            => $form_state->getValue('venue') ?: NULL,
      'organizer'        => $form_state->getValue('organizer') ?: 'EE Department, GNDEC',
      'registration_url' => $form_state->getValue('registration_url') ?: NULL,
      'image_url'        => $form_state->getValue('image_url') ?: NULL,
      'is_featured'      => (int) $form_state->getValue('is_featured'),
      'status'           => (int) $form_state->getValue('status'),
      'uid'              => (int) \Drupal::currentUser()->id(),
    ];

    if ($id) {
      $db->update('ee_events')->fields($fields)->condition('id', $id)->execute();
      $this->messenger()->addStatus($this->t('Event updated successfully.'));
    }
    else {
      $fields['created'] = $now;
      $db->insert('ee_events')->fields($fields)->execute();
      $this->messenger()->addStatus($this->t('Event added successfully.'));
    }

    $form_state->setRedirectUrl(Url::fromRoute('ee_events.admin.list'));
  }

}

<?php

namespace Drupal\ee_labs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

/**
 * Admin form to Add or Edit a Lab.
 */
class LabForm extends FormBase {

  public function getFormId(): string {
    return 'ee_labs_lab_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, int $id = NULL): array {
    $lab = NULL;
    if ($id) {
      $lab = Database::getConnection()
        ->select('ee_labs', 'l')->fields('l')
        ->condition('l.id', $id)->execute()->fetchObject();
    }
    $form_state->set('lab_id', $id);

    $form['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Laboratory Name'),
      '#required'      => TRUE,
      '#maxlength'     => 512,
      '#default_value' => $lab ? $lab->name : '',
      '#placeholder'   => 'e.g. Power Systems Laboratory',
    ];

    $form['lab_code'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Lab Code'),
      '#maxlength'     => 64,
      '#default_value' => $lab ? $lab->lab_code : '',
      '#placeholder'   => 'e.g. EE-LAB-01',
    ];

    $form['description'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Description'),
      '#rows'          => 5,
      '#default_value' => $lab ? $lab->description : '',
    ];

    $form['lab_type'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Lab Type'),
      '#required'      => TRUE,
      '#options'       => [
        'teaching'  => $this->t('Teaching Lab'),
        'research'  => $this->t('Research Lab'),
        'computing' => $this->t('Computing Lab'),
        'other'     => $this->t('Other'),
      ],
      '#default_value' => $lab ? $lab->lab_type : 'teaching',
    ];

    $form['location'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Location / Room No.'),
      '#maxlength'     => 256,
      '#default_value' => $lab ? $lab->location : '',
      '#placeholder'   => 'e.g. Ground Floor, EE Block',
    ];

    $form['specs'] = ['#type' => 'fieldset', '#title' => $this->t('Specifications')];

    $form['specs']['area_sqft'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Area (sq. ft.)'),
      '#step'          => 1,
      '#min'           => 0,
      '#default_value' => $lab ? $lab->area_sqft : '',
    ];

    $form['specs']['capacity'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Student Capacity'),
      '#min'           => 1,
      '#default_value' => $lab ? $lab->capacity : '',
    ];

    $form['specs']['established_year'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Year Established'),
      '#min'           => 1960,
      '#max'           => (int) date('Y'),
      '#default_value' => $lab ? $lab->established_year : '',
    ];

    $form['incharge'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Lab Incharge'),
      '#maxlength'     => 256,
      '#default_value' => $lab ? $lab->incharge : '',
    ];

    $form['major_equipment'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Major Equipment (comma-separated)'),
      '#rows'          => 4,
      '#default_value' => $lab ? $lab->major_equipment : '',
      '#placeholder'   => 'DSO Oscilloscope, Function Generator, Power Supply…',
    ];

    $form['image_url'] = [
      '#type'          => 'url',
      '#title'         => $this->t('Lab Photo URL (optional)'),
      '#maxlength'     => 1024,
      '#default_value' => $lab ? $lab->image_url : '',
    ];

    $form['sort_order'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Sort Order'),
      '#min'           => 0,
      '#default_value' => $lab ? $lab->sort_order : 0,
      '#description'   => $this->t('Lower number appears first in listings.'),
    ];

    $form['status'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Status'),
      '#required'      => TRUE,
      '#options'       => [1 => $this->t('Active'), 0 => $this->t('Inactive')],
      '#default_value' => $lab ? (int) $lab->status : 1,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type'  => 'submit',
      '#value' => $id ? $this->t('Update Lab') : $this->t('Save Lab'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];
    $form['actions']['cancel'] = [
      '#type'  => 'link',
      '#title' => $this->t('Cancel'),
      '#url'   => Url::fromRoute('ee_labs.admin.list'),
      '#attributes' => ['class' => ['button']],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $db  = Database::getConnection();
    $id  = $form_state->get('lab_id');
    $now = \Drupal::time()->getRequestTime();

    $fields = [
      'name'             => $form_state->getValue('name'),
      'lab_code'         => $form_state->getValue('lab_code') ?: NULL,
      'description'      => $form_state->getValue('description') ?: NULL,
      'lab_type'         => $form_state->getValue('lab_type'),
      'location'         => $form_state->getValue('location') ?: NULL,
      'area_sqft'        => $form_state->getValue(['specs', 'area_sqft']) ?: NULL,
      'capacity'         => $form_state->getValue(['specs', 'capacity']) ?: NULL,
      'established_year' => $form_state->getValue(['specs', 'established_year']) ?: NULL,
      'incharge'         => $form_state->getValue('incharge') ?: NULL,
      'major_equipment'  => $form_state->getValue('major_equipment') ?: NULL,
      'image_url'        => $form_state->getValue('image_url') ?: NULL,
      'sort_order'       => (int) $form_state->getValue('sort_order'),
      'status'           => (int) $form_state->getValue('status'),
      'uid'              => (int) \Drupal::currentUser()->id(),
    ];

    if ($id) {
      $db->update('ee_labs')->fields($fields)->condition('id', $id)->execute();
      $this->messenger()->addStatus($this->t('Lab updated successfully.'));
    }
    else {
      $fields['created'] = $now;
      $db->insert('ee_labs')->fields($fields)->execute();
      $this->messenger()->addStatus($this->t('Lab added successfully.'));
    }

    $form_state->setRedirectUrl(Url::fromRoute('ee_labs.admin.list'));
  }

}

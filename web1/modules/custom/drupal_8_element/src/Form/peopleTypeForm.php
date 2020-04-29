<?php

namespace Drupal\drupal_8_element\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class peopleTypeForm.
 */
class peopleTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $people_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $people_type->label(),
      '#description' => $this->t("Label for the People type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $people_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\drupal_8_element\Entity\peopleType::load',
      ],
      '#disabled' => !$people_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $people_type = $this->entity;
    $status = $people_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label People type.', [
          '%label' => $people_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label People type.', [
          '%label' => $people_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($people_type->toUrl('collection'));
  }

}

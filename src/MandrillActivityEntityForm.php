<?php
namespace Drupal\mandrill;

/**
 * Class MandrillActivityEntityForm.
 *
 * @package Drupal\mandrill_activity\Form
 */
class MandrillActivityEntityForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $mandrill_activity_entity = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $mandrill_activity_entity->label(),
      '#description' => $this->t("Label for the Mandrill activity entity."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $mandrill_activity_entity->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\mandrill_activity\Entity\MandrillActivityEntity::load',
      ),
      '#disabled' => !$mandrill_activity_entity->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $mandrill_activity_entity = $this->entity;
    $status = $mandrill_activity_entity->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label Mandrill activity entity.', array(
        '%label' => $mandrill_activity_entity->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label Mandrill activity entity was not saved.', array(
        '%label' => $mandrill_activity_entity->label(),
      )));
    }
    $form_state->setRedirectUrl($mandrill_activity_entity->urlInfo('collection'));
  }

}

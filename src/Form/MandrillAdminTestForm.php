<?php
/**
 * @file
 * Contains \Drupal\mandrill\Form\MandrillAdminTestForm
 */

namespace Drupal\mandrill\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Mandrill send test email form.
 *
 * @ingroup mandrill
 */
class MandrillAdminTestForm extends ConfirmFormBase {

  function getFormID() {
    return 'mandrill_test_email';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Send test email?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This action will send a test email through Mandrill.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('mandrill.admin');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Send test email');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (mailchimp_campaign_send_campaign($this->entity)) {
      drupal_set_message($this->t('Test email has been sent.'));
    }

    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}

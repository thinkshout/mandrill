<?php
/**
 * @file
 * Contains \Drupal\mandrill\Form\MandrillAdminTestForm
 */

namespace Drupal\mandrill\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mandrill\Plugin\Mail\MandrillMail;

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
    return $this->t('Send Test Email');
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
    return new Url('mandrill.test');
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $click_tracking_url = Url::fromUri('http://www.drupal.org/project/mandrill');

    $form['mandrill_test_address'] = array(
      '#type' => 'textfield',
      '#title' => t('Email address to send a test email to'),
      '#default_value' => \Drupal::config('system.site')->get('mail'),
      '#description' => t('Type in an address to have a test email sent there.'),
      '#required' => TRUE,
    );
    $form['mandrill_test_body'] = array(
      '#type' => 'textarea',
      '#title' => t('Test body contents'),
      '#default_value' => t('If you receive this message it means your site is capable of using Mandrill to send email. This url is here to test click tracking: %link',
        array('%link' => \Drupal::l(t('link'), $click_tracking_url))),
    );
    $form['include_attachment'] = array(
      '#title' => t('Include attachment'),
      '#type' => 'checkbox',
      '#description' => t('If checked, the Drupal icon will be included as an attachment with the test email.'),
      '#default_value' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $message = array(
      'to' => $form_state->getValue('mandrill_test_address'),
      'text' => $form_state->getValue('mandrill_test_body'),
      'subject' => t('Drupal Mandrill test email'),
    );

    if ($form_state->getValue('include_attachment')) {
      $message['attachments'][] = \Drupal::service('file_system')->realpath('core/themes/bartik/logo.svg');
      $message['text'] .= ' ' . t('The Drupal icon is included as an attachment to test the attachment functionality.');
    }

    /* @var $mandrill \Drupal\mandrill\Plugin\Mail\MandrillMail */
    $mailer = new MandrillMail();

    if ($mailer->mail($message)) {
      drupal_set_message($this->t('Test email has been sent.'));
    }
  }
}

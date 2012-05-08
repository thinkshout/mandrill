<?php

/**
 * @file
 * Implements Mandrill as a Drupal MailSystemInterface
 */

/**
 * Modify the drupal mail system to use Mandrill when sending emails.
 */
class MandrillMailSystem implements MailSystemInterface {

  /**
   * Concatenate and wrap the email body for either
   * plain-text or HTML emails.
   *
   * @param $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return
   *   The formatted $message.
   */
  public function format(array$message) {
    // Join the body array into one string.
    if (is_array($message['body'])) {
      $message['body'] = implode("\n\n", $message['body']);
    }
    return $message;
  }

  /**
   * Send the email message.
   *
   * @see drupal_mail()
   *
   * @param $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return
   *   TRUE if the mail was successfully accepted, otherwise FALSE.
   */
  public function mail(array $message) {
    if (!$from = variable_get('mandrill_from', '')) {
      drupal_set_message(t('Mandrill can\'t send email. Please !link.',
          array('!link' => l('add a verified from address',
              'admin/config/services/mandrill'
            ))
        ), 'error');
      return FALSE;
    }

    // apply input format to body
    $html = $message['body'];
    $format = variable_get('mandrill_filter_format', '');
    if (!empty($format)) {
      $html = check_markup($message['body'], $format);
    }

    $to = mandrill_get_to($message['to']);
    $mandrill_message = array(
      'html' => $html,
      'text' => drupal_html_to_text($message['body']),
      'subject' => $message['subject'],
      'from_name' => variable_get('mandrill_from_name', ''),
      'from_email' => $from,
      'to' => $to,
      // optional extra headers to add to the message (currently only Reply-To and X-* headers are allowed)
      'headers' => $message['headers'],
      'track_opens' => TRUE,
      'track_clicks' => TRUE,
      'auto_text' => TRUE,
      'url_strip_qs' => TRUE,
      'bcc_address' => isset($message['bcc_email']) ? $message['bcc_email'] : NULL,
      'tags' => array($message['id']),
      'google_analytics_domains' => array(),
      'google_analytics_campaign' => array(),
    );

    // send the email passing the message id as the tag for use in reporting
    $mailer = mandrill_get_api_object();

    try{
      $result = $mailer->messages_send($mandrill_message);
      // @todo: look for rejected messages and log
      return TRUE;
    }
    catch (Mandrill_Exception $e) {
      watchdog('mandrill', 'Error sending email from %from to %to. @code: @message',
        array(
          '%from' => $from, '%to' => print_r($to, TRUE),
          '@code' => $e->getCode(), '@message' => $e->getMessage(),
        ),
        WATCHDOG_ERROR
      );

      return FALSE;
    }
  }

}

/**
 * A mail sending implementation that captures sent messages to watchdog.
 *
 * This class is for running tests or for development.
 */
class TestingMandrillMailSystem extends MandrillMailSystem implements MailSystemInterface {

  /**
   * Accept an e-mail message and store it in a variable.
   *
   * @param $message
   *   An e-mail message.
   */
  public function mail(array$message) {
    if (!$from = variable_get('mandrill_from', '')) {
      drupal_set_message(t('Mandrill can\'t send email. Please !link.',
          array('!link' => l('add a verified from address',
              'admin/config/services/mailchimp/sts'
            ))
        ), 'error');
      return FALSE;
    }
    $to = mandrill_get_to($message['to']);

    watchdog('mandrill', 'Mandrill test email sent from %from to %to. Message: @message',
      array(
        '%from' => $from, '%to' => print_r($to),
        '@message' => print_r($message),
      ),
      WATCHDOG_NOTICE
    );
    drupal_set_message(t('Mandrill test email sent from %from to %to.',
        array('%from' => $from, '%to' => print_r($to))
      ), 'notice');
    return TRUE;
  }
}

/**
 * Helper to generate an array of recipients.
 *
 * @param mixed $to
 *
 * @return array
 */
function mandrill_get_to($to) {
  //@todo: extract name and add to associative array.

  $recipients = array();
  if ($to_array = explode(',', $to)) {
    foreach ($to_array as $email) {
      $recipients[] = array('email' => $email);
    }
  }
  else {
    $recipients[] = array('email' => $to);
  }
  return $recipients;
}


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
   * Concatenate and wrap the email body for either plain-text or HTML emails.
   *
   * @param array $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return array
   *   The formatted $message.
   */
  public function format(array $message) {
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
   * @param array $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return bool
   *   TRUE if the mail was successfully accepted, otherwise FALSE.
   */
  public function mail(array $message) {
    // Send the email passing the message id as the tag for use in reporting.
    if (!$from = variable_get('mandrill_from', variable_get('site_mail'))) {
      drupal_set_message(t('Mandrill can\'t send email. Please !link.',
        array(
          '!link' => l('add a verified from address',
            'admin/config/services/mandrill'
          )
        )
      ), 'error');
      return FALSE;
    }
    $mailer = mandrill_get_api_object();
    // Apply input format to body.
    $html = $message['body'];
    $format = variable_get('mandrill_filter_format', '');
    if (!empty($format)) {
      $html = check_markup($message['body'], $format);
    }
    $to = mandrill_get_to($message['to']);
    $headers = isset($message['headers']) ? $message['headers'] : NULL;
    if (!empty($message['from']) && empty($headers['Reply-To'])) {
      $headers['Reply-To'] = $message['from'];
    }
    $attachments = array();
    if (isset($message['attachments']) && !empty($message['attachments'])) {
      foreach ($message['attachments'] as $attachment) {
        if (is_file($attachment)) {
          $attachments[] = $mailer->getAttachmentStruct($attachment);
        }
      }
    }

    // Determine if content should be available for this message.
    $blacklisted_keys = explode(',', mandrill_mail_key_blacklist());
    $view_content = TRUE;
    foreach ($blacklisted_keys as $key) {
      if ($message['id'] == drupal_strtolower(trim($key))) {
        $view_content = FALSE;
        break;
      }
    }    
    $mandrill_message = array(
      'html' => $html,
      'text' => drupal_html_to_text($message['body']),
      'subject' => $message['subject'],
      'from_email' =>$from,
      'from_name' => variable_get('mandrill_from_name', ''),
      'to' => $to,
      // Optional extra headers to add to the message
      // (Currently only Reply-To and X-* headers are allowed).
      'headers' => $headers,
      'track_opens' => variable_get('mandrill_track_opens', TRUE),
      'track_clicks' => variable_get('mandrill_track_clicks', TRUE),
      // We're handling this with drupal_html_to_text().
      'auto_text' => FALSE,
      'url_strip_qs' => variable_get('mandrill_url_strip_qs', FALSE),
      'bcc_address' => isset($message['bcc_email']) ? $message['bcc_email'] : NULL,
      'tags' => array($message['id']),
      'google_analytics_domains' => (variable_get('mandrill_analytics_domains', NULL)) ? explode(',', variable_get('mandrill_analytics_domains')) : array(),
      'google_analytics_campaign' => variable_get('mandrill_analytics_campaign', ''),
      'attachments' => $attachments,
      'view_content_link' => $view_content,
      // Now we set some default values that are used by the mandrill module
      // and which can be altered using the hook system. These indexes should
      // be stripped out of the message before it is handed off to the Mandrill API.
      'send_function' => 'mandrill_sender_plain',
      'args' => NULL,
    );
    
    // Allow other modules to alter the message content or send_function
    drupal_alter('mandrill_mail', $mandrill_message, $message);

    $status = NULL;
    if (mandrill_process_async()) {
      $queue = DrupalQueue::get(MANDRILL_QUEUE, TRUE);
      $queue->createItem($mandrill_message);
      $status = 'queued';
    }
    else {
      if (mandrill_mailsend($mandrill_message)) {
        $status = 'sent';
      }
      else {
        $status = 'error';
      }
    }
    switch ($status) {
      case "sent":

        return TRUE;

      case "queued":
        watchdog('mandrill', 'Message from %from to %to queued for delivery.', array('%from' => $from, '%to' => $to[0]['email'], WATCHDOG_NOTICE));
          
        return TRUE;

      case "error":
        watchdog('mandrill', 'Message from %from to %to has failed.', array('%from' => $from, '%to' => $to[0]['email'], WATCHDOG_ERROR));
        break;
    }

    return FALSE;
  }
}

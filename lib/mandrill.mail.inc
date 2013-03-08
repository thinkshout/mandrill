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
    $mailer = mandrill_get_api_object();

    // Apply input format to body.
    $html = $message['body'];
    $format = variable_get('mandrill_filter_format', '');
    if (!empty($format)) {
      $html = check_markup($message['body'], $format);
    }

    $to = mandrill_get_to($message['to']);
    $from = variable_get('site_mail', ini_get('sendmail_from'));
    if (!empty($message['from'])) {
      $from = $message['from'];
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
      'from_email' => $from,
      'to' => $to,
      // Optional extra headers to add to the message
      // (Currently only Reply-To and X-* headers are allowed).
      'headers' => $message['headers'],
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
    );

    // Allow other modules to alter the message.
    drupal_alter('mandrill_mail', $mandrill_message, $message);

    $template_map = mandrill_template_map_load_entity_by_key($message['module']);
    $template_args = NULL;
    if ($template_map) {
      $template_args = $template_map->message_send_arguments($mandrill_message);
    }
    $status = NULL;
    if (mandrill_process_async()) {
      $sendable_item = array(
        'message' => $mandrill_message,
        'template_args' => $template_args,
      );
      $queue = DrupalQueue::get(MANDRILL_QUEUE, TRUE);
      $queue->createItem($sendable_item);
      $status = 'queued';
    }
    else {
      if (mandrill_mailsend($mandrill_message, $template_args, $mailer)) {
        $status = 'sent';
      }
      else {
        $status = 'error';
      }
    }
    switch ($status) {
      case "sent":
        drupal_set_message(t('Mandrill test email sent from %from to %to.',
          array('%from' => $from, '%to' => $to[0]['email'])
        ), 'status');

        return TRUE;

      case "queued":
        drupal_set_message(t('Mandrill test email from %from to %to has been queued for delivery.',
          array('%from' => $from, '%to' => $to[0]['email'])
        ), 'status');

        return TRUE;

      case "error":
        drupal_set_message(t('Mandrill test email from %from to %to has failed. Check the logs for details.',
          array('%from' => $from, '%to' => $to[0]['email'])
        ), 'error');
        break;
    }

    return FALSE;
  }
}

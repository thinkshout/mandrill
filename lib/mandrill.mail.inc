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
   * @param $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return
   *   TRUE if the mail was successfully accepted, otherwise FALSE.
   */
  public function mail(array $message) {
    if (!$from = variable_get('mandrill_from', '')) {
      drupal_set_message(t('Mandrill can\'t send email. Please !link.',
        array(
          '!link' => l(t('add a verified from address'),
            'admin/config/services/mandrill'
          )
        )
      ), 'error');
      return FALSE;
    }

    // send the email passing the message id as the tag for use in reporting
    $mailer = mandrill_get_api_object();

    // apply input format to body
    $html = $message['body'];
    $format = variable_get('mandrill_filter_format', '');
    if (!empty($format)) {
      $html = check_markup($message['body'], $format);
    }

    $to = mandrill_get_to($message['to']);

    $attachments = array();
    if (isset($message['attachments']) && !empty($message['attachments'])) {
      foreach ($message['attachments'] as $attachment) {
        if (is_file($attachment)) {
          $attachments[] = $mailer->getAttachmentStruct($attachment);
        }
      }
    }

    // determine if content should be available for this message
    $blacklisted_keys = explode(',', mandrill_mail_key_blacklist());
    $view_content = TRUE;
    foreach ($blacklisted_keys as $key) {
      if  ($message['id'] == drupal_strtolower(trim($key))) {
        $view_content = FALSE;
        break;
      }
    }
    if (variable_get('mandrill_reply_to', FALSE) && empty($message['headers']['Reply-To'])) {
      $message['headers']['Reply-To'] = $message['from'];
    }
    $mandrill_message = array(
      'html' => $html,
      'text' => drupal_html_to_text($message['body']),
      'subject' => $message['subject'],
      'from_name' => variable_get('mandrill_from_name', ''),
      'from_email' => $from,
      'to' => $to,
      // optional extra headers to add to the message (currently only Reply-To and X-* headers are allowed)
      'headers' => $message['headers'],
      'track_opens' => variable_get('mandrill_track_opens', TRUE),
      'track_clicks' => variable_get('mandrill_track_clicks', TRUE),
      // we're handling this with drupal_html_to_text().
      'auto_text' => FALSE,
      'url_strip_qs' => variable_get('mandrill_url_strip_qs', FALSE),
      'bcc_address' => isset($message['bcc_email']) ? $message['bcc_email'] : NULL,
      'tags' => array($message['id']),
      'google_analytics_domains' => (variable_get('mandrill_analytics_domains', NULL)) ? explode(',', variable_get('mandrill_analytics_domains')) : array(),
      'google_analytics_campaign' => variable_get('mandrill_analytics_campaign', ''),
      'attachments' => $attachments,
      'view_content_link' => $view_content
    );
    // todo: document
    drupal_alter('mandrill_mail', $mandrill_message, $message);
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'mandrill_template_map')
      ->propertyCondition('mailsystem_key', $message['module'])
      ->addMetaData('account', user_load(1)); //todo: not sure if this is necessary
    $query_result = $query->execute();
    $template_map = NULL;
    if (!empty($query_result['mandrill_template_map'])) {
      $maps = entity_load('mandrill_template_map', array_keys($query_result['mandrill_template_map']));
      $template_map = array_pop($maps);
    }
    try {
      $result = NULL;
      if (!is_null($template_map)) {
        $result = $template_map->send($mandrill_message, $mailer);
      }
      else {
        $result = $mailer->messages_send($mandrill_message);
      }
      // @todo: look for rejected messages and log
      if ($message['key'] == 'mandrill_test') {
        watchdog('mandrill', 'Mandrill test email sent from %from to %to. Message: <pre>@message</pre>',
          array(
            '%from' => $from, '%to' => $to[0]['email'],
            '@message' => print_r($message, TRUE),
          ),
          WATCHDOG_NOTICE
        );
        drupal_set_message(t('Mandrill test email sent from %from to %to.',
                              array('%from' => $from, '%to' => $to[0]['email'])
                            ), 'notice');
      }
      return TRUE;
    }
    catch (Mandrill_Exception $e) {
      watchdog('mandrill', 'Error sending email from %from to %to. @code: @message',
        array(
          '%from' => $from,
          '%to' => $message['to'],
          '@code' => $e->getCode(),
          '@message' => $e->getMessage(),
        ),
        WATCHDOG_ERROR
      );

      return FALSE;
    }
  }

}


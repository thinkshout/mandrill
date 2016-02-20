<?php

/**
 * @file
 * Contains \Drupal\mandrill\Plugin\Mail\MandrillMail.
 */

namespace Drupal\mandrill\Plugin\Mail;

use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Mail\MailFormatHelper;

/**
 * Modify the Drupal mail system to use Mandrill when sending emails.
 *
 * @Mail(
 *   id = "mandrill_mail",
 *   label = @Translation("Mandrill mailer"),
 *   description = @Translation("Sends the message through Mandrill.")
 * )
 */
class MandrillMail implements MailInterface {

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The Mandrill service.
   *
   * @var \Drupal\mandrill\MandrillService
   */
  protected $mandrill;

  /**
   * The Logger Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $log;

  /**
   * The MIME Type Guesser service.
   *
   * @var \Drupal\Core\File\MimeType\MimeTypeGuesser
   */
  protected $mimeTypeGuesser;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->config = \Drupal::service('config.factory')->get('mandrill.settings');
    $this->mandrill = \Drupal::service('mandrill.service');
    $this->log = \Drupal::service('logger.factory')->get('mandrill');
    $this->mimeTypeGuesser = \Drupal::service('file.mime_type.guesser');
  }
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
    // Optionally log mail keys not using Mandrill already. Helpful in
    // configuring Mandrill.
    if ($this->config->get('mandrill_log_defaulted_sends')) {
      $registered = FALSE;
      foreach ($this->mandrill->getMailSystems() as $key => $system) {
        if ($message['id'] == $key) {
          $registered = TRUE;
        }
      }
      if (!$registered) {
        $this->log->notice("Module: %module Key: %key invoked Mandrill to send email because Mandrill is configured as the default mail system. Specify alternate configuration for this module & key in %mailsystem if this is not desirable.",
          [
            '%module' => $message['module'],
            '%key' => $message['id'],
            '%mailsystem' => \Drupal::l(t('Mail System'), Url::fromRoute('mailsystem.settings')),
          ]
        );
      }
    }
    // Apply input format to body.
    $format = $this->config->get('mandrill_filter_format');
    if (!empty($format)) {
      $message['body'] = check_markup($message['body'], $format);
    }
    // Prepare headers, defaulting the reply-to to the from address since
    // Mandrill needs the from address to be configured separately.
    // Note that only Reply-To and X-* headers are allowed.
    $headers = isset($message['headers']) ? $message['headers'] : array();
    if (isset($message['params']['mandrill']['header'])) {
      $headers = $message['params']['mandrill']['header'] + $headers;
    }
    if (!empty($message['from']) && empty($headers['Reply-To'])) {
      $headers['Reply-To'] = $message['from'];
    }
    // Prepare attachments.
    $attachments = array();
    if (isset($message['attachments']) && !empty($message['attachments'])) {
      foreach ($message['attachments'] as $attachment) {
        if (is_file($attachment)) {
          $attachments[] = $this->getAttachmentStruct($attachment);
        }
      }
    }
    // Determine if content should be available for this message.
    $blacklisted_keys = explode(',', $this->config->get('mandrill_mail_key_blacklist'));
    $view_content = TRUE;
    foreach ($blacklisted_keys as $key) {
      if ($message['id'] == Unicode::strtolower(trim($key))) {
        $view_content = FALSE;
        break;
      }
    }
    // The Mime Mail module (mimemail) expects attachments as an array of file
    // arrays in $message['params']['attachments']. As many modules assume you
    // will be using Mime Mail to handle attachments, we need to parse this
    // array as well.
    if (isset($message['params']['attachments']) && !empty($message['params']['attachments'])) {
      foreach ($message['params']['attachments'] as $attachment) {
        if (isset($attachment['uri'])) {
          $attachment_path = \Drupal::service('file_system')->realpath($attachment['uri']);
          if (is_file($attachment_path)) {
            $struct = $this->getAttachmentStruct($attachment_path);
            // Allow for customised filenames.
            if (!empty($attachment['filename'])) {
              $struct['name'] = $attachment['filename'];
            }
            $attachments[] = $struct;
          }
        }
        // Support attachments that are directly included without a file in the
        // filesystem.
        elseif (isset($attachment['filecontent'])) {
          $attachments[] = array(
            'type' => $attachment['filemime'],
            'name' => $attachment['filename'],
            'content' => chunk_split(base64_encode($attachment['filecontent']), 76, "\n"),
          );
        }
      }
      // Remove the file objects from $message['params']['attachments'].
      // (This prevents double-attaching in the drupal_alter hook below.)
      unset($message['params']['attachments']);
    }
    // Extract an array of recipients.
    $to = $this->mandrill->getReceivers($message['to']);
    // Account for the plaintext parameter provided by the mimemail module.
    $plain_text = empty($message['params']['plaintext']) ? MailFormatHelper::htmlToText($message['body']) : $message['params']['plaintext'];
    // Get metadata.
    $metadata = isset($message['metadata']) ? $message['metadata'] : array();
    $from = array(
      'email' => $this->config->get('from_email'),
      'name' => $this->config->get('from_name'),
    );
    $overrides = isset($message['params']['mandrill']['overrides']) ? $message['params']['mandrill']['overrides'] : array();
    $mandrill_message = $overrides + array(
        'id' => $message['id'],
        'module' => $message['module'],
        'html' => $message['body'],
        'text' => $plain_text,
        'subject' => $message['subject'],
        'from_email' => $from['email'],
        'from_name' => isset($message['params']['mandrill']['from_name']) ? $message['params']['mandrill']['from_name'] : $from['name'],
        'to' => $to,
        'headers' => $headers,
        'track_opens' => $this->config->get('mandrill_track_opens'),
        'track_clicks' => $this->config->get('mandrill_track_clicks'),
        // We're handling this with htmlToText.
        'auto_text' => FALSE,
        'url_strip_qs' => $this->config->get('mandrill_url_strip_qs'),
        'bcc_address' => isset($message['bcc_email']) ? $message['bcc_email'] : NULL,
        'tags' => array($message['id']),
        'google_analytics_domains' => ($this->config->get('mandrill_analytics_domains')) ? explode(',', $this->config->get('mandrill_analytics_domains')) : array(),
        'google_analytics_campaign' => $this->config->get('mandrill_analytics_campaign'),
        'attachments' => $attachments,
        'view_content_link' => $view_content,
        'metadata' => $metadata,
      );
    $subaccount = $this->config->get('mandrill_subaccount');
    if ($subaccount) {
      $mandrill_message['subaccount'] = $subaccount;
    }
    // Allow other modules to alter the Mandrill message, and sender/args.
    $mandrill_params = array(
      'message' => $mandrill_message,
      'function' => 'mandrill_sender_plain',
      'args' => array(),
    );
    \Drupal::moduleHandler()->alter('mandrill_mail', $mandrill_params, $message);
    // Queue for processing during cron or send immediately.
    $status = NULL;
    if ($this->config->get('mandrill_process_async')) {
      $queue = \Drupal::Queue(MANDRILL_QUEUE, TRUE);
      $queue->createItem($mandrill_params);
      if ($this->config->get('mandrill_batch_log_queued')) {
        $this->log->notice('Message from %from to %to queued for delivery.', array(
          '%from' => $from['email'],
          '%to' => $to[0]['email'],
        ));
      }
      return TRUE;
    }
    else {
      return $this->mandrill->send($mandrill_params['message'], $mandrill_params['function'], $mandrill_params['args']);
    }
  }

  /**
   * Return an array structure for a message attachment.
   *
   * @param string $path
   *   Attachment path.
   *
   * @return array
   *   Attachment structure.
   *
   * @throws \Exception
   */
  public function getAttachmentStruct($path) {
    $struct = array();
    if (!@is_file($path)) {
      throw new \Exception($path . ' is not a valid file.');
    }
    $filename = basename($path);
    $file_buffer = file_get_contents($path);
    $file_buffer = chunk_split(base64_encode($file_buffer), 76, "\n");
    $mime_type = $this->mimeTypeGuesser->guess($path);
    if (!$this->isValidContentType($mime_type)) {
      throw new \Exception($mime_type . ' is not a valid content type.');
    }
    $struct['type'] = $mime_type;
    $struct['name'] = $filename;
    $struct['content'] = $file_buffer;
    return $struct;
  }

  /**
   * Helper to determine if an attachment is valid.
   *
   * @param string $file_type
   *   The file mime type.
   *
   * @return bool
   *   True or false.
   */
  protected function isValidContentType($file_type) {
    $valid_types = array(
      'image/',
      'text/',
      'application/pdf',
      'application/x-zip',
    );
    \Drupal::moduleHandler()->alter('mandrill_valid_attachment_types', $valid_types);
    foreach ($valid_types as $vct) {
      if (strpos($file_type, $vct) !== FALSE) {
        return TRUE;
      }
    }
    return FALSE;
  }
}

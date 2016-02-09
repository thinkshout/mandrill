<?php

/**
 * @file
 * Contains \Drupal\mandrill\MandrillService.
 */

namespace Drupal\mandrill;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Service class.
 */
class MandrillService implements MandrillServiceInterface {
  /**
   * Constructs the service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->config = $config_factory;
    $this->log = $logger_factory->get('mandrill');
  }

  /**
   * Check if the Mandrill PHP library is available.
   *
   * @return bool
   *   TRUE if it is installed, FALSE otherwise.
   */
  public function isLibraryInstalled() {
    $settings = $this->config->get('mandrill.settings');
    $className = $this->config->get('mandrill.settings')->get('mandrill_api_classname');
    return class_exists($className);
  }

  /**
   * Get the mail systems defined in the mail system module.
   *
   * @return array
   *   Array of mail systems and keys
   *   - key Either the module-key or default for site wide system.
   *   - sender The class to use for sending mail.
   *   - formatter The class to use for formatting mail.
   */
  public function getMailSystems() {
    $systems = [];
    // Check if the system wide sender or formatter is Mandrill.
    $mailSystemConfig = $this->config->get('mailsystem.settings');
    $systems[] = [
      'key' => 'default',
      'sender' => $mailSystemConfig->get('defaults')['sender'],
      'formatter' => $mailSystemConfig->get('defaults')['formatter'],
    ];
    // Check all custom configured modules if any uses Mandrill.
    $modules = $mailSystemConfig->get('modules') ?: [];
    foreach ($modules as $module => $configuration) {
      foreach ($configuration as $key => $settings) {
        $systems[] = [
          'key' => "$module-$key",
          'sender' => $settings['sender'],
          'formatter' => $settings['formatter'],
        ];
      }
    }
    return $systems;
  }

  /**
   * Helper to generate an array of recipients.
   *
   * @param mixed $receiver
   *   a comma delimited list of email addresses in 1 of 2 forms:
   *   user@domain.com
   *   any number of names <user@domain.com>
   *
   * @return array
   *   array of email addresses
   */
  public function getReceivers($receiver) {
    $recipients = array();
    $receiver_array = explode(',', $receiver);
    foreach ($receiver_array as $email) {
      if (preg_match(MANDRILL_EMAIL_REGEX, $email, $matches)) {
        $recipients[] = array(
          'email' => $matches[2],
          'name' => $matches[1],
        );
      }
      else {
        $recipients[] = array('email' => $email);
      }
    }
    return $recipients;
  }

  /**
   * Abstracts sending of messages, allowing queueing option.
   *
   * @param array $message
   *   A message array formatted for Mandrill's sending API, plus 2 additional
   *   indexes for the send_function and an array of $args, if needed by the send
   *   function.
   * @param string $function
   *   The name of the function to use to send the message.
   * @param array $args
   *   Array of arguments to pass to the function provided by $function.
   *
   * @return bool
   *   TRUE if no exception thrown
   */
  public function send($message, $function, array $args = array()) {
    try {
      if (!function_exists($function)) {
        $this->log->error('Error sending email from %from to %to. Function %function not found.', array(
          '%from' => $message['from_email'],
          '%to' => $message['to'],
          '%function' => $function,
        ));
        return FALSE;
      }
      $params = array($message) + $args;
      $response = call_user_func_array($function, $params);
      if (!isset($response['status'])) {
        foreach ($response as $result) {
          // Allow other modules to react based on a send result.
          \Drupal::moduleHandler()->invokeAll('mandrill_mailsend_result', [$result]);
          switch ($result['status']) {
            case "error":
            case "invalid":
            case "rejected":
              if (!$this->config->get('mandrill.settings')->get('mandrill_test_mode')) {
                $to = isset($result['email']) ? $result['email'] : 'recipient';
                $status = isset($result['status']) ? $result['status'] : 'message';
                $error_message = isset($result['message']) ? $result['message'] : 'no message';
                $this->log->error('Failed sending email from %from to %to. @status: @message', array(
                  '%from' => $message['from_email'],
                  '%to' => $to,
                  '@status' => $status,
                  '@message' => $error_message,
                ));
              }
              return FALSE;
            case "queued":
              $this->log->info('Email from %from to %to queued by Mandrill App.', array(
                '%from' => $message['from_email'],
                '%to' => $result['email'],
              ));
              break;
          }
        }
      }
      else {
        $this->log->warning('Mail send failed with status %status: code %code, %name, %message', array(
          '%status' => $response['status'],
          '%code' => $response['code'],
          '%name' => $response['name'],
          '%message' => $response['message'],
        ));
        return FALSE;
      }
      return TRUE;
    }
    catch (\Exception $e) {
      $this->log->error('Error sending email from %from to %to. @code: @message', array(
        '%from' => $message['from_email'],
        '%to' => $message['to'],
        '@code' => $e->getCode(),
        '@message' => $e->getMessage(),
      ));
      return FALSE;
    }
  }
}
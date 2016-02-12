<?php
/**
 * @file
 * Contains \Drupal\mandrill\MandrillAPI.
 * Abstract the Mandrill PHP Api.
 */

namespace Drupal\mandrill;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Service class to integrate with Mandrill.
 */
class MandrillAPI implements MandrillAPIInterface {

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The Logger Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $log;

  /**
   * Constructs the service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->config = $config_factory->getEditable('mandrill.settings');
    $this->log = $logger_factory->get('mandrill');
  }

  /**
   * Check if the Mandrill PHP library is available.
   *
   * @return bool
   *   TRUE if it is installed, FALSE otherwise.
   */
  public function isLibraryInstalled() {
    $className = $this->config->get('mandrill_api_classname');
    return class_exists($className);
  }

  /**
   * Get a list of sub accounts from Mandrill.
   *
   * @return array
   */
  public function getSubAccounts() {
    $accounts = array();
    try {
      if ($mandrill = $this->getAPIObject()) {
        $accounts = $mandrill->subaccounts->getList();
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $accounts;
  }

  /**
   * Gets messages received by an email address.
   *
   * @param $email
   *   The email address of the message recipient.
   *
   * @return array
   */
  public function getMessages($email) {
    $messages = array();
    try {
      if ($mandrill = $this->getAPIObject()) {
        $messages = $mandrill->messages->search("email:{$email}");
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $messages;
  }

  /**
   * The function that calls the API send message.
   *
   * This is the default function used by mandrill_mailsend().
   *
   * @param array $message
   *   Associative array containing message data.
   * @return array
   *   Results of sending the message.
   *
   * @throws \Exception
   */
  public function send(array $message) {
    if ($mailer = $this->getAPIObject()) {
      return $mailer->messages->send($message);
    }
    else {
      throw new \Exception('Could not load Mandrill API.');
    }
  }

  /**
   * Return Mandrill API object for communication with the mandrill server.
   *
   * @param bool $reset
   *   Pass in TRUE to reset the statically cached object.
   *
   * @return \Mandrill|bool
   *   Mandrill Object upon success
   *   FALSE if 'mandrill_api_key' is unset
   */
  private function getAPIObject($reset = FALSE) {
    $api =& drupal_static(__FUNCTION__, NULL);
    if ($api === NULL || $reset) {
      if (!$this->isLibraryInstalled()) {
        $msg = t('Failed to load Mandrill PHP library. Please refer to the installation requirements.');
        $this->log->error($msg);
        drupal_set_message($msg, 'error');
        return NULL;
      }

      // @TODO: mandrill_api_key is undefined when tests are run
      $api_key = $this->config->get('mandrill_api_key');
      $api_timeout = $this->config->get('mandrill_api_timeout');
      if (empty($api_key)) {
        $msg = t('Failed to load Mandrill API Key. Please check your Mandrill settings.');
        $this->log->error($msg);
        drupal_set_message($msg, 'error');
        return FALSE;
      }
      // We allow the class name to be overridden, following the example of core's
      // mailsystem, in order to use alternate Mandrill classes. The bundled tests
      // use this approach to extend the Mandrill class with a test server.
      $className = $this->config->get('mandrill_api_classname');
      $api = new $className($api_key, $api_timeout);
    }
    return $api;
  }
}

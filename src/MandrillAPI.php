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
   * Gets a list of mandrill template objects.
   *
   * @return array
   *   An of available templates with complete data or NULL if none available.
   */
  public function getTemplates() {
    $templates = NULL;
    try {
      if ($mandrill = $this->getAPIObject()) {
        $templates = $mandrill->templates->getList();
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $templates;
  }

  /**
   * Gets a list of sub accounts.
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
   * Gets a list of webhooks.
   *
   * @return array
   */
  public function getWebhooks() {
    $webhooks = array();
    try {
      if ($mandrill = $this->getAPIObject()) {
        $webhooks = $mandrill->webhooks->getList();
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $webhooks;
  }

  /**
   * Gets a list of inbound domains.
   *
   * @return array
   */
  public function getInboundDomains() {
    $domains = array();
    try {
      if ($mandrill = $this->getAPIObject()) {
        $domains = $mandrill->inbound->domains();
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $domains;
  }

  /**
   * Gets a list of inbound routes.
   *
   * @return array
   */
  public function getInboundRoutes() {
    $routes = array();
    try {
      if ($mandrill = $this->getAPIObject()) {
        $routes = $mandrill->inbound->routes();
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $routes;
  }

  /**
   * Creates a new inbound domain.
   */
  public function addInboundDomain($domain) {
    $result = NULL;
    try {
      if ($mandrill = $this->getAPIObject()) {
        $result = $mandrill->inbound->addDomain($domain);
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $result;
  }

  /**
   * Creates a new webhook.
   *
   * @param string $path
   * @param array $events
   * @param string $description
   *
   * @return object
   *   The created Mandrill webhook object.
   */
  public function addWebhook($path, $events, $description = 'Drupal Webhook') {
    $result = NULL;
    try {
      if ($mandrill = $this->getAPIObject()) {
        $result = $mandrill->webhooks->add($GLOBALS['base_url'] . '/' . $path, $description, $events);
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $result;
  }

  /**
   * Deletes an inbound domain.
   */
  public function deleteInboundDomain($domain) {
    $result = NULL;
    try {
      if ($mandrill = $this->getAPIObject()) {
        $result = $mandrill->inbound->deleteDomain($domain);
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $result;
  }

  /**
   * Adds a new inbound route for a domain.
   */
  public function addInboundRoute($domain, $pattern, $url) {
    $result = NULL;
    try {
      if ($mandrill = $this->getAPIObject()) {
        $result = $mandrill->inbound->addRoute($domain, $pattern, $url);
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $result;
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

<?php

/**
 * @file
 * Contains \Drupal\mandrill\MandrillService.
 */

namespace Drupal\mandrill;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Serivce class to integrate with Twitter.
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
    $this->config = $config_factory->get('mandrill.settings');
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
    }
    catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      watchdog_exception('mandrill', $e);
    }

    return $accounts;
  }

  /**
   * Return Mandrill API object for communication with the mandrill server.
   *
   * @param bool $reset
   *   Pass in TRUE to reset the statically cached object.
   *
   * @return Mandrill|bool
   *   Mandrill Object upon success
   *   FALSE if variable_get('mandrill_api_key') is unset
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

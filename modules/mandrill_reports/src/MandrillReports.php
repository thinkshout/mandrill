<?php

/**
 * @file
 * Contains \Drupal\mandrill_reports\MandrillReports.
 */

namespace Drupal\mandrill_reports;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mandrill\MandrillAPIInterface;

/**
 * Mandrill Reports service.
 */
class MandrillReports implements MandrillReportsInterface {

  /**
   * The Mandrill API service.
   *
   * @var \Drupal\mandrill\MandrillAPIInterface
   */
  protected $mandrill_api;

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructs the service.
   *
   * @param \Drupal\mandrill\MandrillAPIInterface $mandrill_api
   *   The Mandrill API service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(MandrillAPIInterface $mandrill_api, ConfigFactoryInterface $config_factory) {
    $this->mandrill_api = $mandrill_api;
    $this->config = $config_factory;
  }

}

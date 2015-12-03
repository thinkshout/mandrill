<?php

/**
 * @file
 * Contains \Drupal\mandrill_reports\Plugin\Reports\MandrillReports.
 */

namespace Drupal\mandrill_reports\Plugin\Reports;

/**
 * Modify the Drupal mail system to use Mandrill when sending emails.
 *
 * @Reports(
 *   id = "mandrill_reports",
 *   label = @Translation("Mandrill reports"),
 *   description = @Translation("Mandrill reports.")
 * )
 */
//TODO: check out how MailChimp builds reports
class MandrillReports {

  /**
   * Constructor.
   */
  public function __construct() {
    $this->config = \Drupal::service('config.factory')->get('mandrill.settings');
    $this->mandrill = \Drupal::service('mandrill.service');
    $this->log = \Drupal::service('logger.factory')->get('mandrill');
    $this->mimeTypeGuesser = \Drupal::service('file.mime_type.guesser');
  }
}
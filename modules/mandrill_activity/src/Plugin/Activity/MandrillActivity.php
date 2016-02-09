<?php
/**
 * @file
 * Contains \Drupal\mandrill_activity\Plugin\Activity\MandrillActivity.
 */

namespace Drupal\mandrill_activity\Plugin\Activity;

  /**
   * Mandrill activity.
   *
   * @Activity(
   *   id = "mandrill_activity",
   *   label = @Translation("Mandrill activity"),
   *   description = @Translation("Mandrill activity.")
   * )
   */

//TODO: check out how MailChimp does this
class MandrillActivity {
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
<?php

/**
 * @file
 * Contains \Drupal\mandrill\Plugin\QueueWorker\MandrillQueueProcessor.
 */

namespace Drupal\mandrill\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Sends queued mail messages.
 *
 * @QueueWorker(
 *   id = "mandrill_queue",
 *   title = @Translation("Sends queued mail messages"),
 *   cron = {"time" = 60}
 * )
 */
class MandrillQueueProcessor extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    /* @var $mandrill \Drupal\mandrill\MandrillService */
    $mandrill = \Drupal::service('mandrill.service');

    $mandrill->send($data['message'], $data['function'], $data['args']);
  }

}

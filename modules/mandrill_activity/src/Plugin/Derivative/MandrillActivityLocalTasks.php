<?php

/**
 * @file
 * Contains \Drupal\mandrill_activity\Plugin\Derivative\MandrillActivityLocalTasks.
 */

namespace Drupal\mandrill_activity\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\mandrill_activity\Entity\MandrillActivity;

/**
 * Defines dynamic local tasks for Mandrill Activity.
 */
class MandrillActivityLocalTasks extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $activity_ids = \Drupal::entityQuery('mandrill_activity')->execute();

    $activity_entities = MandrillActivity::loadMultiple($activity_ids);

    /* @var $activity \Drupal\mandrill_activity\Entity\MandrillActivity */
    foreach ($activity_entities as $activity) {
      if (!$activity->enabled) {
        continue;
      }

      $task = $activity->entity_type . '.mandrill_activity';

      $this->derivatives[$task] = $base_plugin_definition;
      $this->derivatives[$task]['title'] = 'Mandrill Activity';
      $this->derivatives[$task]['route_name'] = 'entity.' . $activity->entity_type . '.mandrill_activity';
      $this->derivatives[$task]['base_route'] = 'entity.' . $activity->entity_type . '.canonical';
    }

    return $this->derivatives;
  }

}

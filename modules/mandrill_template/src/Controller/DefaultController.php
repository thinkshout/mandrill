<?php /**
 * @file
 * Contains \Drupal\mandrill_template\Controller\DefaultController.
 */

namespace Drupal\mandrill_template\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Default controller for the mandrill_template module.
 */
class DefaultController extends ControllerBase {

  public function mandrill_template_admin() {
    $path = drupal_get_path('module', 'mandrill_template');
    $render = [
      'message' => [
        '#markup' => t('Templates go here.', [
          '!dashboard' => \Drupal::l(t('Mandrill Dashboard'), \Drupal\Core\Url::fromUri('https://mandrillapp.com/')),
          '!cache' => \Drupal::l(t('Clear your cache'), \Drupal\Core\Url::fromRoute('system.performance_settings')),
        ])
        ],
    ];

    return $render;
  }

}

<?php

/**
 * @file
 * Contains \Drupal\mandrill_reports\Controller\MandrillReportsController.
 */

namespace Drupal\mandrill_reports\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * MandrillReports controller.
 */
class MandrillReportsController extends ControllerBase {

  /**
   * View Mandrill dashboard report.
   *
   * @return array
   *   Renderable array of page content.
   */
  public function dashboard() {
    $content = array();

    /* @var $reports \Drupal\mandrill_reports\MandrillReports */
    $reports = \Drupal::service('mandrill_reports.reports');

    $data = array();

    $data['user'] = $reports->getUsers();
    $data['tags'] = $reports->getTags();
    $data['all_time_series'] = $reports->getTagsAllTimeSeries();
    $data['senders'] = $reports->getSenders();
    $data['urls'] = $reports->getUrls();

    return $content;
  }

  /**
   * View Mandrill account summary report.
   *
   * @return array
   *   Renderable array of page content.
   */
  public function summary() {
    $content = array();

    /* @var $reports \Drupal\mandrill_reports\MandrillReports */
    $reports = \Drupal::service('mandrill_reports.reports');

    $user = $reports->getUser();

    $content['info_table_desc'] = array(
      '#markup' => t('Active API user information.'),
    );

    // User info table.
    $content['info_table'] = array(
      '#type' => 'table',
      '#header' => array(
        t('Attribute'),
        t('Value'),
      ),
      '#empty' => 'No account information.',
    );

    $info = array(
      array('attr' => t('Username'), 'value' => $user['username']),
      array('attr' => t('Reputation'), 'value' => $user['reputation']),
      array('attr' => t('Hourly quota'), 'value' => $user['hourly_quota']),
      array('attr' => t('Backlog'), 'value' => $user['backlog']),
    );

    $row = 0;
    foreach ($info as $item) {
      $content['info_table'][$row]['attribute'] = array(
        '#markup' => $item['attr'],
      );

      $content['info_table'][$row]['value'] = array(
        '#markup' => $item['value'],
      );

      $row++;
    }

    $content['stats_table_desc'] = array(
      '#markup' => t('This table contains an aggregate summary of the account\'s sending stats.'),
    );

    // User stats table.
    $content['stats_table'] = array(
      '#type' => 'table',
      '#header' => array(
        t('Range'),
        t('Sent'),
        t('hard_bounces'),
        t('soft_bounces'),
        t('Rejects'),
        t('Complaints'),
        t('Unsubs'),
        t('Opens'),
        t('unique_opens'),
        t('Clicks'),
        t('unique_clicks'),
      ),
      '#empty' => 'No account activity yet.',
    );

    if (!empty($user['stats'])) {
      $row = 0;
      foreach ($user['stats'] as $key => $stat) {
        $content['stats_table'][$row]['range'] = array(
          '#markup' => str_replace('_', ' ', $key),
        );

        $content['stats_table'][$row]['sent'] = array(
          '#markup' => $stat['sent'],
        );

        $content['stats_table'][$row]['hard_bounces'] = array(
          '#markup' => $stat['hard_bounces'],
        );

        $content['stats_table'][$row]['soft_bounces'] = array(
          '#markup' => $stat['soft_bounces'],
        );

        $content['stats_table'][$row]['rejects'] = array(
          '#markup' => $stat['rejects'],
        );

        $content['stats_table'][$row]['complaints'] = array(
          '#markup' => $stat['complaints'],
        );

        $content['stats_table'][$row]['unsubs'] = array(
          '#markup' => $stat['unsubs'],
        );

        $content['stats_table'][$row]['opens'] = array(
          '#markup' => $stat['opens'],
        );

        $content['stats_table'][$row]['unique_opens'] = array(
          '#markup' => $stat['unique_opens'],
        );

        $content['stats_table'][$row]['clicks'] = array(
          '#markup' => $stat['clicks'],
        );

        $content['stats_table'][$row]['unique_clicks'] = array(
          '#markup' => $stat['unique_clicks'],
        );

        $row++;
      }
    }

    return $content;
  }

}

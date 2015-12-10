<?php /**
 * @file
 * Contains \Drupal\mandrill_reports\Controller\DefaultController.
 */

namespace Drupal\mandrill_reports\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Default controller for the mandrill_reports module.
 */
class DefaultController extends ControllerBase {

  public function mandrill_reports_dashboard_page() {
    $data = mandrill_reports_data();
    $settings = [];
    // All time series chart data.
    foreach ($data['all_time_series'] as $series) {
      $settings['mandrill_reports']['volume'][] = [
        'date' => $series['time'],
        'sent' => $series['sent'],
        'bounced' => $series['hard_bounces'] + $series['soft_bounces'],
        'rejected' => $series['rejects'],
      ];
      $settings['mandrill_reports']['engagement'][] = [
        'date' => $series['time'],
        'open_rate' => $series['sent'] == 0 ? 0 : $series['unique_opens'] / $series['sent'],
        'click_rate' => $series['sent'] == 0 ? 0 : $series['unique_clicks'] / $series['sent'],
      ];
    }

    // Url table.
    $rows = [];
    $header = [
      t('URL'),
      t('Delivered'),
      t('Unique clicks'),
      t('Total Clicks'),
    ];
    foreach ($data['urls'] as $url) {
      $percent = number_format($url['unique_clicks'] / $url['sent'], 2) * 100;
      // @FIXME
      // l() expects a Url object, created from a route name or external URI.
      // $rows[] = array(
      //       l($url['url'], $url['url']),
      //       $url['sent'],
      //       $url['unique_clicks'] . "({$percent}%)",
      //       $url['clicks']);

    }

    $path = drupal_get_path('module', 'mandrill_reports');
    $render = [
      '#attached' => [
        'js' => [
          [
            'data' => 'https://www.google.com/jsapi',
            'type' => 'external',
          ],
          $path . '/mandrill_reports.js',
          [
            'data' => $settings,
            'type' => 'setting',
          ],
        ]
        ],
      'message' => [
        '#markup' => t('The following reports are based on Mandrill data from the last 30 days. For more comprehensive data, please visit your !dashboard. !cache to ensure the data is current.', [
          '!dashboard' => \Drupal::l(t('Mandrill Dashboard'), \Drupal\Core\Url::fromUri('https://mandrillapp.com/')),
          '!cache' => \Drupal::l(t('Clear your cache'), \Drupal\Core\Url::fromRoute('system.performance_settings')),
        ])
        ],
      'volume' => [
        '#prefix' => '<h2>' . t('Sending Volume') . '</h2>',
        '#markup' => '<div id="mandrill-volume-chart"></div>',
      ],
      'engagement' => [
        '#prefix' => '<h2>' . t('Average Open and Click Rate') . '</h2>',
        '#markup' => '<div id="mandrill-engage-chart"></div>',
      ],
      'urls' => [
        '#prefix' => '<h2>' . t('Tracked URLs') . '</h2>',
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $rows,
      ],
    ];

    return $render;
  }

  public function mandrill_reports_summary_page() {
    $data = mandrill_reports_data();
    $info = $data['user'];

    $header = [
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
    ];

    $rows = [];
    foreach ($info['stats'] as $key => $stat) {
      $rows[] = [
        str_replace('_', ' ', $key),
        $stat['sent'],
        $stat['hard_bounces'],
        $stat['soft_bounces'],
        $stat['rejects'],
        $stat['complaints'],
        $stat['unsubs'],
        $stat['opens'],
        $stat['unique_opens'],
        $stat['clicks'],
        $stat['unique_clicks'],
      ];
    }

    $render = [
      'info' => [
        '#theme' => 'table',
        '#rows' => [
          [
            t('Username'),
            $info['username'],
          ],
          [t('Reputation'), $info['reputation']],
          [
            t('Hourly quota'),
            $info['hourly_quota'],
          ],
          [t('Backlog'), $info['backlog']],
        ],
        '#header' => [t('Attribute'), t('Value')],
        '#caption' => t('Active API user information.'),
      ],
      'stats' => [
        '#theme' => 'table',
        '#rows' => $rows,
        '#header' => $header,
        '#caption' => t("This table contains an aggregate summary of the account's sending stats."),
      ],
    ];

    return $render;
  }

}

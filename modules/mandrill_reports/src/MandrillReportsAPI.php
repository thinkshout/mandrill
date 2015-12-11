<?php
/**
 * @file
 * Contains \Drupal\mandrill_reports\MandrillReportsAPI.
 */

namespace Drupal\mandrill_reports;

use Drupal\mandrill\MandrillAPI;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Service class to integrate with Mandrill API.
 */
class MandrillReportsAPI extends MandrillAPI {
  /**
   * Get user info from Mandrill.
   *
   * @return array
   */
  public function getUsers($mandrill) {
    $users = array();
    try {
      $users = $mandrill->users->info();
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $users;
  }

  /**
   * Get tags from Mandrill.
   *
   * @return array
   */
  public function getTags($mandrill) {
    $data = array();
    try {
      $tags = $mandrill->tags->info();
      foreach ($tags as $tag) {
        if (!empty($tag['tag'])) {
          $data['tags'][$tag['tag']] = $mandrill->tags->info($tag['tag']);
          $data['tags'][$tag['tag']]['time_series'] = $mandrill->tags->timeSeries($tag['tag']);
        }
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $data['tags'];
  }

  /**
   * Get senders from Mandrill.
   *
   * @return array
   */
  public function getSenders($mandrill) {
    $data = array();
    try {
      $senders = $mandrill->senders->getList();
      foreach ($senders as $sender) {
        try {
          $data['senders'][$sender['address']] = $mandrill->senders->info($sender['address']);
          $data['senders'][$sender['address']]['time_series'] = $mandrill->senders->timeSeries($sender['address']);
        }
        catch (\Exception $e) {
          \Drupal::logger('mandrill')->error('An error occurred requesting sender information from Mandrill for address %address. "%message"', array(
            '%address' => $sender['address'],
            '%message' => $e->getMessage(),
          ));
        }
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $data['senders'];
  }

  /**
   * Get urls from Mandrill.
   *
   * @return array
   */
  public function getUrls($mandrill) {
    $data = array();
    $urls = array();
    try {
      foreach ($urls as $url) {
        // Api has been intermittently tacking on incomplete $url arrays,
        // so we have to check validity first:
        if (isset($url['url'])) {
          $data['urls'][$url['url']] = $url;
          $data['urls'][$url['url']]['time_series'] = $api->urls->timeSeries($url['url']);
        }
      }
    } catch (\Exception $e) {
      drupal_set_message(t('Mandrill: %message', array('%message' => $e->getMessage())), 'error');
      $this->log->error($e->getMessage());
    }
    return $data['urls'];
  }

  /**
   * Mandrill report data.
   *
   * @return array
   */
  public function getReportData() {
    $data = array();
/*    if ($mandrill = $this->getAPIObject()) {
      $data['user'] = $this->getUsers($mandrill);
      $data['tags'] = $this->getTags($mandrill);
      $data['senders'] = $this->getSenders($mandrill);
      $data['senders'] = $this->getUrls($mandrill);
      \Drupal::cache('cache')
        ->set('mandrill_report_data', $data, CACHE_TEMPORARY);
    else {
        drupal_set_message(t('Please enter a Mandrill API key to use reports.'));
        //@TODO: redirect
//      drupal_goto('admin/config/services/mandrill');
      }
    }*/
    return $data;
  }
}
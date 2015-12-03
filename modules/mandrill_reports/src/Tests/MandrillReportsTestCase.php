<?php
/**
 * @file
 * Test class and methods for the Mandrill Reports module.
 */

namespace Drupal\mandrill_reports\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\mandrill_reports\Plugin\Reports;

/**
 * Test Mandrill Reports functionality.
 *
 * @group mandrill
 */
class MandrillReportsTestCase extends WebTestBase {
  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['libraries', 'mandrill', 'mandrill_reports', 'entity'];
  /**
   * Pre-test setup function.
   *
   * Enables dependencies.
   * Sets the mandrill_api_key variable to the test key.
   */
  protected function setUp() {
    parent::setUp();
    $config = \Drupal::service('config.factory')->getEditable('mandrill.settings');
    $config->set('mandrill_api_classname', 'DrupalMandrillTest');
    $config->set('mandrill_api_key', MANDRILL_TEST_API_KEY);
    $config->save();
  }

  /**
   * Tests getting Mandrill reports data.
   */
  public function testGetReportsData() {
    $reports_data = $this->getMandrillReports();

    $this->assertTrue(!empty($reports_data), 'Tested retrieving reports data.');
    $this->assertTrue(!empty($reports_data['user']), 'Tested user report data exists.');
    $this->assertTrue(!empty($reports_data['tags']), 'Tested tags report data exists.');
    $this->assertTrue(!empty($reports_data['all_time_series']), 'Tested all time series report data exists.');
    $this->assertTrue(!empty($reports_data['senders']), 'Tested senders report data exists.');
    $this->assertTrue(!empty($reports_data['urls']), 'Tested URLs report data exists.');
  }

  /**
   * Get the Mandrill Reports plugin.
   *
   * @return \Drupal\mandrill_reports\Plugin\Reports\MandrillReports
   */
  private function getMandrillReports() {
    return new MandrillReports();
  }
}
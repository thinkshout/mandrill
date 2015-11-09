<?php

/**
 * @file
 * Contains Drupal\mandrill_template\Tests\MandrillTemplateTestBase.
 */

namespace Drupal\mandrill_template\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Sets up Mandrill Template module tests.
 */
abstract class MandrillTemplateTestBase extends WebTestBase {

  /**
   * Pre-test setup function.
   *
   * Enables dependencies.
   * Sets the mandrill_api_key variable to the test key.
   */
  protected function setUp() {
    // Use a profile that contains required modules:
    $prof = drupal_get_profile();
    $this->profile = $prof;
    // Enable modules required for the test.
    $enabled_modules = [
      'libraries',
      'mandrill',
      'mandrill_template',
      'entity',
    ];
    parent::setUp($enabled_modules);
    \Drupal::config('mandrill.settings')->set('mandrill_api_classname', 'DrupalMandrillTest')->save();
    \Drupal::config('mandrill.settings')->set('mandrill_api_key', 'MANDRILL_TEST_API_KEY')->save();
  }

  /**
   * Post-test function.
   *
   * Sets test mode to FALSE.
   */
  protected function tearDown() {
    parent::tearDown();

    \Drupal::config('mandrill.settings')->clear('mandrill_api_classname')->save();
    \Drupal::config('mandrill.settings')->clear('mandrill_api_key')->save();
  }

}
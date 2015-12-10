<?php
namespace Drupal\mandrill\Tests;

/**
 * Test core Mandrill functionality.
 *
 * @group mandrill
 */

class MandrillTestCase extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  /**
   * Returns info displayed in the test interface.
   *
   * @return array
   *   Formatted as specified by simpletest.
   */
  public static function getInfo() {
    // Note: getInfo() strings are not translated with t().
    return [
      'name' => 'Mandrill Tests',
      'description' => 'Test core Mandrill functionality.',
      'group' => 'Mandrill',
    ];
  }

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
      'entity',
    ];
    parent::setUp($enabled_modules);
    \Drupal::config('mandrill.settings')->set('mandrill_api_classname', 'DrupalMandrillTest')->save();
    \Drupal::config('mandrill.settings')->set('mandrill_api_key', 'MANDRILL_TEST_API_KEY')->save();
    \Drupal::config('mandrill.settings')->set('mandrill_test_mode', TRUE)->save();
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
    \Drupal::config('mandrill.settings')->clear('mandrill_test_mode')->save();
  }

  /**
   * Tests sending a message to multiple recipients.
   */
  public function testSendMessage() {
    $mail_system = new MandrillMailSystem();

    $to = 'Recipient One <recipient.one@example.com>,' . 'Recipient Two <recipient.two@example.com>,' . 'Recipient Three <recipient.three@example.com>';

    $message = $this->getMessageTestData();
    $message['to'] = $to;

    $response = $mail_system->mail($message);

    $this->assertTrue($response, 'Tested sending message to multiple recipients.');
  }

  /**
   * Tests sending a message to an invalid recipient.
   */
  public function testSendMessageInvalidRecipient() {
    $mail_system = new MandrillMailSystem();

    $to = 'Recipient One <recipient.one>';

    $message = $this->getMessageTestData();
    $message['to'] = $to;

    $response = $mail_system->mail($message);

    $this->assertFalse($response, 'Tested sending message to an invalid recipient.');
  }

  /**
   * Tests sending a message to no recipients.
   */
  public function testSendMessageNoRecipients() {
    $mail_system = new MandrillMailSystem();

    $message = $this->getMessageTestData();
    $message['to'] = '';

    $response = $mail_system->mail($message);

    $this->assertFalse($response, 'Tested sending message to no recipients.');
  }

  /**
   * Gets getting a list of templates for a given label.
   */
  public function testGetTemplates() {
    $templates = mandrill_get_templates();

    $this->assertTrue(!empty($templates), 'Tested retrieving templates.');

    if (!empty($templates) && is_array($templates)) {
      foreach ($templates as $template) {
        $this->assertTrue(!empty($template['name']), 'Tested valid template: ' . $template['name']);
      }
    }
  }

  /**
   * Tests getting a list of subaccounts.
   */
  public function testGetSubaccounts() {
    $subaccounts = mandrill_get_subaccounts();

    $this->assertTrue(!empty($subaccounts), 'Tested retrieving subaccounts.');

    if (!empty($subaccounts) && is_array($subaccounts)) {
      foreach ($subaccounts as $subaccount) {
        $this->assertTrue(!empty($subaccount['name']), 'Tested valid subaccount: ' . $subaccount['name']);
      }
    }
  }

  /**
   * Gets message data used in tests.
   */
  protected function getMessageTestData() {
    $message = [
      'id' => 1,
      'module' => NULL,
      'body' => '<p>Mail content</p>',
      'subject' => 'Mail Subject',
      'from_email' => 'sender@example.com',
      'from_name' => 'Test Sender',
    ];

    return $message;
  }

}

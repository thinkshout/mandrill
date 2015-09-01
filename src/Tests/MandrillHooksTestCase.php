<?php
namespace Drupal\mandrill\Tests;

/**
 * Tests Mandrill hook functionality.
 * 
 * @group mandrill
 */
class MandrillHooksTestCase extends \Drupal\simpletest\WebTestBase {

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
      'name' => 'Mandrill Hooks Tests',
      'description' => 'Tests Mandrill hook functionality.',
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
      'mandrill_test',
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
    \Drupal::config('mandrill.settings')->clear('mandrill_test_mailsend_result')->save();
  }

  /**
   * Tests implementing hook_mandrill_mail_alter().
   */
  public function testMailAlterHook() {
    /**
     * @see MandrillMailSystem::mail() for use example.
     */
    $message = $this->getMessageTestData();

    $mandrill_params = [
      'message' => $message,
      'function' => 'mandrill_sender_plain',
      'args' => [],
    ];

    /**
     * Perform alterations on the message.
     * @see mandrill_test_mandrill_mail_alter()
     */
    \Drupal::moduleHandler()->alter('mandrill_mail', $mandrill_params, $message);

    // Test altered values.
    $this->assertEqual($mandrill_params['message']['subject'], 'Altered Test Subject', 'Tested altered mail subject.');
    $this->assertEqual($mandrill_params['message']['html'], '<p>Altered mail content</p>', 'Tested altered mail content.');

    // Test unaltered values.
    $this->assertEqual($mandrill_params['message']['from_email'], $message['from_email'], 'Tested unaltered from email.');
    $this->assertEqual($mandrill_params['message']['from_name'], $message['from_name'], 'Tested unaltered from name.');
  }

  /**
   * Tests implementing hook_mandrill_valid_attachment_types_alter().
   */
  public function testValidAttachmentTypesAlterHook() {
    $types = [
      'image/png',
      'image/jpeg',
      'image/gif',
    ];

    /**
     * Perform alterations on the attachment types array.
     * @see mandrill_test_mandrill_valid_attachment_types_alter()
     */
    \Drupal::moduleHandler()->alter('mandrill_valid_attachment_types', $types);

    $this->assertTrue(in_array('application/pdf', $types), 'Tested altered attachment types.');
  }

  /**
   * Tests implementing hook_mandrill_mailsend_result().
   */
  public function testMailsendResultHook() {
    $mail_system = new MandrillMailSystem();

    $message = $this->getMessageTestData();
    $message['to'] = 'Recipient One <recipient.one@example.com>';

    $mail_system->mail($message);

    $mailsend_result = \Drupal::config('mandrill.settings')->get('mandrill_test_mailsend_result');

    $this->assertNotNull($mailsend_result, 'Tested mailsend_result hook triggered.');

    if (!is_null($mailsend_result)) {
      $this->assertEqual($mailsend_result['email'], 'recipient.one@example.com', 'Tested expected mailsend_result hook result');
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

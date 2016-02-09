<?php

/**
 * @file
 * Contains Drupal\mandrill_template\Tests\MandrillTemplateTest.
 */


namespace Drupal\mandrill;

/**
 * @file
 * A virtual Mandrill Template API implementation for use in testing.
 */

class MandrillTemplateTest extends MandrillTemplateTestBase {
  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }

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
      'name' => 'Mandrill Template Tests',
      'description' => 'Tests Mandrill Template functionality.',
      'group' => 'Mandrill',
    ];
  }

  /**
   * @see Mandrill_Template::getList()
   */
  public function getList($label = NULL) {
    $templates = $this->getTestTemplateData();

    if (empty($label)) {
      $matched_templates = $templates;
    }
    else {
      $matched_templates = array();

      foreach ($templates as $template) {
        if (in_array($label, $template->labels)) {
          $matched_templates[] = $template;
        }
      }
    }

    return $matched_templates;
  }

  /**
   * Gets an array of templates used in tests.
   */
  protected function getTestTemplateData() {
    $templates = array();

    $template = array(
      'slug' => 'test-template',
      'name' => 'Test Template',
      'labels' => array(
        'test-label'
      ),
      'code' => '<div>editable content</div>',
      'subject' => 'Test Subject',
      'from_email' => 'admin@example.com',
      'from_name' => 'Admin',
      'text' => 'Test text',
      'publish_name' => 'Test Template',
      'publish_code' => '<div>different than draft content</div>',
      'publish_subject' => 'Test Publish Subject',
      'publish_from_email' => 'admin@example.com',
      'publish_from_name' => 'Test Publish Name',
      'publish_text' => 'Test publish text',
      'published_at' => '2013-01-01 15:30:40',
      'created_at' => '2013-01-01 15:30:27',
      'updated_at' => '2013-01-01 15:30:49',
    );

    $templates[] = $template;

    return $templates;
  }

  /**
   * Test sending a templated message using an invalid template.
   */
  public function testSendTemplatedMessageInvalidTemplate() {
    $to = 'Recipient One <recipient.one@example.com>';

    $message = $this->getMessageTestData();
    $message['to'] = $to;

    $template_id = 'Invalid Template';
    $template_content = ['name' => 'Recipient'];

    $response = mandrill_template_sender($message, $template_id, $template_content);

    $this->assertNotNull($response, 'Tested response from sending invalid templated message.');

    if (isset($response['status'])) {
      $this->assertEqual($response['status'], 'error', 'Tested response status: ' . $response['status'] . ', ' . $response['message']);
    }
  }

  /**
   * Gets message data used in tests.
   */
  protected function getMessageTestData() {
    $message = [
      'id' => 1,
      'body' => '<p>Mail content</p>',
      'subject' => 'Mail Subject',
      'from_email' => 'sender@example.com',
      'from_name' => 'Test Sender',
    ];

    return $message;
  }

  /**
   * Test sending a templated message to multiple recipients.
   */
  public function testSendTemplatedMessage() {
    $to = 'Recipient One <recipient.one@example.com>,' . 'Recipient Two <recipient.two@example.com>,' . 'Recipient Three <recipient.three@example.com>';

    $message = $this->getMessageTestData();
    $message['to'] = $to;

    $template_id = 'Test Template';
    $template_content = ['name' => 'Recipient'];

    $response = mandrill_template_sender($message, $template_id, $template_content);

    $this->assertNotNull($response, 'Tested response from sending templated message.');

    if (isset($response['status'])) {
      $this->assertNotEqual($response['status'], 'error', 'Tested response status: ' . $response['status'] . ', ' . $response['message']);
    }
  }


}

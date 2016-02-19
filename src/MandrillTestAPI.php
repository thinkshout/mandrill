<?php

/**
 * @file
 * Contains \Drupal\mandrill\MandrillTestAPI.
 */

namespace Drupal\mandrill;

/**
 * Test Mandrill API service.
 */
class MandrillTestAPI extends MandrillAPI {

  /**
   * {@inheritdoc}
   */
  public function getMessages($email) {
    $matched_messages = array();

    $query_key = 'email';
    $query_value = $email;

    $messages = $this->getTestMessagesData();

    foreach ($messages as $message) {
      if (isset($message[$query_key]) && ($message[$query_key] == $query_value)) {
        $matched_messages[] = $message;
      }
    }

    return $matched_messages;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubAccounts() {
    $subaccounts = array();

    // Test Customer One
    $subaccount = array(
      'id' => 'test-customer-1',
      'name' => 'Test Customer One',
      'custom_quota' => 42,
      'status' => 'active',
      'reputation' => 42,
      'created_at' => '2013-01-01 15:30:27',
      'first_sent_at' => '2013-01-01 15:30:29',
      'sent_weekly' => 42,
      'sent_monthly' => 42,
      'sent_total' => 42,
    );

    $subaccounts[] = $subaccount;

    // Test Customer Two
    $subaccount = array(
      'id' => 'test-customer-2',
      'name' => 'Test Customer Two',
      'custom_quota' => 42,
      'status' => 'active',
      'reputation' => 42,
      'created_at' => '2013-01-01 15:30:27',
      'first_sent_at' => '2013-01-01 15:30:29',
      'sent_weekly' => 42,
      'sent_monthly' => 42,
      'sent_total' => 42,
    );

    $subaccounts[] = $subaccount;

    return $subaccounts;
  }

  /**
   * {@inheritdoc}
   */
  public function send(array $message) {
    if (!isset($message['to']) || empty($message['to'])) {
      return $this->getErrorResponse(12, 'ValidationError', 'No recipients defined.');
    }

    $response = array();

    foreach ($message['to'] as $recipient) {
      $recipient_response = array(
        'email' => $recipient['email'],
        'status' => '',
        'reject_reason' => '',
        '_id' => uniqid(),
      );

      // TODO: Replace deprecated valid_email_address().
      if (valid_email_address($recipient['email'])) {
        $recipient_response['status'] = 'sent';
      }
      else {
        $recipient_response['status'] = 'invalid';
      }

      $response[] = $recipient_response;
    }

    return $response;
  }

  /**
   * Gets a Mandrill-style formatted error response.
   *
   * @param int $code
   *   The Mandrill error code.
   * @param string $name
   *   The name of the Mandrill error type (ValidationError, etc.)
   * @param $message
   *   The error message.
   *
   * @return array
   *   Formatted error response.
   */
  protected function getErrorResponse($code, $name, $message) {
    $response = array(
      'status' => 'error',
      'code' => $code,
      'name' => $name,
      'message' => $message,
    );

    return $response;
  }

  /**
   * Gets an array of messages used in tests.
   */
  protected function getTestMessagesData() {
    $messages = array();

    $message = array(
      'ts' => 1365190000,
      '_id' => 'abc123abc123abc123abc123',
      'sender' => 'sender@example.com',
      'template' => 'test-template',
      'subject' => 'Test Subject',
      'email' => 'recipient@example.com',
      'tags' => array(
        'test-tag'
      ),
      'opens' => 42,
      'opens_detail' => array(
        'ts' => 1365190001,
        'ip' => '55.55.55.55',
        'location' => 'Georgia, US',
        'ua' => 'Linux/Ubuntu/Chrome/Chrome 28.0.1500.53',
      ),
      'clicks' => 42,
      'clicks_detail' => array(
        'ts' => 1365190001,
        'url' => 'http://www.example.com',
        'ip' => '55.55.55.55',
        'location' => 'Georgia, US',
        'ua' => 'Linux/Ubuntu/Chrome/Chrome 28.0.1500.53',
      ),
      'state' => 'sent',
      'metadata' => array(
        'user_id' => 123,
        'website' => 'www.example.com',
      ),
    );

    $messages[] = $message;

    return $messages;
  }

}

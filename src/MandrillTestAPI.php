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
  private function getErrorResponse($code, $name, $message) {
    $response = array(
      'status' => 'error',
      'code' => $code,
      'name' => $name,
      'message' => $message,
    );

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  private function getAPIObject($reset = FALSE) {

  }

}

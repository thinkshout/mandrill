<?php

namespace Drupal\mandrill;

/**
 * Test Mandrill service.
 */
class MandrillTestService extends MandrillService {

  /**
   * {@inheritdoc}
   */
  protected function handleSendResponse($response, $message) {
    if (isset($response['status'])) {
      // There was a problem sending the message.
      return FALSE;
    }

    foreach ($response as $result) {
      // Allow other modules to react based on a send result.
      \Drupal::moduleHandler()->invokeAll('mandrill_mailsend_result', [$result]);
      switch ($result['status']) {
        case "error":
        case "invalid":
        case "rejected":
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Abstracts sending of messages using PHP's built-in mail() function
   * instead of Mandrill's sending API.
   *
   * @param array $message
   *   A message array formatted for Mandrill's sending API.
   *
   * @return bool
   *   TRUE if the mail was successfully accepted for delivery, FALSE otherwise.
   */
  public function send($message) {

    // Construct simple email using values from $message, which has much more
    // information than we need because it's formatted for Mandrill's sending
    // API.
    $to = $message['to'][0]['email'];
    // Check for subject as string.
    if (gettype($message['subject']) == 'string') {
      $subject = $message['subject'];
    }
    // If subject is not a string we assume it's TranslatableMarkup and call
    // its render() function.
    else {
      $subject = $message['subject']->render();
    }
    $body = $message['html'];
    // Add headers to message.
    $additional_headers = '';
    foreach ($message['headers'] as $key => $value) {
      $additional_headers .= implode(':', [$key, $value]) . "\r\n";
    }

    // Send email using PHP's mail() function.
    $result = mail($to, $subject, $body, $additional_headers);

    // Return result of attempt to send mail.
    return $result;
  }

}

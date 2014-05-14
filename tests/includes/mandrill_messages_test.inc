<?php
/**
 * @file
 * A virtual Mandrill Messages API implementation for use in testing.
 */

class Mandrill_MessagesTest extends Mandrill_Messages {
  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }

  /**
   * @see Mandrill_Messages::send()
   * TODO: Allow testing of "queued", "scheduled", "rejected", and "invalid"
   */
  public function send($message, $async=false, $ip_pool=null, $send_at=null) {
    if (!isset($message['to']) || empty($message['to'])) {
      return $this->master->get_error_response(12, 'ValidationError', 'No recipients defined.');
    }

    $response = array();

    foreach ($message['to'] as $recipient) {
      $recipient_response = array(
        'email' => $recipient['email'],
        'status' => 'sent',
        'reject_reason' => '',
        '_id' => uniqid(),
      );

      $response[] = $recipient_response;
    }

    return $response;
  }
}

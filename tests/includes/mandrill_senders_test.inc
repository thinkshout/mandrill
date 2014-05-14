<?php
/**
 * @file
 * A virtual Mandrill Senders API implementation for use in testing.
 */

class Mandrill_SendersTest extends Mandrill_Senders {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

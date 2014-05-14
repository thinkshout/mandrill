<?php
/**
 * @file
 * A virtual Mandrill Rejects API implementation for use in testing.
 */

class Mandrill_RejectsTest extends Mandrill_Rejects {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

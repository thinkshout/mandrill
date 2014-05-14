<?php
/**
 * @file
 * A virtual Mandrill Inbound API implementation for use in testing.
 */

class Mandrill_InboundTest extends Mandrill_Inbound {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

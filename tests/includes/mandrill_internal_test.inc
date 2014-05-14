<?php
/**
 * @file
 * A virtual Mandrill Internal API implementation for use in testing.
 */

class Mandrill_InternalTest extends Mandrill_Internal {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

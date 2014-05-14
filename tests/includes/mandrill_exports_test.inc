<?php
/**
 * @file
 * A virtual Mandrill Exports API implementation for use in testing.
 */

class Mandrill_ExportsTest extends Mandrill_Exports {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

<?php
/**
 * @file
 * A virtual Mandrill Templates API implementation for use in testing.
 */

class Mandrill_TemplatesTest extends Mandrill_Templates {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

<?php
/**
 * @file
 * A virtual Mandrill Users API implementation for use in testing.
 */

class Mandrill_UsersTest extends Mandrill_Users {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

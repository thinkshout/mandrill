<?php
/**
 * @file
 * A virtual Mandrill Whitelists API implementation for use in testing.
 */

class Mandrill_WhitelistsTest extends Mandrill_Whitelists {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

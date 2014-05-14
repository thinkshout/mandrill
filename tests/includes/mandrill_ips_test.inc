<?php
/**
 * @file
 * A virtual Mandrill IPs API implementation for use in testing.
 */

class Mandrill_IpsTest extends Mandrill_Ips {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

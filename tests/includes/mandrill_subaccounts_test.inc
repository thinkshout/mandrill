<?php
/**
 * @file
 * A virtual Mandrill Subaccounts API implementation for use in testing.
 */

class Mandrill_SubaccountsTest extends Mandrill_Subaccounts {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

<?php
/**
 * @file
 * A virtual Mandrill URLs API implementation for use in testing.
 */

class Mandrill_UrlsTest extends Mandrill_Urls {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

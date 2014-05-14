<?php
/**
 * @file
 * A virtual Mandrill Webhooks API implementation for use in testing.
 */

class Mandrill_WebhooksTest extends Mandrill_Webhooks {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

<?php
/**
 * @file
 * A virtual Mandrill Tags API implementation for use in testing.
 */

class Mandrill_TagsTest extends Mandrill_Tags {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

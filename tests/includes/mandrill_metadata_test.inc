<?php
/**
 * @file
 * A virtual Mandrill Metadata API implementation for use in testing.
 */

class Mandrill_MetadataTest extends Mandrill_Metadata {
  // TODO: Override API methods with test methods.

  public function __construct(DrupalMandrillTest $master) {
    parent::__construct($master);
  }
}

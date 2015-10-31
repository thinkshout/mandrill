<?php

/**
 * @file
 * Contains \Drupal\mandrill\MandrillServiceInterface.
 */

namespace Drupal\mandrill;


/**
 * Interface for the Mandrill service.
 */
interface MandrillServiceInterface {
  public function isLibraryInstalled();
  public function getSubAccounts();
}

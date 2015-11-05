<?php
/**
 * @file
 * Contains \Drupal\mandrill\MandrillAPIInterface.
 */
namespace Drupal\mandrill;
/**
 * Interface for the Mandrill API.
 */
interface MandrillAPIInterface {
  public function getSubAccounts();
  public function send(array $message);
}
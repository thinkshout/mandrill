<?php

/**
 * @file
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 * Allows other modules to alter the Mandrill message and sender arguments.
 *
 * @param $mandrill_params
 *   The mandril message array
 *   @see MandrillMailSystem::mail().
 *
 * @param $message
 *   The drupal_mail message array.
 *   @see drupal_mail().
 */
function hook_mandrill_mail_alter(&$mandrill_params, $message) {
  // No example.
}

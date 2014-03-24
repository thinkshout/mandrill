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

/**
 * Allows other modules to alter the allowed attachment file types.
 *
 * @param $types
 *   An array of file types indexed numerically.
 */

function hook_mandrill_valid_attachment_types_alter(&$types) {
  // Example, allow word docs:
  $types[] = 'application/msword';
  // allow openoffice docs:
  $types[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
}

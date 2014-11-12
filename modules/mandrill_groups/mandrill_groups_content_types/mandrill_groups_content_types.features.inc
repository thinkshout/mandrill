<?php
/**
 * @file
 * mandrill_groups_content_types.features.inc
 */

/**
 * Implements hook_node_info().
 */
function mandrill_groups_content_types_node_info() {
  $items = array(
    'mandrill_discussion' => array(
      'name' => t('Mandrill Discussion'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'mandrill_group' => array(
      'name' => t('Mandrill Group'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}

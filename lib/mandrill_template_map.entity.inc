<?php
/**
 * @file
 * Redhen Contact entity classses
 */
<?php

class MandrillTemplateMap extends Entity {
  public
    $mandrill_template_map_entity_id,
    $name,
    $label,
    $entity_type,
    $bundle,
    $mailsystem_key,
    $template_id,
    $main_section,
    $sections;

  public function __construct(array $values = array()) {
    parent::__construct($values, 'mandrill_template_map');
  }

  /**
   * Specify URI
   */
  protected function defaultUri() {
    return array('path' => 'admin/config/services/mandrill/templates/' . $this->identifier());
  }

  /**
   * Send email through Mandrill using this template map
   */
  public function send() {

  }
}

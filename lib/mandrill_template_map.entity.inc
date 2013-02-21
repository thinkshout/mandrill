<?php
/**
 * @file
 * Redhen Contact entity classses
 */

class MandrillTemplateMap extends Entity {
  public
    $mandrill_template_map_entity_id,
    $name,
    $label,
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
  public function send($mandrill_message, Mandrill $mailer) {
    $template_content = array(
      '0' => array(
        'name' => $this->main_section,
        'content' => $mandrill_message['text'],
      )
    );
    return $mailer->messages_send_template($this->template_id, $template_content, $mandrill_message);
  }
}

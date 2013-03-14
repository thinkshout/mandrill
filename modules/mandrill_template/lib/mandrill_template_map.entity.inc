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
   * Specify URI.
   */
  protected function defaultUri() {
    return array('path' => 'admin/config/services/mandrill/templates/' . $this->identifier());
  }

  /**
   * Generate the arguments that are required for sending templates
   * 
   * @return array
   */
  public function message_send_arguments($message) {
    // Apply input format to body.
    $html = $message['body'];
    $format = variable_get('mandrill_filter_format', '');
    if (!empty($format)) {
      $html = check_markup($message['body'], $format);
    }
    return array(
      'id' => $this->template_id,
      'template_content' => array(
        '0' => array(
          'name' => $this->main_section,
          'content' => $html,
        ),
      ),
    );
  }
}

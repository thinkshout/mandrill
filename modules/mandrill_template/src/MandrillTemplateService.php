<?php

/**
 * @file
 * Contains \Drupal\mandrill_template\MandrillTemplateService.
 */

namespace Drupal\mandrill_template;

use Drupal\mandrill\MandrillService;

/**
 * Mandrill Template service.
 *
 * Overrides Mandirll Service to allow sending of templated messages.
 */
class MandrillTemplateService extends MandrillService {

  /**
   * {@inheritdoc}
   */
  public function send($message, $function, array $args = array()) {
    $template_map = null;

    if (isset($message['id']) && isset($message['module'])) {
      $template_map = mandrill_template_map_load_by_mailsystem($message['id'], $message['module']);
    }

    if (!empty($template_map)) {
      $template_content = array(
        array(
          'name' => $template_map->main_section,
          'content' => $message['body'],
        ),
      );

      if (isset($message['mandrill_template_content'])) {
        $template_content = array_merge($message['mandrill_template_content'], $template_content);
      }

      $response = $this->mandrill_api->sendTemplate($message, $template_map->template_id, $template_content);
    }
    else {
      $response = $this->mandrill_api->send($message);
    }

    if (!empty($response)) {
      return $this->handleSendResponse($response, $message);
    }
    else {
      return FALSE;
    }
  }

  /**
   * @param $message
   * @param $template_id
   * @param $template_content
   * @return bool
   */
  public function sendTemplate($message, $template_id, $template_content) {
    try {
      $response = $this->mandrill_api->sendTemplate($message, $template_id, $template_content);

      return $this->handleSendResponse($response, $message);
    }
    catch (\Exception $e) {
      $this->log->error('Error sending email from %from to %to. @code: @message', array(
        '%from' => $message['from_email'],
        '%to' => $message['to'],
        '@code' => $e->getCode(),
        '@message' => $e->getMessage(),
      ));
      return FALSE;
    }
  }

}

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
  public function isLibraryInstalled();
  public function getMessages($email);
  public function getTemplates();
  public function getSubAccounts();
  public function getWebhooks();
  public function getInboundDomains();
  public function getInboundRoutes();
  public function addInboundDomain($domain);
  public function addWebhook($path, $events, $description = 'Drupal Webhook');
  public function deleteInboundDomain($domain);
  public function addInboundRoute($domain, $pattern, $url);
  public function sendTemplate($message, $template_id, $template_content);
  public function send(array $message);
}

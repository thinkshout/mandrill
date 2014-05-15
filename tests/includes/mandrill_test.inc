<?php
/**
 * @file
 * A virtual Mandrill API implementation for use in testing.
 */

require 'mandrill_exports_test.inc';
require 'mandrill_inbound_test.inc';
require 'mandrill_internal_test.inc';
require 'mandrill_ips_test.inc';
require 'mandrill_messages_test.inc';
require 'mandrill_metadata_test.inc';
require 'mandrill_rejects_test.inc';
require 'mandrill_senders_test.inc';
require 'mandrill_subaccounts_test.inc';
require 'mandrill_tags_test.inc';
require 'mandrill_templates_test.inc';
require 'mandrill_urls_test.inc';
require 'mandrill_users_test.inc';
require 'mandrill_webhooks_test.inc';
require 'mandrill_whitelists_test.inc';

class DrupalMandrillTest extends DrupalMandrill {
  /**
   * @see DrupalMandrill::__construct()
   */
  public function __construct($apikey = NULL, $timeout = 60) {
    parent::__construct($apikey, $timeout);

    // Set up test classes.
    $this->templates = new Mandrill_TemplatesTest($this);
    $this->exports = new Mandrill_ExportsTest($this);
    $this->users = new Mandrill_UsersTest($this);
    $this->rejects = new Mandrill_RejectsTest($this);
    $this->inbound = new Mandrill_InboundTest($this);
    $this->tags = new Mandrill_TagsTest($this);
    $this->messages = new Mandrill_MessagesTest($this);
    $this->whitelists = new Mandrill_WhitelistsTest($this);
    $this->ips = new Mandrill_IpsTest($this);
    $this->internal = new Mandrill_InternalTest($this);
    $this->subaccounts = new Mandrill_SubaccountsTest($this);
    $this->urls = new Mandrill_UrlsTest($this);
    $this->webhooks = new Mandrill_WebhooksTest($this);
    $this->senders = new Mandrill_SendersTest($this);
    $this->metadata = new Mandrill_MetadataTest($this);
  }

  public function getErrorResponse($code, $name, $message) {
    $response = array(
      'status' => 'error',
      'code' => $code,
      'name' => $name,
      'message' => $message,
    );

    return $response;
  }
}

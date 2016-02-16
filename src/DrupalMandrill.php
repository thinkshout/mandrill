<?php
namespace Drupal\mandrill;

use Mandrill;

/**
 * Class DrupalMandrill.
 */
class DrupalMandrill extends Mandrill {

  protected $userAgent;
  protected $timeout;

  /**
   * Override constructor to remove curl operations.
   */
  public function __construct($apikey = NULL, $timeout = 60) {
    if (!$apikey) {
      throw new Mandrill_Error('You must provide a Mandrill API key');
    }
    $this->apikey = $apikey;

    $library = libraries_load('mandrill');
    $this->userAgent = "Mandrill-PHP/{$library['version']}";
    $this->timeout = $timeout;

    $this->root = rtrim($this->root, '/') . '/';

    $this->templates = new Mandrill_Template($this);
    $this->exports = new Mandrill_Exports($this);
    $this->users = new Mandrill_Users($this);
    $this->rejects = new Mandrill_Rejects($this);
    $this->inbound = new Mandrill_Inbound($this);
    $this->tags = new Mandrill_Tags($this);
    $this->messages = new Mandrill_Messages($this);
    $this->whitelists = new Mandrill_Whitelists($this);
    $this->ips = new Mandrill_Ips($this);
    $this->internal = new Mandrill_Internal($this);
    $this->subaccounts = new Mandrill_Subaccounts($this);
    $this->urls = new Mandrill_Urls($this);
    $this->webhooks = new Mandrill_Webhooks($this);
    $this->senders = new Mandrill_Senders($this);
    $this->metadata = new Mandrill_Metadata($this);
  }

  /**
   * Override _destruct() to prevent calling curl_close().
   */
  public function __destruct() {}

  /**
   * Override call method to user Drupal's HTTP handling.
   */
  public function call($url, $params) {
    $params['key'] = $this->apikey;
    $params = \Drupal\Component\Serialization\Json::encode($params);

    $client = \Drupal::httpClient();
    //@TODO: make sure createRequest format is right, in particular that the data is being sent correctly
    // http://docs.guzzlephp.org/en/latest/quickstart.html#post-form-requests
    $request = $client->createRequest('POST',  $this->root . $url . '.json', ['data' => $params]);
    $request->addHeader('Content-Type', 'application/json');
    $request->addHeader('Accept-Language', language_default()->language);
    $request->addHeader('User-Agent', $this->userAgent);

    try {
      $response = $client->send($request, ['timeout' => $this->timeout]);
      // Expected result.
      $data = $response->getBody(TRUE);
      $result = \Drupal\Component\Serialization\Json::decode($data);
    }
    catch (RequestException $e) {
      watchdog_exception('my_module', $e->getMessage());
      throw new Mandrill_HttpError(t('Mandrill API call to %url failed: %msg', array('%url' => $url, '%msg' => $response->error)));
    }

    if ($result === NULL) {
      throw new Mandrill_Error('We were unable to decode the JSON response from the Mandrill API: ' . $response->data);
    }

    if (floor($response->code / 100) >= 4) {
      throw $this->castError($result);
    }

    return $result;
  }

}
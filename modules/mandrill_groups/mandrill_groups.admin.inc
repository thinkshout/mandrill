<?php
/**
 * @file
 * Administrative forms for Mandrill Groups module.
 */

/**
 * Administrative settings.
 *
 * @param array $form
 *   Form render array.
 * @param array $form_state
 *   Array containing form state values.
 *
 * @return array
 *   An array containing form items to place on the module settings page.
 */
function mandrill_groups_settings_form($form, &$form_state) {
  $form = array();

  // Create table of user's Mandrill inbound domains and their statuses.
  $mandrill = mandrill_get_api_object();
  // Set arrays for table rows.
  $rows_valid = array();
  $rows_invalid = array();
  foreach ($mandrill->inbound->domains() as $domain) {

    // Check if MX record for domain is valid.
    $mx_valid = isset($domain['valid_mx']);

    // If MX record is valid, ensure the Mandrill Groups route is configured.
    if ($mx_valid) {
      // Check if Mandrill Groups route is configured for the current domain.
      $routes = $mandrill->inbound->routes($domain['domain']);
      $route_ok = FALSE;
      foreach ($routes as $route) {
        if ($route['url'] == url('mandrill/webhook/inbound', array('absolute' => TRUE))) {
          $route_ok = TRUE;
        }
      }
      if ($route_ok) {
        $domain_status = 'OK';
      }
      else {
        $domain_status = t('Mandrill Groups route not configured. Add the following route to this domain in your <a href="https://mandrillapp.com/inbound">Mandrill inbound configuration</a>: %url', array('%url' => url('mandrill/webhook/inbound', array('absolute' => TRUE))));
      }
    }
    else {
      $domain_status = 'Invalid MX record. Use the "DNS Settings" button in your <a href="https://mandrillapp.com/inbound">Mandrill inbound configuration</a> to ensure your DNS records are set correctly for this domain.';
    }

    // Populate tables.
    if ($domain_status == 'OK') {
      $rows_valid[] = array(
        'domain' => $domain['domain'],
        'status' => $domain_status,
      );
    }
    else {
      $rows_invalid[] = array(
        'domain' => $domain['domain'],
        'status' => $domain_status,
      );
    }
  }
  // Table header.
  $header = array(
    'domain' => t('Domain'),
    'status' => t('Status'),
  );

  // List of valid inbound domains from Mandrill to choose from.
  $form['mandrill_groups_inbound_domains'] = array(
    '#type' => 'tableselect',
    '#header' => $header,
    '#options' => $rows_valid,
    '#default_value' => variable_get('mandrill_groups_inbound_domain'),
    '#empty' => t('<p>There are currently no inbound domains configured in Mandrill. Add one using the "Add Mandrill Domain" textbox below or head to your <a href="https://mandrillapp.com/inbound">Mandrill inbound configuration</a> and modify an existing one.'),
  );

  // List of invalid inbound domains from Mandrill.
  $form['mandrill_groups_invalid_inbound_domains'] = array(
    '#type' => 'tableselect',
    '#header' => $header,
    '#options' => $rows_invalid,
    '#disabled' => TRUE,
  );

  // Option to add a domain to Mandrill if desired one does not appear in table
  // above.
  $form['add_domain'] = array(
    '#type' => 'textfield',
    '#title' => t('Add Mandrill Domain'),
    '#description' => 'Don\'t see the domain you want in the above table? Add it here and click "Save configuration."<br />The Mandrill Groups route for your site will be automatically added to the new domain.',
    '#attributes' => array('placeholder' => t('example.yoursite.com')),
  );
  return system_settings_form($form);
}

/**
 * Validation for mandrill_groups_settings_form.
 *
 * @param array $form
 *   Form render array.
 * @param array $form_state
 *   Array containing form state values.
 */
function mandrill_groups_settings_form_validate($form, &$form_state) {
//  try{
//    $mandrill = mandrill_get_api_object();
//    $domain = $form['mandrill_groups_inbound_domain']['#value'];
//    $pattern = '*';
////    $url = url('mandrill/webhook/inbound', array('absolute' => TRUE));
//    // current ngrok URL for development
//    $url = 'http://56c8a7bf.ngrok.com';
//    $result = $mandrill->inbound->addRoute($domain, $pattern, $url);
//  }
//  catch(Mandrill_Error $e) {
//    // Mandrill errors are thrown as exceptions.
//    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
//    // A mandrill error occurred: Mandrill_Unknown_InboundDomain - Unknown Inbound Domain: mandrill.com
//    throw $e;
//  }

}

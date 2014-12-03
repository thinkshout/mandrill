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

  // Static webhook endpoint.
  // Create table of user's Mandrill inbound domains and their statuses.
  // Set arrays for table rows.
  $rows_valid = array();
  $rows_invalid = array();
  foreach (mandrill_get_inbound_domains() as $domain) {
    // Define operations for Mandrill Inbound Domain.
    $operations = l(t('delete'), 'admin/config/services/mandrill/groups/' . $domain['domain'] . '/delete');

    // Check if MX record for domain is valid.
    $mx_valid = (boolean) $domain['valid_mx'];

    // If MX record is valid, ensure the Mandrill Groups route is configured.
    if ($mx_valid) {
      // Check if Mandrill Groups route is configured for the current domain.
      $routes = mandrill_get_inbound_routes($domain['domain']);
      $route_ok = FALSE;
      foreach ($routes as $route) {
        if ($route['url'] == url("mandrill/webhook/groups", array(
            'absolute' => TRUE,
            'query' => array('domain' => $domain['domain']),
          ))) {
          $route_ok = TRUE;
        }
      }
      if ($route_ok) {
        $domain_status = 'OK';
      }
      else {
        $domain_status = t('Mandrill Groups route not configured. Add the following route to this domain in your <a href="https://mandrillapp.com/inbound">Mandrill inbound configuration</a>: %url',
          array(
            '%url' => url("mandrill/webhook/groups", array(
              'absolute' => TRUE,
              'query' => array(
                'domain' => $domain['domain'],
              ),
            )),
          )
        );
      }
    }
    else {
      $domain_status = 'Invalid MX record. Use the "DNS Settings" button in your <a href="https://mandrillapp.com/groups">Mandrill inbound configuration</a> to ensure your DNS records are set correctly for this domain.';
    }

    // Populate tables.
    if ($domain_status == 'OK') {
      $rows_valid[] = array(
        $domain['domain'],
        $domain_status,
        $operations,
      );
    }
    else {
      $rows_invalid[] = array(
        $domain['domain'],
        $domain_status,
        $operations,
      );
    }
  }

  // Table header.
  $header = array(
    'domain' => t('Domain'),
    'status' => t('Status'),
    'operations' => t('Operations'),
  );

  // List of valid inbound domains from Mandrill to choose from.
  $valid_domains = array(
    'header' => $header,
    'rows' => $rows_valid,
    'caption' => 'Valid Domains',
    'empty' => t('<p>There are currently no valid inbound domains configured in Mandrill. Add one using the "Add Mandrill Domain" textbox below or head to your <a href="https://mandrillapp.com/inbound">Mandrill inbound configuration</a> and modify an existing one.'),
  );
  $output = theme('table', $valid_domains);
  $form['mandrill_groups_valid_inbound_domains'] = array(
    '#type' => 'markup',
    '#markup' => $output,
  );

  // List of invalid inbound domains from Mandrill.
  $invalid_domains = array(
    'header' => $header,
    'rows' => $rows_invalid,
    'caption' => 'Invalid Domains',
  );
  $output = theme('table', $invalid_domains);
  $form['mandrill_groups_invalid_inbound_domains'] = array(
    '#type' => 'markup',
    '#markup' => $output,
  );

  // Option to add a domain to Mandrill if desired one does not appear in table
  // above.
  $form['add_domain'] = array(
    '#type' => 'textfield',
    '#title' => t('Add Mandrill Domain'),
    '#description' => t("Don't see the domain you want in the above table? Add it here and click 'Save configuration.' The Mandrill Groups route for this site will be automatically added to the new domain, but you'll have to configure the MX record to route to Mandrill through your DNS service."),
    '#attributes' => array('placeholder' => t('example.yoursite.com')),
  );

  // Add submit handler that adds domain and route if present.
  $form['#submit'][] = 'mandrill_groups_settings_form_submit';

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
function mandrill_groups_settings_form_submit($form, &$form_state) {
  // Add a new domain if one was provided on the form.
  $domain = $form_state['input']['add_domain'];
  if ($domain) {
    mandrill_add_inbound_domain($domain);
    mandrill_add_inbound_route($domain, '*', url("mandrill/webhook/inbound",
      array(
        'absolute' => TRUE,
        'query' => array('domain' => $domain),
      )
    ));
  }
}

/**
 * Inbound domain deletion form.
 */
function mandrill_groups_inbound_domain_delete_form($form, &$form_state, $domain) {
  // Necessary to pass $domain on to
  // mandrill_groups_inbound_domain_delete_form_submit.
  $form_state['domain'] = $domain;
  return confirm_form($form,
    t('Are you sure you want to delete the Mandrill inbound domain form "%name"?', array('%name' => $domain)),
    t('This action cannot be undone.'),
    t('Delete Mandrill Inbound Domain'));
}

/**
 * Submit handler for mandrill_groups_inbound_domain_delete_form().
 */
function mandrill_groups_inbound_domain_delete_form_submit($form, &$form_state) {
  $domain = $form_state['domain'];
  mandrill_delete_inbound_domain($domain);
  drupal_set_message(t('%name has been deleted.', array('%name' => $domain)));
  $form_state['redirect'] = 'admin/config/services/mandrill/groups';
}

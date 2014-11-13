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
  // @todo - do this automatically using the add-route API call: https://mandrillapp.com/api/docs/inbound.php.html#method=add-route
  $form['current_webhook'] = array(
    '#markup' => t(
      'IMPORTANT: Add this route the below inbound domain in the Mandrill UI: %url', array(
        '%url' => url('mandrill/webhook/inbound', array('absolute' => TRUE)),
      )),
  );

  // Create table of user's Mandrill inbound domains.
  $mandrill = mandrill_get_api_object();
  $rows = array();
  foreach ($mandrill->inbound->domains() as $list) {
    $rows[$list['domain']] = array(
      $list['domain'],
      isset($list['valid_mx']) ? $list['valid_mx'] : t('MX: invalid'),
    );
  }
  $header = array(
    t('Domain'),
    t('DNS'),
  );

  // List of inbound domains from Mandrill to choose from.
  $form['mandrill_groups_inbound_domain'] = array(
    '#type' => 'tableselect',
    '#header' => $header,
    '#options' => $rows,
    '#multiple' => FALSE,
    '#default_value' => variable_get('mandrill_groups_inbound_domain'),
    '#empty' => t('<p>There are currently no inbound domains configured in Mandrill. Configure one here: <a target="_blank" href="https://mandrillapp.com/inbound">https://mandrillapp.com/inbound</a></p>'),
  );
  // @todo - add "Add inbound domain" button so the users doesn't have to go to Mandrill UI
  // should be able to easily leverage the add-domain API hook: https://mandrillapp.com/api/docs/inbound.php.html#method=add-domain

  return system_settings_form($form);
}

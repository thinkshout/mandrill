<?php
/**
 * @file
 * Administrative forms for Mandrill module.
 */


/**
 * Administrative settings.
 *
 * @return
 *   An array containing form items to place on the module settings page.
 */
function mandrill_admin_settings($form, &$form_state) {
  $key = variable_get('mandrill_api_key');
  $form['mandrill_api_key'] = array(
    '#title' => t('Mandrill API Key'),
    '#type' => 'textfield',
    '#description' => t('Create or grab your API key from the !link.',
      array('!link' => l('Mandrill settings', 'https://mandrillapp.com/settings/index'))),
    '#default_value' => $key
  );

  if ($key) {
    $mailsystem_config_keys = mailsystem_get();
    $in_use = FALSE;
    $usage_rows = array();
    foreach($mailsystem_config_keys as $key => $sys) {
      if ($sys === 'MandrillMailSystem' && $key != 'mandrill') {
        $in_use = TRUE;
        $usage_rows[] = array(
          $key,
          $sys,
        );
      }
    }
    if ($in_use) {
      $usage_array = array(
        '#theme' => 'table',
        '#header' => array(
          t('Module Key'),
          t('Mail System'),
          ),
        '#rows' => $usage_rows,
      );
      $form['mandrill_status'] = array(
        '#type' => 'markup',
        '#markup' => t('Mandrill is currently configured to be used by the following Modules. To change these settings or configure additional systems to use Mandrill, use !link.<br /><br />'
            . drupal_render($usage_array),
          array('!link' => l('Mail System', 'admin/config/system/mailsystem'))),
      );
    }
    else {
      drupal_set_message(t('PLEASE NOTE: Mandrill is not currently configured for use by Drupal. In order to route your email through Mandrill, you must configure at least one MailSystemInterface (other than mandrill) to use "MandrillMailSystem" in !link, or you will only be able to send Test Emails through Mandrill.',
        array('!link' => l('Mail System', 'admin/config/system/mailsystem'))),'warning');
    }

    $form['email_options'] = array(
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#title' => t('Email options'),
    );
    $form['email_options']['mandrill_from'] = array(
      '#title' => t('From address'),
      '#type' => 'textfield',
      '#description' => t('The sender email address. If this address has not been verified, messages will be queued and not sent until it is.'),
      '#default_value' => variable_get('mandrill_from', variable_get('site_mail'))
    );
    $form['email_options']['mandrill_from_name'] = array(
      '#type' => 'textfield',
      '#title' => t('From name'),
      '#default_value' => variable_get('mandrill_from_name', ''),
      '#description' => t('Optionally enter a from name to be used.'),
    );

    $formats = filter_formats();
    $options = array('' => t('-- Select --'));
    foreach ($formats AS $v => $format) {
      $options[$v] = $format->name;
    }
    $form['email_options']['mandrill_filter_format'] = array(
      '#type' => 'select',
      '#title' => t('Input format'),
      '#description' => t('If selected, the input format to apply to the message body before sending to the Mandrill API.'),
      '#options' => $options,
      '#default_value' => array(variable_get('mandrill_filter_format', 'full_html')),
    );

    $form['send_options'] = array(
      '#title' => t('Send options'),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
    );
    $form['send_options']['mandrill_track_opens'] = array(
      '#title' => t('Track opens'),
      '#type' => 'checkbox',
      '#description' => t('Whether or not to turn on open tracking for messages.'),
      '#default_value' => variable_get('mandrill_track_opens', TRUE)
    );
    $form['send_options']['mandrill_track_clicks'] = array(
      '#title' => t('Track clicks'),
      '#type' => 'checkbox',
      '#description' => t('Whether or not to turn on click tracking for messages.'),
      '#default_value' => variable_get('mandrill_track_clicks', TRUE)
    );
    $form['send_options']['mandrill_url_strip_qs'] = array(
      '#title' => t('Strip query string'),
      '#type' => 'checkbox',
      '#description' => t('Whether or not to strip the query string from URLs when aggregating tracked URL data.'),
      '#default_value' => variable_get('mandrill_url_strip_qs', FALSE)
    );
    $form['send_options']['mandrill_mail_key_blacklist'] = array(
      '#title' => t('Content logging blacklist'),
      '#type' => 'textarea',
      '#description' => t('Comma delimited list of Drupal mail keys to exclude content logging for. CAUTION: Removing the default password reset key may expose a security risk.'),
      '#default_value' => mandrill_mail_key_blacklist()
    );

    $form['analytics'] = array(
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#title' => t('Google analytics'),
    );
    $form['analytics']['mandrill_analytics_domains'] = array(
      '#title' => t('Google analytics domains'),
      '#type' => 'textfield',
      '#description' => t('One or more domains for which any matching URLs will automatically have Google Analytics parameters appended to their query string. Separate each domain with a comma.'),
      '#default_value' => variable_get('mandrill_analytics_domains', '')
    );
    $form['analytics']['mandrill_analytics_campaign'] = array(
      '#title' => t('Google analytics campaign'),
      '#type' => 'textfield',
      '#description' => t('The value to set for the utm_campaign tracking parameter. If this isn\'t provided the messages from address will be used instead.'),
      '#default_value' => variable_get('mandrill_analytics_campaign', '')
    );

    $form['#submit'][] = 'mandrill_admin_settings_submit';
  }

  return system_settings_form($form);
}

/**
 * Return a form for sending a test email.
 *
 * @param string $form
 * @param string $form_state
 *
 * @return array $form
 */
function mandrill_test_form($form, &$form_state) {
  drupal_set_title(t('Send test email'));

  $form['mandrill_test_address'] = array(
    '#type' => 'textfield',
    '#title' => t('Email address to send a test email to'),
    '#default_value' => variable_get('site_mail', ''),
    '#description' => t('Type in an address to have a test email sent there.'),
    '#required' => TRUE
  );
  $form['mandrill_test_body'] = array(
    '#type' => 'textarea',
    '#title' => t('Test body contents'),
    '#default_value' => t('If you receive this message it means your site is capable of using Mandrill to send email. This url is here to test click tracking: !link',
      array('!link' => l('link', 'http://www.drupal.org/project/mandrill'))),
  );
  $form['include_attachment'] = array(
    '#title' => t('Include attachment'),
    '#type' => 'checkbox',
    '#description' => t('If checked, the Drupal icon will be included as an attachment with the test email.'),
    '#default_value' => TRUE
  );

  $form['test_submit'] = array(
    '#type' => 'submit',
    '#value' => t('Send test email'),
  );
  $form['test_cancel'] = array(
    '#type' => 'link',
    '#href' => 'admin/config/services/mandrill',
    '#title' => t('Cancel'),
  );

  return $form;
}

/**
 * Submit handler for mandrill_test_form(), sends the test email.
 *
 * @param string $form
 * @param string $form_state
 *
 * @return void
 */
function mandrill_test_form_submit($form, &$form_state) {
  // If an address was given, send a test email message.
  $test_address = $form_state['values']['mandrill_test_address'];
  global $language;
  $params['subject'] = t('Drupal Mandrill test email');
  $params['body'] = $form_state['values']['mandrill_test_body'];
  $params['include_attachment'] = $form_state['values']['include_attachment'];
  $cached_setting = mailsystem_get()['mandrill'];
  mailsystem_set(array('mandrill' => 'MandrillMailSystem'));
  $result = drupal_mail('mandrill', 'mandrill_test', $test_address, $language, $params, variable_get('mandrill_from', ''));
  mailsystem_set(array('mandrill' => $cached_setting));
  if (isset($result['result']) && $result['result'] === true) {
    drupal_set_message(t('A test email has been sent to @email.', array('@email' => $test_address)));
  }
}

/**
 * Return a form for adding/editing a Mandrill template map.
 *
 * @param array $form
 * @param array $form_state
 * @param object $list
 */
function mandrill_template_map_form($form, &$form_state, MandrillTemplateMap $map = NULL, $op = 'edit') {
  if ($op == 'clone') {
    $map->label .= ' (cloned)';
    $map->name = '';
  }
  // store the existing list for updating on submit
  if (isset($map)) {
    $form_state['map'] = $map;
  }

  $form['label'] = array(
    '#type' => 'textfield',
    '#title' => t('Label'),
    '#description' => t('The name of this Template Mapping.'),
    '#size' => 35,
    '#maxlength' => 32,
    '#default_value' => $map ? $map->label : '',
    '#required' => TRUE
  );

  // Machine-readable list name.
  $form['name'] = array(
    '#type' => 'machine_name',
    '#default_value' => isset($map->name) ? $map->name : '',
    '#maxlength' => 32,
    '#machine_name' => array(
      'exists' => 'mandrill_template_map_load_entities',
      'source' => array('label'),
    ),
    '#description' => t('A unique machine-readable name for this template map. It must only contain lowercase letters, numbers, and underscores.'),
  );

  $form['map_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Template Map Settings'),
    '#collapsible' => TRUE,
  );

  $templates = mandrill_get_templates();
  // check if the currently configured template still exists
  if (!empty($map->template_id) && !array_key_exists($map->template_id, $templates)) {
    drupal_set_message(t('The configured Mandrill template is no longer available, please select a valid one.'));
  }
  if (!empty($templates)) {
    $options = array('' => t('-- Select --'));
    $regions = array();
    foreach ($templates as $template) {
      $options[$template['name']] = $template['name'];
      $regions[$template['name']] = _mandrill_parse_regions($template['publish_code'], 'mc:edit');
    }
    $form['map_settings']['template_id'] = array(
      '#type' => 'select',
      '#title' => t('Email Template'),
      '#description' => t('Select a Mandrill template.'), //https://mandrillapp.com/templates/my-templates
      '#options' => $options,
      '#default_value' => isset($map->template_id) ? $map->template_id : '',
    );/*
    $form['map_settings']['main_section'] = array(
      '#type' => 'select',
      '#title' => t('Select the main content region'),
      '#options' => $regions[$map->template_id],
      '#default_value' => isset($map->main_section) ? $map->main_section : '',
    );*/

  }
  else {
    $form['email_options']['#description'] = t('The template selection is only available if the Mandrill API is correctly configured and available.');
  }

  $form['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
/*
  $form['delete'] = array(
    '#type' => 'submit',
    '#value' => t('Delete'),
    '#access' => isset($map),
    '#submit' => array('mandrill_template_map_delete_submit'),
  );
*/
  $form['cancel'] = array(
    '#type' => 'link',
    '#title' => t('Cancel'),
    '#href' => 'admin/config/services/mandrill/templates'
  );

  return $form;
}

function mandrill_template_maps_overview_page() {
  //$maps = mandrill_template_map_load_entities();

  $rows = array();
  /*
  foreach ($maps as $map) {
  $mc_list = mailchimp_get_list($list->mc_list_id);
  $actions = array(
    l(t('Edit'), 'admin/config/services/mailchimp/lists/' . $list->id . '/edit'),
    l(t('Delete'), 'admin/config/services/mailchimp/lists/' . $list->id . '/delete')
  );
  if ($list->list_type !== MAILCHIMP_LISTTYPE_FREEFORM && isset($list->settings['cron']) && $list->settings['cron']) {
    $actions[] = l(t('Queue existing'), 'mailchimp/lists/' . $list->id . '/queue_existing');
  }
  $rows[] = array(
    l($list->label, 'admin/config/services/mailchimp/lists/' . $list->id . '/edit'),
    l($mc_list['name'], 'https://admin.mailchimp.com/lists/dashboard/overview?id=' . $mc_list['web_id']),
    $list->description,
    $list->list_type,
    implode(' | ', $actions)
  );
  }
  */
  $table = array(
    'header' => array(
      t('Name'),
      t('Mandrill Template'),
      t('Primary Zone'),
      t('In Use'),
    ),
    'rows' => $rows,
  );
  /*$mc_lists = mailchimp_get_lists();
  if (empty($mc_lists)) {
    drupal_set_message(t('You don\'t have any lists configured in your MailChimp account, (or you haven\'t configured your API key correctly on the Global Settings tab). Head over to !link and create some lists, then come back here and click "Refresh lists from MailChimp!"',
      array('!link' => l('MailChimp', 'https://admin.mailchimp.com/lists/'))),'warning');
  }
  else {
    $options = 'Currently Available MailChimp lists:<i>';
    foreach ($mc_lists as $mc_list) {
      $options .= ' ' . $mc_list['name'] . ',';
    }
    $options = rtrim($options,',');
    $options .= ".</i>";
    $table['caption'] = $options;
  }
  */
  return theme('table', $table);
}

function _mandrill_parse_regions($html, $tag){
  $instances = array();
  $offset = 0;
  $inst = NULL;
  while ($offset = strpos($html, $tag, $offset)) {
    $start = 1 + strpos($html, '"', $offset);
    $length = strpos($html, '"', $start) - $start;
    $inst = substr($html, $start, $length);
    $instances[$inst] = '';
    $offset = $start + $length;
  }
  return $instances;
}

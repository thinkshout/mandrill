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
      array('!link' => l(t('Mandrill settings'), 'https://mandrillapp.com/settings/index'))),
    '#default_value' => $key
  );

  if ($key) {
    $mailsystem_config_keys = mailsystem_get();
    $in_use = FALSE;
    $usage_rows = array();
    foreach ($mailsystem_config_keys as $key => $sys) {
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
        '#markup' => t('Mandrill is currently configured to be used by the following Module Keys. To change these settings or configure additional systems to use Mandrill, use !link.<br /><br />'
            . drupal_render($usage_array),
          array('!link' => l(t('Mail System'), 'admin/config/system/mailsystem'))),
      );
    }
    else {
      drupal_set_message(t('PLEASE NOTE: Mandrill is not currently configured for use by Drupal. In order to route your email through Mandrill, you must configure at least one MailSystemInterface (other than mandrill) to use "MandrillMailSystem" in !link, or you will only be able to send Test Emails through Mandrill.',
        array('!link' => l(t('Mail System'), 'admin/config/system/mailsystem'))), 'warning');
    }

    $form['email_options'] = array(
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#title' => t('Email options'),
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
    $form['email_options']['mandrill_reply_to'] = array(
      '#type' => 'checkbox',
      '#title' => t('Set the Reply-To header'),
      '#default_value' => variable_get('mandrill_reply_to', FALSE),
      '#description' => t("Set the <code>Reply-To</code> header using the original email's <code>From</code> header."),
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
      array('!link' => l(t('link'), 'http://www.drupal.org/project/mandrill'))),
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
  $mailsystem = mailsystem_get();
  //check for empty mailsystem config for Mandrill:
  if (empty($mailsystem['mandrill'])) {
    drupal_set_message(t('Automatically setting Mandrill tests to go through Mandrill: MandrillMailSystem was not configure in Mail System.'));
    mailsystem_set(array('mandrill' => 'MandrillMailSystem'));
  }
  //check for wrong mailsystem config for Mandrill, if not empty, and issue a warning:
  else if ($mailsystem['mandrill'] != 'MandrillMailSystem') {
    drupal_set_message(
      t('Mail System is configured to send Mandrill Test messages through %system, not Mandrill. To send tests through Mandrill, go to !link and change the setting.',
        array(
          '%system' => $mailsystem['mandrill'],
          '!link' => l(t('Mandrill'), 'https://mandrillapp.com/templates'))),
      'warning');
    // hack because we are apparently formatting the body differently than default drupal messages
    $params['body'] = array('0' => $params['body']);
  }
  $result = drupal_mail('mandrill', 'mandrill_test', $test_address, $language, $params);
}

/**
 * Return a form for adding/editing a Mandrill template map.
 *
 * @param array $form
 * @param array $form_state
 * @param object $map
 */
function mandrill_template_map_form($form, &$form_state, MandrillTemplateMap $map = NULL, $op = 'edit') {
  $form_state['op'] = $op;
  if ($form_state['op'] == 'clone') {
    $map->label .= ' (cloned)';
    $map->name = '';
  }
  // store the existing map for updating on submit
  if (isset($map)) {
    $form_state['map'] = $map;
  }
  else {
    $form_state['op'] = 'add';
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
    '#collapsible' => FALSE,
    '#attributes' => array(
      'id' => array('mandrill-template-mapping'),
    ),
  );

  $templates = mandrill_get_templates();
  $template_names = array();
  foreach ($templates as $template) {
    $template_names[$template['name']] = $template;
  }
  // check if the currently configured template still exists
  if (!empty($map->template_id) && !array_key_exists($map->template_id, $template_names)) {
    drupal_set_message(t('The configured Mandrill template is no longer available, please select a valid one.'), 'warning');
  }
  if (!empty($templates)) {
    $options = array('' => t('-- Select --'));
    foreach ($templates as $template) {
      $options[$template['name']] = $template['name'];
    }
    $form['map_settings']['template_id'] = array(
      '#type' => 'select',
      '#title' => t('Email Template'),
      '#description' => t('Select a Mandrill template.'), //https://mandrillapp.com/templates/my-templates
      '#options' => $options,
      '#default_value' => isset($map->template_id) ? $map->template_id : '',
      '#required' => TRUE,
      '#ajax' => array(
        'callback' => 'mandrill_template_map_form_callback',
        'wrapper' => 'mandrill-template-mapping'
      ),
    );
    $form_template_id = & $form_state['values']['template_id'];

    if (!$form_template_id && isset($map->mandrill_template_map_entity_id)) {
      $form_template_id = $map->template_id;
    }

    if ($form_template_id) {
      $regions = array('' => t('-- Select --')) + _mandrill_parse_regions($template_names[$form_template_id]['publish_code']);
      $form['map_settings']['main_section'] = array(
        '#type' => 'select',
        '#title' => t('Template region'),
        '#description' => t('Select the template region to use for email content.'),
        '#options' => $regions,
        '#default_value' => isset($map->main_section) ? $map->main_section : '',
        '#required' => TRUE,
      );
    }
    $usable_keys = mandrill_template_map_usage_chart();
    $module_names = _mandrill_get_module_key_names();
    $mandrill_in_use = FALSE;
    $available_modules = FALSE;
    $mailsystem_options = array('' => t('-- None --'));
    foreach ($usable_keys as $key => $sys) {
      $mandrill_in_use = TRUE;
      if ($sys === NULL || (isset($map) && $sys == $map->mandrill_template_map_entity_id)) {
        $mailsystem_options[$key] = $module_names[$key];
        $available_modules = TRUE;
      }
    }

    if ($mandrill_in_use) {
      asort($mailsystem_options);
      $form['mailsystem_key'] = array(
        '#type' => 'select',
        '#title' => t('Email key'),
        '#description' => t(
          'Select a module and mail key to use this template for outgoing email. Note that if an email has been selected in another Template Mapping, it will not appear in this list. These keys are defined through the !MailSystem interface.',
          array('!MailSystem' => l(t('MailSystem'), 'admin/config/system/mailsystem'))
        ),
        '#options' => $mailsystem_options,
        '#default_value' => isset($map->mailsystem_key) ? $map->mailsystem_key : '',
      );
      if (!$available_modules) {
        drupal_set_message(t("All email-using modules that have been assigned to Mandrill are already assigned to other template maps"), 'warning');
      }
    }

    if (!$mandrill_in_use) {
      drupal_set_message(t("You have not assigned any Modules to use Mandrill: to use this template, make sure Mandrill is assigned in Mailsystem."), 'warning');
    }
  }
  else {
    $form['email_options']['#description'] = t('The template selection is only available if the Mandrill API is correctly configured and available.');
  }

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  $form['actions']['cancel'] = array(
    '#type' => 'link',
    '#title' => t('Cancel'),
    '#href' => 'admin/config/services/mandrill/templates'
  );

  return $form;
}

/**
 * Submit handler for mandrill_template_map(), creates the entity.
 *
 * @param string $form
 * @param string $form_state
 *
 * @return void
 */
function mandrill_template_map_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  $map = array();
  if ($form_state['op'] == 'add' || $form_state['op'] == 'clone') {
    $map = entity_create('mandrill_template_map', $values);
  }
  else {
    $map = $form_state['mandrill_template_map'];
    foreach ($values as $key => $val) {
      $map->{$key} = $val;
    }
  }
  $map->save();
  $form_state['redirect'] = 'admin/config/services/mandrill/templates';
}

/**
 * Build a page providing a table display of mandrill_template_maps.
 *
 * @return string
 */
function mandrill_template_map_overview_page() {
  $maps = mandrill_template_map_load_entities();
  $key_names = _mandrill_get_module_key_names();
  $rows = array();

  foreach ($maps as $map) {
    $in_use = '-- not in use --';
    if (isset($key_names[$map->mailsystem_key])) {
      $in_use = $key_names[$map->mailsystem_key];
    }
    $actions = array(
      l(t('Edit'), 'admin/config/services/mandrill/templates/manage/' . $map->mandrill_template_map_entity_id . '/edit'),
      l(t('Clone'), 'admin/config/services/mandrill/templates/manage/' . $map->mandrill_template_map_entity_id . '/clone'),
      l(t('Delete'), 'admin/config/services/mandrill/templates/manage/' . $map->mandrill_template_map_entity_id . '/delete')
    );
    $rows[] = array(
      l($map->label, 'admin/config/services/mandrill/templates/manage/' . $map->mandrill_template_map_entity_id),
      l($map->template_id, 'https://mandrillapp.com/templates/preview?id=' . $map->template_id),
      $map->main_section,
      $in_use,
      implode(' | ', $actions)
    );
  }

  $table = array(
    'header' => array(
      t('Name'),
      t('Mandrill Template'),
      t('Primary Content Zone'),
      t('In Use By'),
      t('Actions'),
    ),
    'rows' => $rows,
  );
  $templates = mandrill_get_templates();
  if (empty($templates)) {
    drupal_set_message(t('You don\'t have any templates configured in your Mandrill account, (or you haven\'t configured your API key correctly on the Settings tab). Head over to !link and create some templates, then come back!',
      array('!link' => l(t('Mandrill'), 'https://mandrillapp.com/templates'))), 'warning');
  }
  else {
    $options = 'Currently Available Mandrill Templates:<i>';
    foreach ($templates as $template) {
      $options .= ' ' . $template['name'] . ',';
    }
    $options = rtrim($options, ',');
    $options .= ".</i>";
    $table['caption'] = $options;
  }

  return theme('table', $table);
}

/**
 * Javascript callback for the mandrill_template_map_form.
 *
 * @param $form
 * @param $form_state
 * @return mixed
 */
function mandrill_template_map_form_callback($form, &$form_state) {
  return $form['map_settings'];
}

/**
 * The Mandrill API does not provide an elegant data structure
 * containing the mc:edit tags for a given template, but rather
 * a big pile of ugly HTML containing the tags. We need to parse
 * through it.
 *
 * @param $html
 * @param $tag
 * @return array
 */
function _mandrill_parse_regions($html, $tag = 'mc:edit') {
  $instances = array();
  $offset = 0;
  $inst = NULL;
  while ($offset = strpos($html, $tag, $offset)) {
    $start = 1 + strpos($html, '"', $offset);
    $length = strpos($html, '"', $start) - $start;
    $inst = substr($html, $start, $length);
    $instances[$inst] = $inst;
    $offset = $start + $length;
  }
  return $instances;
}

/**
 * This function is used when we want to display the names of the modules
 * that are using Mailsystem in a way that is informative in the UI
 * and consistent with Mailsystem's display. In the future, if Mailsystem
 * were to provide an API for their labeling, that should be used here.
 *
 * @return array of all module names indexing to their "display" names,
 * and some special items for non-module values like null, default-system,
 * and some clarification talked onto the end of the Mandrill module's name.
 */
function _mandrill_get_module_key_names() {
  $name_array = array(
    '' => '--none--',
    'default-system' => "Site-wide default",
  );
  $descriptions = array();
  foreach (system_rebuild_module_data() as $item) {
    if ($item->status) {
      $descriptions[$item->name] = (
      empty($item->info['package'])
        ? '' : $item->info['package']
      ) . ' » ' . t('!module module', array('!module' => $item->info['name']));
    }
  }
  asort($descriptions);
  $mailsystem_settings = mailsystem_get();
  unset($mailsystem_settings['default-system']);
  foreach ($mailsystem_settings as $id => $class) {
    // Separate $id into $module and $key.
    $module = $id;
    while ($module && empty($descriptions[$module])) {
      // Remove a key from the end
      $module = implode('_', explode('_', $module, -1));
    }
    // If an array key of the $mail_system variable is neither "default-system"
    // nor begins with a module name, then it should be unset.
    if (empty($module)) {
      //this shouldn't happen
    }
    // Set $title to the human-readable module name.
    $title = preg_replace('/^.* » /', '', $descriptions[$module]);
    if ($key = substr($id, strlen($module) + 1)) {
      $title .= " ($key key)";
    }
    if ($module == 'mandrill') {
      $title .= " (send test email)";
    }
    $name_array[$id] = $title;
  }

  return $name_array;
}

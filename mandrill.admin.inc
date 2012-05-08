<?php
/**
 * @file
 * Administrative forms for Mandrill module.
 */

/**
 * Page callback for mandrill admin settings.
 */
function mandrill_admin_page() {
  $mandrill = mandrill_get_api_object();
  
  // display error if there is no api key.
  if (!$mandrill) {
    // return render array.
    return array(
      '#markup' => t('A !link must be set to use Mandrill.',
        array('!link' => l('Mandrill API key', 'admin/config/services/mandrill')))
    );
  }
  
  $response = $mandrill->list_verified_email_addresses();
  if (!$mandrill->errorCode) {
    $emails = array();
    if (!isset($response['email_addresses']) || count($response['email_addresses']) < 1) {
      drupal_set_message(t('You need to add at least one verified email address to use Mandrill.'), 'warning');
    }
    else {
      foreach ($response['email_addresses'] AS $email) {
        // filter out MailChimp transactional emails
        if (!empty($email) && !stripos($email, 'transact.mcsv.net')) {
          $emails[$email] = $email;
        }
      }
    }

    $message = t('Enable and configure this site to use the !mandrill
      for sending emails. You may also view the !reports.',
      array(
        '!reports' => l(t('Mandrill quotas, statistics, and reports'), 'admin/reports/mandrill'),
        '!mandrill' => l(t('Mandrill API'), 'mandrillapp.com/api/docs')
      ));

    // return render array.
    return array(
      'message' => array('#markup' => $message),
      'form' => drupal_get_form('mandrill_admin_settings', $emails)
    );
  }
  else {
    drupal_set_message(
      t('There was a problem accessing the Mandrill service: %msg',
      array('%msg' => $mandrill->errorMessage)
    ), 'warning');

    return t('Refer to the !link for properly configuring Mandrill.',
      array('!link' => l('MailChimp Documentation',
      'http://kb.mailchimp.com/article/how-does-mailchimp-integrate-with-amazon-ses'
    )));
  }
}

/**
 * Administrative settings.
 *
 * @return
 *   An array containing form items to place on the module settings page.
 */
function mandrill_admin_settings($form, &$form_state, $verified_emails = array()) {
  $form['mandrill_status'] = array(
    '#type'          => 'radios',
    '#title'         => t('Mandrill Mail interface status'),
    '#default_value' => variable_get('mandrill_status', mandrill_STATUS_OFF),
    '#options'       => array(
      mandrill_STATUS_ON => t('On'),
      mandrill_STATUS_TEST => t('Test'),
      mandrill_STATUS_OFF => t('Off')
    ),
    '#description'   => t('Setting to on routes all site emails through the Mandrill
      API. Test mode implements an alternate mail interface,
      TestingMailChimpMandrillMailSystem, that does not send any emails, it just
      displays a message and logs the event.'),
  );

  $form['email_options'] = array(
    '#type'  => 'fieldset',
    '#collapsible' => TRUE,
    '#title' => t('Email options'),
  );
  $form['email_options']['mandrill_from'] = array(
    '#type'          => 'select',
    '#title'         => t('Email from address'),
    '#default_value' => variable_get('mandrill_from', ''),
    '#description'   => t('Select a verified email address. All email will be sent as this address.'),
    '#options' => $verified_emails,
  );
  $form['email_options']['mandrill_from_new'] = array(
    '#type' => 'fieldset',
    '#title' => t('Verify a new email address'),
    '#description' => t('A confirmation message will be sent to this address.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['email_options']['mandrill_from_new']['new_address'] = array(
    '#type' => 'textfield',
    '#title' => 'New Email address',
  );
  $form['email_options']['mandrill_from_new']['new_address_submit'] = array(
    '#type' => 'submit',
    '#value' => t('Add email'),
    '#submit' => array('mandrill_admin_new_address_submit'),
  );
  $form['email_options']['mandrill_from_name'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Email from name'),
    '#default_value' => variable_get('mandrill_from_name', ''),
    '#description'   => t('Optionally enter a from name to be used.'),
  );

  $formats = filter_formats();
  $options = array('' => t('-- Select --'));
  foreach ($formats AS $v => $format) {
    $options[$v] = $format->name;
  }
  $form['email_options']['mandrill_filter_format'] = array(
    '#type' => 'select',
    '#title' => t('Input format'),
    '#description' => t('If selected, the input format to apply to the message
      body before sending to the Mandrill API.'),
    '#options' => $options,
    '#default_value' => array(variable_get('mandrill_filter_format', 'full_html')),
  );

  $form['#submit'][] = 'mandrill_admin_settings_submit';
  return system_settings_form($form);
}

/**
 * Validataion for the administrative settings form.
 *
 * @param form
 *   An associative array containing the structure of the form.
 * @param form_state
 *   A keyed array containing the current state of the form.
 */
function mandrill_admin_settings_validate($form, &$form_state) {
  if (isset($form_state['submit_handlers'][0]) &&
      $form_state['submit_handlers'][0] == 'mandrill_admin_new_address_submit') {
    if (empty($form_state['values']['new_address']) || !valid_email_address($form_state['values']['new_address'])) {
      form_set_error('new_address', t('A valid email address is required.'));
    }
  }
  else {
    // unset form fields we don't want to save
    unset($form_state['values']['new_address']);
  }
}

/**
 * Submission for the administrative settings form.
 *
 * @param form
 *   An associative array containing the structure of the form.
 * @param form_state
 *   A keyed array containing the current state of the form.
 */
function mandrill_admin_settings_submit($form, &$form_state) {
  $values = $form_state['values'];

  switch ($form_state['values']['mandrill_status']) {
    case mandrill_STATUS_ON:
      variable_set('mail_system', array('default-system' => 'MailChimpMandrillMailSystem'));
      drupal_set_message(t('Mandrill will be used to deliver all site emails.'));
      watchdog('mandrill', 'Mandrill has been enabled.');
    break;
    case mandrill_STATUS_TEST:
      variable_set('mail_system', array('default-system' => 'TestingMailChimpMandrillMailSystem'));
      drupal_set_message(t('Mandrill will be used in test mode. Emails will
        not actually be sent, just logged.'));
      watchdog('mandrill', 'Mandrill has been placed in test mode.');
    break;
    case mandrill_STATUS_OFF:
      variable_set('mail_system', array('default-system' => 'DefaultMailSystem'));
      drupal_set_message(t('The default mail system will be used to deliver all site emails.'));
      watchdog('mandrill', 'Mandrill has been disabled.');
    break;
  }
}

/**
 * Submission for the administrative settings form - new address.
 *
 * @param form
 *   An associative array containing the structure of the form.
 * @param form_state
 *   A keyed array containing the current state of the form.
 */
function mandrill_admin_new_address_submit($form, &$form_state) {
  $address = $form_state['values']['new_address'];
  if ($address) {
    $mandrill = mandrill_get_api_object();
    $new_address = $mandrill->verify_email_address($address);
    if ($mandrill->errorCode) {
      drupal_set_message(t('There has been an error verifying your message: %msg.',
        array('%msg' => $mandrill->errorMessage)));
    }
    else {
      drupal_set_message(t('The address %address has been verified. Please check
        your email for a confirmation message.', array('%address' => $address)));
    }
  }
}

/**
 * Return a form for sending a test email.
 *
 * @param string $form
 * @param string $form_state
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
    '#default_value' => t('If you receive this message it means your site is capable of using Mandrill to send email.'),
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
 * @return void
 */
function mandrill_test_form_submit($form, &$form_state) {
  // If an address was given, send a test email message.
  $test_address = $form_state['values']['mandrill_test_address'];
  global $language;
  $params['subject'] = t('Drupal Mandrill test email');
  $params['body'] = $form_state['values']['mandrill_test_body'];
  drupal_mail('mandrill', 'mandrill-test', $test_address, $language, $params, variable_get('mandrill_from', ''));
  drupal_set_message(t('A test email has been sent to @email.', array('@email' => $test_address)));
}

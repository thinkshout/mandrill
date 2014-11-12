-- SUMMARY --

This module provides the ability to create and manage group discussions in the
Drupal UI. Discussions can be participated in via Drupal or email.

-- FEATURES --
@todo
The main module provides 'Incoming message is received' event for Rules module with related variables.

Also it has integration with some modules:
  * Privatemsg - Users will be able reply to private messages by email.
  * Subscriptions - Users will be able reply to comments by email.

-- REQUIREMENTS --

* Features
* Mandrill
* Organic Groups

-- INSTALLATION --
@todo
1. Ensure Mandrill module is enabled and configured for your Mandrill account.
    If you're having trouble configuring Mandrill with your API Key make sure
    you have the Mandrill PHP Library available. See the Mandrill project page
    for more details: https://www.drupal.org/project/mandrill.
2. Enable 'Mandrill Groups' module.
2. Go to 'admin/config/services/mandrill/groups' and add an email domain. (in.example.com)
3. Go to https://mandrillapp.com/inbound and add an Inbound Domain. (in.example.com)
4. Add a route for the new domain:
  - Route: *@in.example.com
  - Webhook: http://example.com/mandrill/webhook/inbound

You can manage your Mandrill webhooks here: https://www.mandrillapp.com/settings/webhooks/

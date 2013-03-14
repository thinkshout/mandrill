Integration with Mandrill transactional emails, a service by the folks behind
MailChimp. Learn more about Mandrill and how to sign up on [their website] 
(http://mandrill.com).

NOTE: If you installed version 1.3, you will get the following error message 
when enabling the mandrill_template module: 
DatabaseSchemaObjectExistsException:
Table <em class="placeholder">mandrill_template_map</em> already exists.
This is ugly but irrelevant, everything should function normally.

You may also find an extra Mail System class in the Mail System 
configuration called "Mandrill module class". It's harmless, 
but feel free to \delete it.

## Settings

### Mandrill Mail interface is 'enabled' by using the [Mail System module]
(http://drupal.org/project/mailsystem)

### Email Options
* **From address:** The email address that emails should be sent from
* **From name:** The name to use for sending
* **_Input format_:** An optional input format to apply to the message body 
before sending emails

### Send Options
* **Track opens:** Toggles open tracking for messages
* **Track clicks:** Toggles click tracking for messages
* **Strip query string:** Strips the query string from URLs when aggregating
 tracked URL data
* **Log sends from module/key pairs that are not registered independently in 
mailsystem:** You can use this when Mandrill is configured as the 
site-default email sender to have Mandrill log sends from any module/key 
pairs that aren't already registered. 

### Google Analytics
* **Domains:** One or more domains for which any matching URLs will
automatically have Google Analytics parameters appended to their query string.
Separate each domain with a comma.
* **Campaign:** The value to set for the utm_campaign tracking parameter. If
empty, the from address of the message will be used instead.

## Reports
The mandrill_reports sub-module provides reports on various metrics.

### Asynchronous Options
* **Queue Outgoing Messages**
Drops all messages sent through Mandrill into a queue without sending them. 
When Cron is triggered, a number of queued messages are sent equal to the 
configured:
* **Batch Size**
The number of messages to send when Cron triggers. Any number less than 1 
allows all queued messages to send each time Cron triggers.

### Dashboard
Displays charts that show volume and engagement, along with a tabular list of
URL interactions for the past 30 days.

### Account Summary
Shows account information, quotas, and all-time usage stats.

## Altering Messages
If you would like to use additional template (or other) sending variables not
implemented in this module, set them in hook_mail_alter under:
$params['mandrill']
Make sure that your key matches the API key and that your structers match the
Mandrill API specifications exactly, as they will be passed exactly as-is.

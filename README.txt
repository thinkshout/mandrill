Integration with the Mandrill transactional email service.

You can follow instructions on how to do that and read more in the 
[Mandrill Documentation](http://mandrill.com)

## Settings

### Mandrill Mail interface status 
* On: Setting to "On" routes all site emails through the STS API. 
* Test: Test mode implements an alternate mail interface, 
TestingMailChimpMandrillMailSystem, that does not send any emails, it just displays
a message and logs the event. You can view the logs in the Mandrill Reports 
located at http://example.com/admin/reports/mandrill
*Off: Setting Mandrill off routes all email through site's server.

### Email options
* Email from address - Select the email address you want your emails to be sent 
from.
* Verify New Email Address - To add a new Email Address to your Amazon SES 
account, add the email address here. Amazon will send the email address a 
confirmation message in which the user will need to click a link to confirm that 
you are authorized to use this email address.
* Input format - This selection allows you to select the optional input format 
to apply to the message body before sending to the STS API.

## Reports

Mandrill provides a set of integrated reports within Drupal at 
http://example.comadmin/reports/mandrill.

* Mandrill Quota reports on the number of emails sent in the last 24 hours and the
max number of emails your accounts supports per 24 hr period and per second.

* Mandrill Send Statistics is the heart of the transactional email reporting system.
When Drupal sends an email, it's assocaited with a unique mail key. This key is
passed to MailChimp as the email tag and the reports are broken down by tag.
Example tags/keys include password resets, user registrations, comment 
notifications, etc. For each tag per hour, the report returns emails sent, 
bounces, rejections, complaints, opens, and clicks.

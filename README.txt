Integration with Mandrill transactional emails, a service by the folks behind
MailChimp. Learn more about Mandrill and how to sign up at http://mandrill.com.

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
* From name
* Input format - This selection allows you to select the optional input format
to apply to the message body before sending to the STS API.

### Send options
* Track opens: Whether or not to turn on open tracking for messages.
* Track clicks: Whether or not to turn on click tracking for messages.
* Strip query string params: Whether or not to strip the query string from URLs when aggregating tracked URL data

### Google analytics
* Domains: One or more domains for which any matching URLs will automatically have Google Analytics parameters appended to their query string. Separate each domain with a comma.
* Campaign: The value to set for the utm_campaign tracking parameter. If this isn't provided the messages from address will be used instead.

## Reports are available in the mandrill_reports sub module.
* The dashboard charts volume and engagement using Google Charts, in addition
to providing a tabular list of URL interactions for the last 30 days.
* The account summary contains account information, quotas, and all time usage
stats.

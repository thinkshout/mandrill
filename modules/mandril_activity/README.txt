View Mandrill campaign activity for any entity with a valid email address.
Activity keys off of an email address and is cached after the initial load.
If a list has webhooks enabled, than the cache is cleared when a new campaign
is sent.

## Installation

1. Enable the Mandrill Activity module and the Entity Module
2. To use Mandrill Activity module, you will need to install and enable the Entity
API module [http://drupal.org/project/entity]([http://drupal.org/project/entity)

## Usage

1. Define which entity types you want to show campaign activity for at
/admin/config/services/Mandrill/activity.
  * Select a Drupal entity type.
  * Select a bundle.
  * Select the email entity property.
2. Configure permissions for viewing campaign activity.
3. Once setup, a new Mandrill activity local task will appear for any
configured entity.

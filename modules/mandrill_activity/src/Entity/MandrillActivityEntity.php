<?php

/**
 * @file
 * Contains Drupal\mandrill_activity\Entity\MandrillActivityEntity.
 */

namespace Drupal\mandrill_activity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\mandrill_activity\MandrillActivityEntityInterface;

/**
 * Defines the Mandrill activity entity entity.
 *
 * @ConfigEntityType(
 *   id = "mandrill_activity_entity",
 *   label = @Translation("Mandrill activity entity"),
 *   handlers = {
 *     "list_builder" = "Drupal\mandrill_activity\Controller\MandrillActivityEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\mandrill_activity\Form\MandrillActivityEntityForm",
 *       "edit" = "Drupal\mandrill_activity\Form\MandrillActivityEntityForm",
 *       "delete" = "Drupal\mandrill_activity\Form\MandrillActivityEntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "mandrill_activity_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/mandrill_activity_entity/{mandrill_activity_entity}",
 *     "edit-form" = "/admin/structure/mandrill_activity_entity/{mandrill_activity_entity}/edit",
 *     "delete-form" = "/admin/structure/mandrill_activity_entity/{mandrill_activity_entity}/delete"
 *   }
 * )
 */
class MandrillActivityEntity extends ConfigEntityBase implements MandrillActivityEntityInterface {
  /**
   * The Mandrill activity entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Mandrill activity entity label.
   *
   * @var string
   */
  protected $label;

}

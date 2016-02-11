<?php
/**
 * @file
 * Contains \Drupal\mandrill_activity\Form\MandrillActivityForm.
 */

namespace Drupal\mandrill_activity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the MandrillActivity entity edit form.
 *
 * @ingroup mandrill_activity
 */
class MandrillActivityForm extends EntityForm {

  /**
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $user_input = $form_state->getUserInput();

    /* @var $activity \Drupal\mandrill_activity\Entity\MandrillActivity */
    $activity = $this->entity;

    $entity_not_null = !empty($activity->mandrill_activity_entity_id);

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $activity->label,
      '#description' => t('The human-readable name of this activity entity.'),
      '#required' => TRUE,
    );

    $form['name'] = array(
      '#title' => t('Name'),
      '#type' => 'machine_name',
      '#default_value' => $activity->name,
      '#description' => t('machine name should only contain lowercase letters & underscores.'),
      '#disabled' => !empty($activity->name),
      '#required' => TRUE,
      '#machine_name' => array(
        'exists' => 'mandrill_activity_load',
        'source' => array('label'),
      ),
    );

    $form['drupal_entity'] = array(
      '#title' => t('Drupal entity'),
      '#type' => 'fieldset',
      '#attributes' => array(
        'id' => array('Mandrill-activity-drupal-entity'),
      ),
      '#prefix' => '<div id="entity-wrapper">',
      '#suffix' => '</div>',
    );

    // Prep the entity type list before creating the form item:
    $entity_info = \Drupal::entityTypeManager()->getDefinitions();

    $entity_types = array('' => t('-- Select --'));

    /* @var $entity_type \Drupal\Core\Entity\EntityType */
    foreach ($entity_info as $key => $entity_type) {
      // Ignore Mandrill entity types.
      if (strpos($entity_type->id(), 'mandrill') !== FALSE) {
        continue;
      }

      // Ignore configuration entities.
      if (get_class($entity_type) == 'Drupal\Core\Config\Entity\ConfigEntityType') {
        continue;
      }

      $entity_types[$entity_type->id()] = $entity_type->getLabel();
    }

    asort($entity_types);
    $form['drupal_entity']['entity_type'] = array(
      '#title' => t('Entity type'),
      '#type' => 'select',
      '#options' => $entity_types,
      '#default_value' => $activity->entity_type,
      '#required' => TRUE,
      '#description' => t('Select an entity type to enable Mandrill history on.'),
      '#ajax' => array(
        'callback' => '::entity_callback',
        'wrapper' => 'entity-wrapper',
        'method' => 'replace',
        'effect' => 'fade',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Retrieving bundles for this entity type.'),
        ),
      ),
    );

    $form_entity_type = $user_input['entity_type'];
    if (empty($form_entity_type) && $entity_not_null) {
      $form_entity_type = $activity->entity_type;
    }

    if (!empty($form_entity_type)) {
      // Prep the bundle list before creating the form item.
      $bundle_info = \Drupal::entityManager()->getBundleInfo($form_entity_type);

      $bundles = array('' => t('-- Select --'));

      foreach ($bundle_info as $key => $bundle) {
        $bundles[$key] = ucfirst($key);
      }

      asort($bundles);
      $form['drupal_entity']['bundle'] = array(
        '#title' => t('Entity bundle'),
        '#type' => 'select',
        '#required' => TRUE,
        '#description' => t('Select a Drupal entity bundle with an email address to report on.'),
        '#options' => $bundles,
        '#default_value' => $activity->bundle,
        '#ajax' => array(
          'callback' => 'mandrill_activity_mapping_form_callback',
          'wrapper' => 'entity-wrapper',
        ),
      );

      $form_bundle = $user_input['bundle'];
      if (empty($form_bundle) && $entity_not_null) {
        $form_bundle = $activity->bundle;
      }

      if (!empty($form_bundle)) {
        // Prep the field & properties list before creating the form item:
        //$fields = mandrill_activity_email_fieldmap_options($form_entity_type, $form_bundle);
        //$form['drupal_entity']['email_property'] = array(
          //'#title' => t('Email Property'),
          //'#type' => 'select',
          //'#required' => TRUE,
          //'#description' => t('Select the field which contains the email address'),
          //'#options' => $fields,
          //'#default_value' => $entitynotnull ? $mandrill_activity_entity->email_property : 0,
          //'#maxlength' => 127,
        //);
      }
    }

    $form['enabled'] = array(
      '#title' => t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => ($entity_not_null) ? $activity->enabled : TRUE,
    );

    return $form;
  }

  /**
   * AJAX callback handler for MandrillActivityForm.
   */
  public function entity_callback(&$form, FormStateInterface $form_state) {
    return $form['drupal_entity'];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /* @var $activity \Drupal\mandrill_activity\Entity\MandrillActivity */
    $activity = $this->getEntity();
    $activity->label = $form_state->getValue('label');

    $activity->save();

    \Drupal::service('router.builder')->setRebuildNeeded();

    $form_state->setRedirect('mandrill.admin');
  }

  public function exist($id) {
    $entity = $this->entityQuery->get('mandrill_activity')
      ->condition('mandrill_activity_entity_id', $id)
      ->execute();
    return (bool) $entity;
  }

}

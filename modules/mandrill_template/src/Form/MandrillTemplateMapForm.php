<?php
/**
 * @file
 * Contains \Drupal\mandrill_template\Form\MandrillTemplateMapForm.
 */

namespace Drupal\mandrill_template\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the MandrillTemplateMap entity edit form.
 *
 * @ingroup mandrill_template
 */
class MandrillTemplateMapForm extends EntityForm {

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

    /* @var $template_map \Drupal\mandrill_template\Entity\MandrillTemplateMap */
    $template_map = $this->entity;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $template_map->label,
      '#description' => t('The human-readable name of this Mandrill Template Map entity.'),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $template_map->id,
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => array(
        'source' => array('label'),
        'exists' => array($this, 'exists'),
      ),
      '#description' => t('A unique machine-readable name for this Mandrill Template Map entity. It must only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$template_map->isNew(),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /* @var $template_map \Drupal\mandrill_template\Entity\MandrillTemplateMap */
    $template_map = $this->getEntity();
    $template_map->save();

    \Drupal::service('router.builder')->setRebuildNeeded();

    $form_state->setRedirect('mandrill_template.admin');
  }

  public function exists($id) {
    $entity = $this->entityQuery->get('mandrill_template_map')
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}

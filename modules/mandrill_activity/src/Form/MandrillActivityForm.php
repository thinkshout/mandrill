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

    $activity = $this->entity;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#size' => 35,
      '#maxlength' => 32,
      '#default_value' => $activity->title,
      '#required' => TRUE,
    );

    return $form;
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

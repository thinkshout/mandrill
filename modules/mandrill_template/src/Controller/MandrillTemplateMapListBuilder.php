<?php
/**
 * @file
 * Contains \Drupal\mandrill_template\Controller\MandrillTemplateMapListBuilder.
 */

namespace Drupal\mandrill_template\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of MandrillTemplateMap entities.
 *
 * @ingroup mandrill_template
 */
class MandrillTemplateMapListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label() . ' (Machine name: ' . $entity->id() . ')';

    return $row + parent::buildRow($entity);
  }

}

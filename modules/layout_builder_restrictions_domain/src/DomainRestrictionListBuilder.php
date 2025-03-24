<?php

namespace Drupal\layout_builder_restrictions_domain;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DomainRestrictionListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id())
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage) {
    parent::__construct($entity_type, $storage);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'domain_restriction_list';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['domain'] = $this->t('Domain');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\layout_builder_restrictions_domain\DomainRestrictionInterface $entity */
    $row['domain'] = $entity->getDomain()->label();
    return $row + parent::buildRow($entity);
  }
}

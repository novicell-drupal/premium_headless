<?php

namespace Drupal\layout_builder_restrictions_domain\Form;

use Drupal\content_notify\ContentNotifyManager;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\domain\Entity\Domain;
use Drupal\entity_overview\EngineManager;
use Drupal\entity_overview\OverviewManager;
use Drupal\layout_builder_restrictions_domain\Entity\DomainRestriction;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add form for domain restrictions.
 */
class DomainRestrictionAddForm extends EntityForm {

  /**
   * @var \Drupal\layout_builder_restrictions_domain\DomainRestrictionInterface
   */
  protected $entity;

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;

    $domain_options = [];
    foreach (Domain::loadMultiple() as $key => $domain) {
      if (is_null(DomainRestriction::load($domain->id()))) {
        $domain_options[$domain->id()] = $domain->label();
      }
    }
    $form['id'] = [
      '#type' => 'select',
      '#title' => $this->t('Domain'),
      '#options' => $domain_options,
      '#default_value' => $this->entity->getDomainID() ?? '',
      '#required' => TRUE
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\layout_builder_restrictions_domain\DomainRestrictionInterface $entity */
    $entity = parent::buildEntity($form, $form_state);

    $entity->setDomainID($entity->id());

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirectUrl($this->entity->toUrl('edit-form'));
  }

}

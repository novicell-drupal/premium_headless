<?php
namespace Drupal\layout_builder_restrictions_domain\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\domain\DomainInterface;
use Drupal\domain\Entity\Domain;
use Drupal\layout_builder_restrictions_domain\DomainRestrictionInterface;

/**
 * Defines the domain restriction entity.
 *
 * @ConfigEntityType(
 *   id = "domain_restriction",
 *   label = @Translation("Domain restriction"),
 *   handlers = {
 *     "list_builder" = "Drupal\layout_builder_restrictions_domain\DomainRestrictionListBuilder",
 *     "form" = {
 *       "default" = "Drupal\layout_builder_restrictions_domain\Form\DomainRestrictionEditForm",
 *       "add" = "Drupal\layout_builder_restrictions_domain\Form\DomainRestrictionAddForm",
 *       "edit" = "Drupal\layout_builder_restrictions_domain\Form\DomainRestrictionEditForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "domain_restriction",
 *   admin_permission = "configure layout builder restrictions",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "domain_id",
 *     "allowlisted_blocks",
 *     "denylisted_blocks",
 *     "allowed_categories",
 *     "restricted_categories",
 *     "allowed_layouts"
 *   },
 *   lookup_keys = {
 *     "domain_id"
 *   },
 *   links = {
 *     "add-page" = "/admin/config/content/layout_builder_restrictions_domain/add",
 *     "collection" = "/admin/config/content/layout_builder_restrictions_domain",
 *     "edit-form" = "/admin/config/content/layout_builder_restrictions_domain/edit/{domain_restriction}",
 *     "delete-form" = "/admin/config/content/layout_builder_restrictions_domain/delete/{domain_restriction}"
 *   }
 * )
 */
class DomainRestriction extends ConfigEntityBase implements DomainRestrictionInterface {

  use StringTranslationTrait;

  /**
   * The domain restriction ID.
   *
   * @var string
   */
  protected $id;

  /**
   * Blocks that have been selected as allowed.
   *
   * @var array
   */
  protected $allowlisted_blocks = [];

  /**
   * Blocks that have been selected as denied.
   *
   * @var array
   */
  protected $denylisted_blocks = [];

  /**
   * Categories that have been selected as allowed.
   *
   * @var array
   */
  protected $allowed_categories = [];

  /**
   * Categories that have been selected as denied.
   *
   * @var array
   */
  protected $restricted_categories = [];

  /**
   * Layouts that have been selected as allowed.
   *
   * @var array
   */
  protected $allowed_layouts = [];

  /**
   * The engine used to drive this overview.
   *
   * @var string
   */
  protected $domain_id = '';

  /**
   * The engine used to drive this overview.
   *
   * @var DomainInterface
   */
  protected $domain = NULL;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getDomain()->label();
  }

  /**
   * {@inheritdoc}
   */
  public function getDomainID(): string {
    return $this->domain_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setDomainID($domain_id) {
    $this->domain_id = $domain_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getDomain(): DomainInterface {
    if (empty($this->domain)) {
      $this->domain = Domain::load($this->domain_id);
    }
    return $this->domain;
  }

  public function setAllowlistedBlocks($allowlisted_blocks) {
    $this->allowlisted_blocks = $allowlisted_blocks;
  }

  public function getAllowlistedBlocks(): array {
    return $this->allowlisted_blocks;
  }

  public function setDenylistedBlocks($denylisted_blocks) {
    $this->denylisted_blocks = $denylisted_blocks;
  }

  public function getDenylistedBlocks(): array {
    return $this->denylisted_blocks;
  }

  public function setRestrictedCategories($restricted_categories) {
    $this->restricted_categories = $restricted_categories;
  }

  public function getRestrictedCategories(): array {
    return $this->restricted_categories;
  }

  public function setAllowedCategories($allowed_categories) {
    $this->allowed_categories = $allowed_categories;
  }

  public function getAllowedCategories(): array {
    return $this->allowed_categories;
  }

  public function setAllowedLayouts($allowed_layouts) {
    $this->allowed_layouts = $allowed_layouts;
  }

  public function getAllowedLayouts(): array {
    return $this->allowed_layouts;
  }

}

<?php
namespace Drupal\layout_builder_restrictions_domain;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\domain\DomainInterface;

interface DomainRestrictionInterface extends ConfigEntityInterface {

  /**
   * @param array $allowlisted_blocks
   *
   * @return $this
   */
  public function setAllowlistedBlocks($allowlisted_blocks);

  /**
   * @return array
   */
  public function getAllowlistedBlocks(): array;

  /**
   * @param array $denylisted_blocks
   *
   * @return $this
   */
  public function setDenylistedBlocks($denylisted_blocks);

  /**
   * @return array
   */
  public function getDenylistedBlocks(): array;

  /**
   * @param array $restricted_categories
   *
   * @return $this
   */
  public function setRestrictedCategories($restricted_categories);

  /**
   * @return array
   */
  public function getRestrictedCategories(): array;

  /**
   * @param array $allowed_categories
   *
   * @return $this
   */
  public function setAllowedCategories($allowed_categories);

  /**
   * @return array
   */
  public function getAllowedCategories(): array;

  /**
   * @param array $allowed_layouts
   *
   * @return $this
   */
  public function setAllowedLayouts($allowed_layouts);

  /**
   * @return array
   */
  public function getAllowedLayouts(): array;

  /**
   * @param string $domain_id
   *
   * @return $this
   */
  public function setDomainID($domain_id);

  /**
   * @return string
   */
  public function getDomainID(): string;

    /**
   * @return \Drupal\domain\DomainInterface
   */
  public function getDomain(): DomainInterface;

}

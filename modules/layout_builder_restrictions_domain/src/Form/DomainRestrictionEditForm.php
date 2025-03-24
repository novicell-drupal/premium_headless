<?php

namespace Drupal\layout_builder_restrictions_domain\Form;

use Drupal\content_notify\ContentNotifyManager;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\domain\DomainInterface;
use Drupal\entity_overview\EngineManager;
use Drupal\entity_overview\OverviewManager;
use Drupal\layout_builder\Entity\LayoutEntityDisplayInterface;
use Drupal\layout_builder_restrictions\Traits\PluginHelperTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Edit form for overviews.
 */
class DomainRestrictionEditForm extends EntityForm {

  use PluginHelperTrait;

  /**
   * @var \Drupal\layout_builder_restrictions_domain\DomainRestrictionInterface
   */
  protected $entity;

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;

    $allowed_block_categories = (is_array($this->entity->getAllowedCategories())) ? $this->entity->getAllowedCategories() : [];
    $form['layout']['layout_builder_restrictions']['allowed_block_categories'] = [
      '#title' => $this->t('Default restriction for new categories of blocks not listed below.'),
      '#description_display' => 'before',
      '#type' => 'radios',
      '#options' => [
        "allowed" => $this->t('Allow all blocks from newly available categories.'),
        "restricted" => $this->t('Restrict all blocks from newly available categories.'),
      ],
      '#parents' => [
        'layout_builder_restrictions',
        'allowed_block_categories',
      ],
      '#default_value' => !empty($allowed_block_categories) ? "restricted" : "allowed",
    ];

    // Block settings.
    $form['layout']['layout_builder_restrictions']['allowed_blocks'] = [
      '#type' => 'details',
      '#title' => $this->t('Blocks available for placement (all layouts & regions)'),
      '#states' => [
        'disabled' => [
          ':input[name="layout[enabled]"]' => ['checked' => FALSE],
        ],
        'invisible' => [
          ':input[name="layout[enabled]"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $allowlisted_blocks = (is_array($this->entity->getAllowlistedBlocks())) ? $this->entity->getAllowlistedBlocks() : [];
    $denylisted_blocks = (is_array($this->entity->getDenylistedBlocks())) ? $this->entity->getDenylistedBlocks() : [];
    $restricted_categories = (is_array($this->entity->getRestrictedCategories())) ? $this->entity->getRestrictedCategories() : [];

    foreach ($this->getBlockDefinitionsByDomain($this->entity->getDomain()) as $category => $data) {
      $category = $this->getUntranslatedCategory($category);
      $title = $data['label'];
      if (!empty($data['translated_label']) && $category != 'Custom blocks') {
        $title = $data['translated_label'];
      }
      $category_form = [
        '#type' => 'fieldset',
        '#title' => $title,
        '#parents' => ['layout_builder_restrictions', 'allowed_blocks'],
      ];
      // Check whether this is a newly available category that has been
      // restricted previously.
      $category_is_restricted = (!empty($allowed_block_categories) && !in_array($category, $allowed_block_categories));
      // The category is 'restricted' if it's already been specified as such,
      // or if the default behavior for new categories indicate such.
      if (in_array($category, array_keys($allowlisted_blocks))) {
        $category_setting = 'allowlisted';
      }
      elseif (in_array($category, array_keys($denylisted_blocks))) {
        $category_setting = 'denylisted';
      }
      elseif ($category_is_restricted) {
        $category_setting = 'restrict_all';
      }
      elseif (in_array($category, $restricted_categories)) {
        $category_setting = 'restrict_all';
      }
      else {
        $category_setting = 'all';
      }
      $category_form['restriction_behavior'] = [
        '#type' => 'radios',
        '#options' => [
          "all" => $this->t('Allow all existing & new %category blocks.', ['%category' => $data['label']]),
          "restrict_all" => $this->t('Restrict all existing & new %category blocks.', ['%category' => $data['label']]),
          "allowlisted" => $this->t('Allow specific %category blocks:', ['%category' => $data['label']]),
          "denylisted" => $this->t('Restrict specific %category blocks:', ['%category' => $data['label']]),
        ],
        '#default_value' => $category_setting,
        '#parents' => [
          'layout_builder_restrictions',
          'allowed_blocks',
          $category,
          'restriction',
        ],
      ];
      $category_form['available_blocks'] = [
        '#type' => 'container',
        '#states' => [
          'invisible' => [
            [':input[name="layout_builder_restrictions[allowed_blocks][' . $category . '][restriction]"]' => ['value' => "all"]],
            [':input[name="layout_builder_restrictions[allowed_blocks][' . $category . '][restriction]"]' => ['value' => "restrict_all"]],
          ],
        ],
      ];
      foreach ($data['definitions'] as $block_id => $block) {
        $enabled = FALSE;
        if ($category_setting == 'allowlisted' && isset($allowlisted_blocks[$category]) && in_array($block_id, $allowlisted_blocks[$category])) {
          $enabled = TRUE;
        }
        elseif ($category_setting == 'denylisted' && isset($denylisted_blocks[$category]) && in_array($block_id, $denylisted_blocks[$category])) {
          $enabled = TRUE;
        }
        $category_form['available_blocks'][$block_id] = [
          '#type' => 'checkbox',
          '#title' => $block['admin_label'],
          '#default_value' => $enabled,
          '#parents' => [
            'layout_builder_restrictions',
            'allowed_blocks',
            $category,
            'available_blocks',
            $block_id,
          ],
        ];
      }
      if ($category == 'Custom blocks' || $category == 'Custom block types') {
        $category_form['description'] = [
          '#type' => 'container',
          '#children' => $this->t('<p>In the event both <em>Custom Block Types</em> and <em>Content Blocks</em> restrictions are enabled, <em>Custom Block Types</em> restrictions are disregarded.</p>'),
          '#states' => [
            'visible' => [
              ':input[name="layout_builder_restrictions[allowed_blocks][' . $category . '][restriction]"]' => ['value' => "restricted"],
            ],
          ],
        ];
      }
      $form['layout']['layout_builder_restrictions']['allowed_blocks'][$category] = $category_form;
    }
    // Layout settings.
    $allowed_layouts = (is_array($this->entity->getAllowedLayouts())) ? $this->entity->getAllowedLayouts() : [];
    $layout_form = [
      '#type' => 'details',
      '#title' => $this->t('Layouts available for sections'),
      '#parents' => ['layout_builder_restrictions', 'allowed_layouts'],
      '#states' => [
        'disabled' => [
          ':input[name="layout[enabled]"]' => ['checked' => FALSE],
        ],
        'invisible' => [
          ':input[name="layout[enabled]"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $layout_form['layout_restriction'] = [
      '#type' => 'radios',
      '#options' => [
        "all" => $this->t('Allow all existing & new layouts.'),
        "restricted" => $this->t('Allow only specific layouts:'),
      ],
      '#default_value' => !empty($allowed_layouts) ? "restricted" : "all",
    ];
    $definitions = $this->getLayoutDefinitions();
    foreach ($definitions as $plugin_id => $definition) {
      $enabled = FALSE;
      if (!empty($allowed_layouts) && in_array($plugin_id, $allowed_layouts)) {
        $enabled = TRUE;
      }
      $layout_form['layouts'][$plugin_id] = [
        '#type' => 'checkbox',
        '#default_value' => $enabled,
        '#description' => [
          $definition->getIcon(60, 80, 1, 3),
          [
            '#type' => 'container',
            '#children' => $definition->getLabel() . ' (' . $plugin_id . ')',
          ],
        ],
        '#states' => [
          'invisible' => [
            ':input[name="layout_builder_restrictions[allowed_layouts][layout_restriction]"]' => ['value' => "all"],
          ],
        ],
      ];
    }
    $form['layout']['layout_builder_restrictions']['allowed_layouts'] = $layout_form;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\layout_builder_restrictions_domain\DomainRestrictionInterface $entity */
    $entity = parent::buildEntity($form, $form_state);
    $block_restrictions = $this->setAllowedBlocks($form_state);
    $entity->setAllowlistedBlocks($block_restrictions['allowlisted'] ?? []);
    $entity->setDenylistedBlocks($block_restrictions['denylisted'] ?? []);
    $entity->setRestrictedCategories($block_restrictions['restricted_categories'] ?? []);
    $entity->setAllowedLayouts($this->setAllowedLayouts($form_state));
    $entity->setAllowedCategories($this->setAllowedBlockCategories($form_state, $entity->getDomain()));
    // Save!
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    $this->messenger()->addMessage($this->t('Overview %label saved.', [
      '%label' => $this->entity->label(),
    ]));
  }

  /**
   * Gets block definitions appropriate for a context.
   *
   * @param \Drupal\domain\DomainInterface $domain
   *
   * @return array[]
   *   Keys are category names, and values are arrays of which the keys are
   *   plugin IDs and the values are plugin definitions.
   */
  protected function getBlockDefinitionsByDomain(DomainInterface $domain): array {
    $contexts = ['domain' => $domain];
    $section_storage = $this->sectionStorageManager()->load('defaults', $contexts);
    // Do not use the plugin filterer here, but still filter by contexts.
    $definitions = $this->blockManager()->getDefinitions();

    // Create a list of block_content IDs for later filtering.
    $custom_blocks = [];
    foreach ($definitions as $key => $definition) {
      if ($definition['provider'] == 'block_content') {
        $custom_blocks[] = $key;
      }
    }

    // Allow filtering of available blocks by other parts of the system.
    //$definitions = $this->contextHandler()->filterPluginDefinitionsByContexts($this->getPopulatedContexts($section_storage), $definitions);
    $grouped_definitions = $this->getDefinitionsByUntranslatedCategory($definitions);
    // Create a new category of block_content blocks that meet the context.
    foreach ($grouped_definitions as $category => $data) {
      if (empty($data['definitions'])) {
        unset($grouped_definitions[$category]);
      }
      // Ensure all block_content definitions are included in the
      // 'Custom blocks' category.
      foreach ($data['definitions'] as $key => $definition) {
        if (in_array($key, $custom_blocks)) {
          if (!isset($grouped_definitions['Custom blocks'])) {
            $grouped_definitions['Custom blocks'] = [
              'label' => 'Custom blocks',
              'data' => [],
            ];
          }
          // Remove this block_content from its previous category so
          // that it is defined only in one place.
          unset($grouped_definitions[$category]['definitions'][$key]);
          $grouped_definitions['Custom blocks']['definitions'][$key] = $definition;
        }
      }
    }

    // Generate a list of custom block types under the
    // 'Custom block types' namespace.
    $custom_block_bundles = $this->entityTypeBundleInfo()->getBundleInfo('block_content');
    if ($custom_block_bundles) {
      $grouped_definitions['Custom block types'] = [
        'label' => 'Custom block types',
        'definitions' => [],
      ];
      foreach ($custom_block_bundles as $machine_name => $value) {
        $grouped_definitions['Custom block types']['definitions'][$machine_name] = [
          'admin_label' => $value['label'],
          'category' => $this->t('Custom block types'),
        ];
      }
    }
    ksort($grouped_definitions);

    return $grouped_definitions;
  }

  /**
   * Helper function to prepare saved allowed blocks.
   *
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   An array of layout names or empty.
   */
  protected function setAllowedBlocks(FormStateInterface $form_state) {
    $categories = $form_state->getValue([
      'layout_builder_restrictions',
      'allowed_blocks',
    ]);
    $block_restrictions = [];
    $block_restrictions['restricted_categories'] = [];
    if (!empty($categories)) {
      foreach ($categories as $category => $settings) {
        $restriction_type = $settings['restriction'];
        if (in_array($restriction_type, ['allowlisted', 'denylisted'])) {
          $block_restrictions[$restriction_type][$category] = [];
          foreach ($settings['available_blocks'] as $block_id => $block_setting) {
            if ($block_setting == '1') {
              // Include only checked blocks.
              $block_restrictions[$restriction_type][$category][] = $block_id;
            }
          }
        }
        elseif ($restriction_type === "restrict_all") {
          $block_restrictions['restricted_categories'][] = $category;
        }
      }
    }
    return $block_restrictions;
  }

  /**
   * Helper function to prepare saved allowed layouts.
   *
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   An array of layout names or empty.
   */
  protected function setAllowedLayouts(FormStateInterface $form_state) {
    // Set allowed layouts.
    $layout_restriction = $form_state->getValue([
      'layout_builder_restrictions',
      'allowed_layouts',
      'layout_restriction',
    ]);
    $allowed_layouts = [];
    if ($layout_restriction == 'restricted') {
      $allowed_layouts = array_keys(array_filter($form_state->getValue([
        'layout_builder_restrictions',
        'allowed_layouts',
        'layouts',
      ])));
    }
    return $allowed_layouts;
  }

  /**
   * Helper function to prepare saved block definition categories.
   *
   * @return array
   *   An array of block category names or empty.
   */
  protected function setAllowedBlockCategories(FormStateInterface $form_state, DomainInterface $domain) {
    // Set default for allowed block categories.
    $block_category_default = $form_state->getValue([
      'layout_builder_restrictions',
      'allowed_block_categories',
    ]);
    if ($block_category_default == 'restricted') {
      // Create a allowlist of categories whose blocks should be allowed.
      // Newly available categories' blocks not in this list will be
      // disallowed.
      $allowed_block_categories = array_keys($this->getBlockDefinitionsByDomain($domain));
    }
    else {
      // The UI choice indicates that all newly available categories'
      // blocks should be allowed by default. Represent this in the schema
      // as an empty array.
      $allowed_block_categories = [];
    }
    return $allowed_block_categories;
  }
}

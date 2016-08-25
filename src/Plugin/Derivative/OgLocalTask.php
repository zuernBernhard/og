<?php

namespace Drupal\og\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProvider;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for all entity bundles.
 */
class OgLocalTask extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Route provider object.
   *
   * @var RouteProvider
   */
  protected $routProvider;

  /**
   * Creates an OgLocalTask object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   * @param RouteProvider $route_provider
   *   The route provider services.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, TranslationInterface $string_translation, RouteProvider $route_provider) {
    $this->entityManager = $entity_manager;
    $this->stringTranslation = $string_translation;
    $this->routProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('string_translation'),
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $derivatives = [];

    // @todo: Use GroupTypeManager.
    foreach ($this->entityManager->getDefinitions() as $entity_type_id => $entity_type) {
      $route_name = "entity.$entity_type_id.og_admin_routes";

      if (!$this->routProvider->getRoutesByNames([$route_name])) {
        // Route not found.
        continue;
      }

      $derivatives[$entity_type_id . '.og_admin_routes'] = [
        'route_name' => $route_name,
        'title' => $this->t('Group'),
        'base_route' => 'entity.' . $entity_type_id . '.canonical',
        'weight' => 50,
      ];
    }

    // @todo: Remove hardcoding
    $entity_type_id = 'node';
    $id = 'members';
    $base_route_name = "entity.$entity_type_id.og_admin_routes";

    $derivatives["$base_route_name.$id"] = [
      'title' => $this->t('Members'),
      'route_name' => "$base_route_name.$id",
      'appears_on' => [$base_route_name],
      'class' => '\Drupal\Core\Menu\LocalActionDefault',
    ];

    foreach ($derivatives as &$entry) {
      $entry += $base_plugin_definition;
    }

    return $derivatives;
  }

}

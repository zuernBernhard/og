<?php

namespace Drupal\og\Controller;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\og\Event\OgAdminRoutesEvent;
use Drupal\og\Event\OgAdminRoutesEventInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The OG admin routes controller.
 */
class OgAdminRoutesController extends ControllerBase {

  /**
   * The access manager service.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * Constructs an OgAdminController object.
   *
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $event_dispatcher
   *   The event dispatcher service.
   * @param \Drupal\Core\Access\AccessManagerInterface $access_manager
   *   The access manager service.
   */
  public function __construct(AccessManagerInterface $access_manager) {
    $this->accessManager = $access_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('access_manager')
    );
  }

  /**
   * Show all the available admin routes.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   *
   * @return array
   *   List of available admin routes for the current group.
   */
  public function overview(RouteMatchInterface $route_match) {
    /** @var \Drupal\Core\Entity\EntityInterface $group */
    $group = $route_match->getParameter('group');

    // Get list from routes.
    $content = [];

    $route_name = "og_admin.members";
    $parameters = ['entity_type_id' => $group->getEntityTypeId(), 'group' => $group->id()];

    // We don't use Url::fromRoute() here for the access check, as it will
    // prevent us from unit testing this method.
    if (!$this->accessManager->checkNamedRoute($route_name, $parameters)) {
      // User doesn't have access to the route.
      return ['#markup' => $this->t('You do not have any administrative items.')];
    }

    $content[] = [
      'title' => 'Members',
      'description' => 'Manage members',
      'url' => Url::fromRoute($route_name, $parameters),
    ];

    return [
      'og_admin_routes' => [
        '#theme' => 'admin_block_content',
        '#content' => $content,
      ],
    ];
  }

}

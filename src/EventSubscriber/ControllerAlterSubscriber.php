<?php
namespace Drupal\premium_headless\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ControllerAlterSubscriber.
 */
class ControllerAlterSubscriber implements EventSubscriberInterface {

  /**
   * Alters the controller output.
   */
  public function onView(ViewEvent $event) {
    $request = $event->getRequest();
    $route = $request->attributes->get('_route');

    if ($route == 'layout_builder.choose_block') {
      $build = $event->getControllerResult();
      if (is_array($build)) {
        // alter controller build array
        $build['add_block']['#attributes']['class'][] = 'button';

        $event->setControllerResult($build);
      }
    } elseif ($route == 'layout_builder.choose_inline_block') {
      $build = $event->getControllerResult();
      if (is_array($build)) {
        // alter controller build array
        $build['back_button']['#attributes']['class'][] = 'button';

        $event->setControllerResult($build);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    // priority > 0 so that it runs before the controller output
    // is rendered by \Drupal\Core\EventSubscriber\MainContentViewSubscriber
    $events[KernelEvents::VIEW][] = ['onView', 50];
    return $events;
  }

}

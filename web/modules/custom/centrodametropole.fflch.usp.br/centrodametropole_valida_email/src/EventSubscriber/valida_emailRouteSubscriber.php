<?php

/**
 * @file
 * Contains \Drupal\valida_email\EventSubscribe\valida_emailRouteSubscriber.
 */

  namespace Drupal\valida_email\EventSubscriber;

  use Drupal\Core\Routing\RouteSubscriberBase;
  use Symfony\Component\Routing\RouteCollection;


  /**
   * Listens to the dynamic route events.
   */
  class valida_emailRouteSubscriber extends RouteSubscriberBase {

      /**
       * {@inheritdoc}
       */
      protected function alterRoutes(RouteCollection $collection) {
//          if ($route = $collection->get('user.login')) {
//              $route->setDefault('_form', '\Drupal\form_overwrite\Form\NewUserLoginForm');
//          }
      }
  }
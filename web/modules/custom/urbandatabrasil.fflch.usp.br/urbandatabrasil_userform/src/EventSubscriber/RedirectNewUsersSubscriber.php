<?php

namespace Drupal\urbandatabrasil_userform\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Path\CurrentPathStack;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectNewUsersSubscriber.
 */
class RedirectNewUsersSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Path\CurrentPathStack definition.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $pathCurrent;

  /**
   * Constructs a new RedirectNewUsersSubscriber object.
   */
  public function __construct(CurrentPathStack $path_current) {
    $this->pathCurrent = $path_current;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['kernel.request'] = ['newUserRedirection'];
    return $events;
  }

  /**
   * This method is called when the kernel.request is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function newUserRedirection(Event $event) {

    $current_path = $this->pathCurrent->getPath();
    $session = \Drupal::request()->getSession()->get('usuario_validado');

    if ($current_path != '/node/add') {
      return;
    }

    if (!empty($session)) {
      return;
    }

    \Drupal::messenger()->addMessage('Bem vindo ao site do Urbandata.', 'status', TRUE);
    $response = new RedirectResponse("/cadastre-se", 301);
    $event->setResponse($response);

  }

}

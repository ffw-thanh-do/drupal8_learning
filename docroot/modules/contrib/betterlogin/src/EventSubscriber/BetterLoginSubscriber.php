<?php

namespace Drupal\betterlogin\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Url;

/**
 * Better Login Subscriber class.
 */
class BetterLoginSubscriber implements EventSubscriberInterface {

  /**
   * Function checkForRedirection.
   *
   * Redirection for anonymous users.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   GetResponseEvent event.
   */
  public function checkForRedirection(GetResponseEvent $event) {
    if (\Drupal::currentUser()->isAnonymous()) {
      // Anonymous user.
      if ($event->getRequest()->query->get('user')) {
        $loginUrl = Url::fromRoute('user.login', ['destination' => 'user'])->toString();
        $event->setResponse(new RedirectResponse($loginUrl));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkForRedirection'];
    return $events;
  }

}

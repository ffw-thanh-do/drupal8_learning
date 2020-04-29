<?php

namespace Drupal\drupal_8_core\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ThanhSubscriber.
 */
class ThanhSubscriber implements EventSubscriberInterface {
  public function  onKernelRequest($event) {
    var_dump($event);die;
  }

  public static function getSubscribedEvents()
  {
    return [
      KernelEvents::REQUEST => 'onKernelRequest',
    ];
  }

}

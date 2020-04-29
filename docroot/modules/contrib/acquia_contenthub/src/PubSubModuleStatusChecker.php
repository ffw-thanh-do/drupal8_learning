<?php

namespace Drupal\acquia_contenthub;

use Drupal\Core\Extension\ModuleHandler;

/**
 * Utility class; encapsulates static general-purpose methods.
 *
 * @package Drupal\acquia_contenthub
 */
class PubSubModuleStatusChecker {

  public const ACQUIA_CONTENTHUB_PUBLISHER_MODULE_ID = 'acquia_contenthub_publisher';

  public const ACQUIA_CONTENTHUB_SUBSCRIBER_MODULE_ID = 'acquia_contenthub_subscriber';

  /**
   * @var ModuleHandler
   */
  protected $module_handler_service;

  public function __construct(ModuleHandler $module_handler_service) {
    $this->module_handler_service = $module_handler_service;
  }

  /**
   * Determines if publisher module is installed and enabled.
   *
   * @return bool
   */
  public function isPublisher(): bool {
    return $this->moduleEnabled(self::ACQUIA_CONTENTHUB_PUBLISHER_MODULE_ID);
  }

  /**
   * Determines if subscriber module is installed and enabled.
   *
   * @return bool
   */
  public function isSubscriber(): bool {
    return $this->moduleEnabled(self::ACQUIA_CONTENTHUB_SUBSCRIBER_MODULE_ID);
  }

  /**
   * Checks if site has dual configuration (pub/sub).
   *
   * @return bool
   */
  public function siteHasDualConfiguration(): bool {
    return $this->isSubscriber() && $this->isPublisher();
  }

  /**
   * Determines if a module is installed and enabled.
   *
   * @param string $module_id
   *
   * @return bool
   */
  private function moduleEnabled(string $module_id): bool {
    return $this->module_handler_service->moduleExists($module_id);
  }
}

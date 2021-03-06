<?php

namespace Drupal\acquia_contenthub_subscriber\Plugin\QueueWorker;

use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Client\ClientFactory;
use Drupal\acquia_contenthub\ContentHubCommonActions;
use Drupal\acquia_contenthub\Event\TrackTotalsEvent;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ContentHubImportQueueWorker.
 *
 * @QueueWorker(
 *   id = "acquia_contenthub_subscriber_import",
 *   title = "Queue Worker to import entities from contenthub."
 * )
 */
class ContentHubImportQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * The common actions object.
   *
   * @var \Drupal\acquia_contenthub\ContentHubCommonActions
   */
  protected $common;

  /**
   * The client factory.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientFactory
   */
  protected $factory;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * ContentHubExportQueueWorker constructor.
   *
   * @param \Drupal\acquia_contenthub\ContentHubCommonActions $common
   *   The common actions object.
   * @param \Drupal\acquia_contenthub\Client\ClientFactory $factory
   *   The client factory.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger_channel
   *   The logger factory.
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   */
  public function __construct(EventDispatcherInterface $dispatcher, ContentHubCommonActions $common, ClientFactory $factory, LoggerChannelInterface $logger_channel, array $configuration, $plugin_id, $plugin_definition) {

    $this->common = $common;
    if (!empty($this->common->getUpdateDbStatus())) {
      throw new \Exception("Site has pending database updates. Apply these updates before importing content.");
    }
    $this->dispatcher = $dispatcher;
    $this->factory = $factory;
    $this->loggerChannel = $logger_channel;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('event_dispatcher'),
      $container->get('acquia_contenthub_common_actions'),
      $container->get('acquia_contenthub.client.factory'),
      $container->get('acquia_contenthub.logger_channel'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * Processes acquia_contenthub_subscriber_import queue items.
   *
   * @param mixed $data
   *   The data in the queue.
   *
   * @throws \Exception
   */
  public function processItem($data) {
    $settings = $this->factory->getSettings();
    $webhook = $settings->getWebhook('uuid');
    $contentHubClient = $this->factory->getClient();

    $interests = $contentHubClient->getInterestsByWebhook($webhook);
    $processItems = explode(', ', $data->uuids);

    // Get rid of items potentially deleted from the interest list.
    $uuids = array_intersect($processItems, $interests);
    if (count($uuids) !== count($processItems)) {
      // Log the uuids no longer on the interest list for this webhook.
      $missing_uuids = array_diff($processItems, $uuids);
      $this
        ->loggerChannel
        ->info(sprintf('Skipped importing the following missing entities: %s. This occurs when entities are deleted at the Publisher before importing.', implode(', ', $missing_uuids)));
    }

    if (!$uuids) {
      return;
    }

    try {
      $stack = $this->common->importEntities(...$uuids);
      $this->factory->getClient();
    }
    catch (\Exception $e) {
      $this
        ->loggerChannel
        ->error(sprintf('Import failed: %s.', $e->getMessage()));
      throw $e;
    }

    if ($webhook) {
      try {
        $contentHubClient->addEntitiesToInterestList($webhook, array_keys($stack->getDependencies()));

        $this
          ->loggerChannel
          ->info('Imported entities added to Interest List on Plexus');
      }
      catch (\Exception $e) {
        $this
          ->loggerChannel
          ->error(sprintf('Message: %s.', $e->getMessage()));
      }
    }
  }

}

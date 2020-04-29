<?php

namespace Drupal\acquia_contenthub_subscriber\EventSubscriber\PrunePublishEntities;

use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\PrunePublishCdfEntitiesEvent;
use Drupal\acquia_contenthub\PubSubModuleStatusChecker;
use Drupal\acquia_contenthub_subscriber\SubscriberTracker;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PruneImportedEntitiesFromExport implements EventSubscriberInterface {

  /**
   * Keep track of the tracker table.
   *
   * @var \Drupal\acquia_contenthub_subscriber\SubscriberTracker
   */
  protected $tracker;

  /**
   * @var \Drupal\acquia_contenthub\PubSubModuleStatusChecker
   */
  private $checker;

  /**
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  private $entity_repository;

  /**
   * TrackTotals constructor.
   *
   * @param \Drupal\acquia_contenthub_subscriber\SubscriberTracker $tracker
   * @param \Drupal\acquia_contenthub\PubSubModuleStatusChecker $checker
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   */
  public function __construct(SubscriberTracker $tracker, PubSubModuleStatusChecker $checker, EntityRepositoryInterface $entity_repository) {
    $this->tracker = $tracker;
    $this->checker = $checker;
    $this->entity_repository = $entity_repository;
  }

  /**
   * Retrieve subscribed events.
   *
   * @return array
   */
  public static function getSubscribedEvents(): array {
    return [
      AcquiaContentHubEvents::PRUNE_PUBLISH_CDF_ENTITIES => 'onPrunePublishCdfEntitiesIfSiteHasDualConfiguration'
    ];
  }

  /**
   * onPrunePublishCdfEntities event handler.
   *
   * @param \Drupal\acquia_contenthub\Event\PrunePublishCdfEntitiesEvent $event
   *
   * @throws \ReflectionException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function onPrunePublishCdfEntitiesIfSiteHasDualConfiguration(PrunePublishCdfEntitiesEvent $event): void {
    if (!$this->checker->siteHasDualConfiguration()) {
      return;
    }

    /** @var \Acquia\ContentHubClient\CDFDocument $document */
    $document = $event->getDocument();
    $doc_uuids = array_keys($document->getEntities());
    $untracked_uuids = $this->tracker->getUntracked($doc_uuids);
    $tracked_uuids = array_diff($doc_uuids, $untracked_uuids);

    //By removing all tracked uuids from the document, we are left with untracked uuids
    foreach ($tracked_uuids as $remove_uuid) {
      $document->removeCdfEntity($remove_uuid);
    }

    // Fetch all entities from Plexus from the list of untracked uuids
    $remote_entities = $event->getClient()->getEntities($untracked_uuids);
    $site_origin = $event->getOrigin();

    foreach ($remote_entities->getEntities() as $entity) {
      $uuid = $entity->getUuid();
      // It's eligible to be exported iff owned by self
      if ($entity->getOrigin() === $site_origin) {
        continue;
      }

      // Otherwise, it should be removed from the document to prevent it from being exported
      $cdf = $document->getCdfEntity($uuid);
      $document->removeCdfEntity($uuid);

      // Also it should be added to the tracking table so that it never ends up in the export queue again
      $local_entity = $this->entity_repository->loadEntityByUuid($cdf->getAttribute('entity_type')->getValue()['und'], $uuid);

      if ($local_entity) {
        $this->tracker->track($local_entity, $entity->getAttribute('hash')->getValue()['und']);
      }

    }
  }

}

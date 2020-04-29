<?php

namespace Drupal\acquia_contenthub\Event;

use Acquia\ContentHubClient\CDFDocument;
use Symfony\Component\EventDispatcher\Event;
use Acquia\ContentHubClient\ContentHubClient;

/**
 * Class TrackTotalsEvent
 *
 * @package Drupal\acquia_contenthub\Event
 */
class PrunePublishCdfEntitiesEvent extends Event {

  /**
   * Keep track of the CH client.
   *
   * @var \Acquia\ContentHubClient\ContentHubClient
   */
  protected $client;

  /**
   * @var \Acquia\ContentHubClient\CDFDocument
   */
  private $document;

  /**
   * @var string
   */
  private $origin;

  /**
   * TrackTotalsEvent constructor.
   *
   * @param \Acquia\ContentHubClient\ContentHubClient $client
   * @param \Acquia\ContentHubClient\CDFDocument $document
   * @param string $origin
   */
  public function __construct(ContentHubClient $client, CDFDocument $document, string $origin) {
    $this->client = $client;
    $this->document = $document;
    $this->origin = $origin;
  }

  /**
   * @return string
   */
  public function getOrigin(): string {
    return $this->origin;
  }

  /**
   * @return \Acquia\ContentHubClient\CDFDocument
   */
  public function getDocument(): CDFDocument {
    return $this->document;
  }

  /**
   * Exposes the client.
   *
   * @return \Acquia\ContentHubClient\ContentHubClient
   */
  public function getClient(): ContentHubClient {
    return $this->client;
  }

}

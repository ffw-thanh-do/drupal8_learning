<?php

namespace Drupal\acquia_contenthub\Event;

use Symfony\Component\EventDispatcher\Event;
use Acquia\ContentHubClient\ContentHubClient;

/**
 * Class TrackTotalsEvent
 *
 * @package Drupal\acquia_contenthub\Event
 */
class TrackTotalsEvent extends Event {

  /**
   * Keep track of the CH client.
   *
   * @var \Acquia\ContentHubClient\ContentHubClient
   */
  protected $client;

  /**
   * TrackTotalsEvent constructor.
   *
   * @param \Acquia\ContentHubClient\ContentHubClient $client
   */
  public function __construct(ContentHubClient $client) {
    $this->client = $client;
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

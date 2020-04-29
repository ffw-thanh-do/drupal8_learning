<?php

namespace Drupal\drupal_8_element\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining People entities.
 *
 * @ingroup drupal_8_element
 */
interface peopleInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the People name.
   *
   * @return string
   *   Name of the People.
   */
  public function getName();

  /**
   * Sets the People name.
   *
   * @param string $name
   *   The People name.
   *
   * @return \Drupal\drupal_8_element\Entity\peopleInterface
   *   The called People entity.
   */
  public function setName($name);

  /**
   * Gets the People creation timestamp.
   *
   * @return int
   *   Creation timestamp of the People.
   */
  public function getCreatedTime();

  /**
   * Sets the People creation timestamp.
   *
   * @param int $timestamp
   *   The People creation timestamp.
   *
   * @return \Drupal\drupal_8_element\Entity\peopleInterface
   *   The called People entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the People published status indicator.
   *
   * Unpublished People are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the People is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a People.
   *
   * @param bool $published
   *   TRUE to set this People to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\drupal_8_element\Entity\peopleInterface
   *   The called People entity.
   */
  public function setPublished($published);

  /**
   * Gets the People revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the People revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\drupal_8_element\Entity\peopleInterface
   *   The called People entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the People revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the People revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\drupal_8_element\Entity\peopleInterface
   *   The called People entity.
   */
  public function setRevisionUserId($uid);

}

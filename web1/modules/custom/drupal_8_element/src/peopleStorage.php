<?php

namespace Drupal\drupal_8_element;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\drupal_8_element\Entity\peopleInterface;

/**
 * Defines the storage handler class for People entities.
 *
 * This extends the base storage class, adding required special handling for
 * People entities.
 *
 * @ingroup drupal_8_element
 */
class peopleStorage extends SqlContentEntityStorage implements peopleStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(peopleInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {people_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {people_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(peopleInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {people_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('people_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}

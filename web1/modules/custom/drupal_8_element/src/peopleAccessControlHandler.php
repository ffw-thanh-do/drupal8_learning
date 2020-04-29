<?php

namespace Drupal\drupal_8_element;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the People entity.
 *
 * @see \Drupal\drupal_8_element\Entity\people.
 */
class peopleAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\drupal_8_element\Entity\peopleInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished people entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published people entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit people entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete people entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add people entities');
  }

}

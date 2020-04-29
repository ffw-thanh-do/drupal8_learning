<?php

namespace Drupal\acquia_contenthub\Event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\depcalc\DependencyStack;
use Symfony\Component\EventDispatcher\Event;

class PreEntitySaveEvent extends Event {

  /**
   * The entity we're about to save.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The dependency stack.
   *
   * @var \Drupal\depcalc\DependencyStack
   */
  protected $stack;

  /**
   * PreEntitySaveEvent constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity about to be saved.
   * @param \Drupal\depcalc\DependencyStack $stack
   *   The dependency stack.
   */
  public function __construct(EntityInterface $entity, DependencyStack $stack) {
    $this->entity = $entity;
    $this->stack = $stack;
  }

  /**
   * Get the entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function getEntity() : EntityInterface {
    return $this->entity;
  }

  /**
   * Get the dependency stack.
   *
   * @return \Drupal\depcalc\DependencyStack
   */
  public function getStack() : DependencyStack {
    return $this->stack;
  }

}

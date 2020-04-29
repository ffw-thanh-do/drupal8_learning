<?php

namespace Drupal\drupal_8_element\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for People entities.
 */
class peopleViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}

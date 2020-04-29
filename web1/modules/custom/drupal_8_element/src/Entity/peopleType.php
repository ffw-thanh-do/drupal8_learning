<?php

namespace Drupal\drupal_8_element\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the People type entity.
 *
 * @ConfigEntityType(
 *   id = "people_type",
 *   label = @Translation("People type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\drupal_8_element\peopleTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\drupal_8_element\Form\peopleTypeForm",
 *       "edit" = "Drupal\drupal_8_element\Form\peopleTypeForm",
 *       "delete" = "Drupal\drupal_8_element\Form\peopleTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\drupal_8_element\peopleTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "people_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "people",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/people_type/{people_type}",
 *     "add-form" = "/admin/structure/people_type/add",
 *     "edit-form" = "/admin/structure/people_type/{people_type}/edit",
 *     "delete-form" = "/admin/structure/people_type/{people_type}/delete",
 *     "collection" = "/admin/structure/people_type"
 *   }
 * )
 */
class peopleType extends ConfigEntityBundleBase implements peopleTypeInterface {

  /**
   * The People type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The People type label.
   *
   * @var string
   */
  protected $label;

}

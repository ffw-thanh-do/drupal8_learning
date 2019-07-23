<?php

namespace Drupal\drupal_8_wysiwyg\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "horizontalred" plugin.
 *
 * @CKEditorPlugin(
 *   id = "horizontalred",
 *   label = @Translation("CKEditor Horizontal red"),
 * )
 */
class Horizontalred extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    $path = drupal_get_path('module', 'drupal_8_wysiwyg') . '/js/plugins/horizontalred/plugin.js';

    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $path = drupal_get_path('module', 'drupal_8_wysiwyg') . '/js/plugins/horizontalred';

    return [
      'horizontalred' => [
        'label' => $this->t('Add horizontal red'),
        'image' => $path . '/icons/horizontalred.png',
      ],
    ];
  }

}

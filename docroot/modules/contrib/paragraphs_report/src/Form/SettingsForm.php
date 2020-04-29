<?php

namespace Drupal\paragraphs_report\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Paragraphs Report settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Configuration settings.
   */
  const SETTINGS = 'paragraphs_report.settings';

  /**
   * Configuration interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entityTypeManager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paragraphs_report_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * Build the form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   *   Form to render.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    // Get list of content types to report on.
    $contentTypes = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $contentTypesList = [];
    foreach ($contentTypes as $contentType) {
      $contentTypesList[$contentType->id()] = $contentType->label();
    }
    $form['content_types'] = [
      '#title' => $this->t('Content Types'),
      '#type' => 'checkboxes',
      '#options' => $contentTypesList,
      '#default_value' => $config->get('content_types') ?? [],
    ];

    $form['import_rows_per_batch'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of nodes per batch'),
      '#size' => 5,
      '#description'   => $this->t('Lower this value if you have a high number of paragraphs per node.'),
      '#default_value' => $config->get('import_rows_per_batch') ?? 10,
    ];

    $form['watch_content'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Watch for content changes and update report data'),
      '#description'   => $this->t('If enabled, any node save/delete will update Paragraphs Report data.'),
      '#default_value' => $config->get('watch_content') ?? FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('paragraphs_report.settings')
      ->set('content_types', $form_state->getValue('content_types'))
      ->set('import_rows_per_batch', $form_state->getValue('import_rows_per_batch'))
      ->set('watch_content', $form_state->getValue('watch_content'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}

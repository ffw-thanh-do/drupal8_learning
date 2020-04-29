<?php

namespace Drupal\paragraphs_report;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Xss;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\AliasManager;

/**
 * Class ParagraphsReport.
 *
 * Control various module logic like batches, lookups, and report output.
 *
 * @package Drupal\paragraphs_report
 */
class ParagraphsReport {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * @var \Drupal\Core\Path\AliasManager
   */
  protected $aliasManager;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $paraSettings;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $paraEditSettings;

  /**
   * Constructs the controller object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\Core\Entity\EntityFieldManager $entityFieldManager
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   * @param \Drupal\Core\Path\AliasManager $aliasManager
   */
  public function __construct(ConfigFactoryInterface $configFactory, EntityFieldManager $entityFieldManager, EntityTypeManager $entityTypeManager, CurrentPathStack $currentPath, AliasManager $aliasManager) {
    $this->configFactory = $configFactory;
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->currentPath = $currentPath;
    $this->aliasManager = $aliasManager;

    $this->paraSettings = $this->configFactory->get('paragraphs_report.settings');
    $this->paraEditSettings = $this->configFactory->getEditable('paragraphs_report.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager'),
      $container->get('path.current'),
      $container->get('path.alias_manager')
    );
  }

  /**
   * Batch API process starting point.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
   */
  public function batchSetup() {
    // Get all nodes to process.
    $nids = $this->getNodes();
    // Put nodes into batches.
    $batch = $this->batchPrep($nids);
    // Start batch api process.
    batch_set($batch);
    // Redirect page and display message on completion.
    return batch_process('/admin/reports/paragraphs-report');
  }

  /**
   * Setup batch array var.
   *
   * @param array $nids
   *   Node ids.
   *
   * @return array
   *   Batches ready to run
   */
  public function batchPrep($nids = []) {
    $totalRows = count($nids);
    $rowsPerBatch = $this->paraSettings->get('import_rows_per_batch') ?: 10;
    $batchesPerImport = ceil($totalRows / $rowsPerBatch);
    // Put x-amount of rows into operations array slots.
    $operations = [];
    for ($i = 0; $i < $batchesPerImport; $i++) {
      $offset = ($i == 0) ? 0 : $rowsPerBatch * $i;
      $batchNids = array_slice($nids, $offset, $rowsPerBatch);
      $operations[] = ['batchGetParaFields', [$batchNids]];
    }
    // Full batch array.
    $batch = [
      'init_message' => $this->t('Executing a batch...'),
      'progress_message' => $this->t('Operation @current out of @total batches, @perBatch per batch.',
        ['@perBatch' => $rowsPerBatch]
      ),
      'progressive' => TRUE,
      'error_message' => $this->t('Batch failed.'),
      'operations' => $operations,
      'finished' => 'batchSave',
      'file' => drupal_get_path('module', 'paragraphs_report') . '/paragraphs_report.batch.inc',
    ];
    return $batch;
  }

  /**
   * Get paragraph fields from a bundle/type.
   *
   * @param string $bundle
   *   Entity bundle like 'node'.
   *
   * @param string $type
   *   Entity type like 'page'.
   *
   * @return array
   */
  public function getParaFieldsOnType($bundle = '', $type = '') {
    $paraFields = [];
    $fields = $this->entityFieldManager->getFieldDefinitions($bundle, $type);
    foreach ($fields as $field_name => $field_definition) {
      if (!empty($field_definition->getTargetBundle()) && $field_definition->getSetting('target_type') == 'paragraph') {
        $paraFields[] = $field_name;
      }
    }
    return $paraFields;
  }

  /**
   * Get paragraph fields for selected content types.
   *
   * @return array
   *   Paragraph fields by content type key
   */
  public function getParaFieldDefinitions() {
    // Loop through the fields for chosen content types to get paragraph fields.
    // Example content_type[] = field_name.
    $paraFields = [];
    foreach ($this->getTypes() as $contentType) {
      $fields = $this->entityFieldManager->getFieldDefinitions('node', $contentType);
      foreach ($fields as $field_name => $field_definition) {
        if (!empty($field_definition->getTargetBundle()) && $field_definition->getSetting('target_type') == 'paragraph') {
          $paraFields[$contentType][] = $field_name;
        }
      }
    }
    return $paraFields;
  }

  /**
   * Get list of content types chosen from settings.
   *
   * @return array
   */
  public function getTypes() {
    $data = $this->paraSettings->get('content_types') ?? [];
    $types = array_filter($data);
    return $types;
  }

  /**
   * Query db for nodes to check for paragraphs.
   *
   * @return array
   *   Nids to check for para fields.
   *
   * @throws
   */
  public function getNodes() {
    $contentTypes = array_filter($this->paraSettings->get('content_types'));
    $query = $this->entityTypeManager->getStorage('node');
    $nids = $query->getQuery()
      ->condition('type', $contentTypes, 'IN')
      ->execute();
    return $nids;
  }

  /**
   * Pass node id, return paragraphs report data as array.
   *
   * @param $nid string
   *   Node id.
   * @param $current array
   *   Paragraphs to append new ones to.
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getParasFromNid($nid = '', $current = []) {
    // Pass any current array items into array.
    $arr = $current;
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    // Get and loop through first level paragraph fields on the node.
    $paraFields = $this->getParaFieldsOnType('node', $node->bundle());
    foreach ($paraFields as $paraField) {
      // Get paragraph values (target_ids).
      $paras = $node->get($paraField)->getValue();
      foreach ($paras as $para) {
        // Load paragraph from target_id.
        $p = Paragraph::load($para['target_id']);
        // Add paragraph to report array
        $arr[$p->bundle()]['top'][] = $nid;
        // Check if the top level paragraph has sub-paragraph fields.
        $arr = $this->getParaSubFields($node, $p, $arr);
      }
    }
    return $arr;
  }

  /**
   * Helper recursive method to find embedded paragraphs.
   *
   * Send a paragraph, check fields for sub-paragraph fields recursively.
   *
   * @param $node
   * @param $paragraph
   * @param $reports array
   *
   * @return array
   *   Paragraph values.
   */
  public function getParaSubFields($node, $paragraph, $reports) {
    // Get fields on paragraph and check field type.
    $fields = $this->entityFieldManager->getFieldDefinitions('paragraph', $paragraph->bundle());
    foreach ($fields as $field_name => $field_definition) {
      // Check if this field a paragraph type.
      if (!empty($field_definition->getTargetBundle()) && $field_definition->getSetting('target_type') == 'paragraph') {
        // Get paragraphs on this field.
        $paras = $paragraph->get($field_name)->getValue();
        foreach ($paras as $para) {
          $p = Paragraph::load($para['target_id']);
          // If yes, add this field to report and check for more sub-fields.
          // Example arr[main component][parent] = alias of node.
          $reports[$p->bundle()][$paragraph->bundle()][] = $node->id();
          $reports = $this->getParaSubFields($node, $p, $reports);
        }
      }
    }
    return $reports;
  }

  /**
   * Get a list of the paragraph components and return as lookup array.
   *
   * @return array
   *   Machine name => label.
   */
  public function getParaTypes() {
    $paras = paragraphs_type_get_types();
    $names = [];
    foreach ($paras as $machine => $obj) {
      $names[$machine] = $obj->label();
    }
    return $names;
  }

  /**
   * Pass array of path data to save for the report.
   *
   * @param array $arr
   *   Paragraph->parent->path data.
   */
  public function configSaveReport($arr = []) {
    $json = Json::encode($arr);
    $this->paraEditSettings->set('report', $json)->save();
  }

  /**
   * Remove a node path from report data.
   *
   * @param string $removeNid
   *   Remove from report data.
   *
   * @return array updated encoded data.
   */
  public function configRemoveNode($removeNid = '') {
    $json = Json::decode($this->paraSettings->get('report'));
    // Force type to be array.
    $json = is_array($json) ? $json : [];
    // Search for nid and remove from array.
    // Remove item from array.
    $new = [];
    foreach ($json as $para => $sets) {
      foreach ($sets as $parent => $nids) {
        // Remove nid from array.
        $tmp = [];
        foreach ($nids as $nid) {
          if ($nid != $removeNid) {
            $tmp[] = $nid;
          }
        }
        $new[$para][$parent] = $tmp;
      }
    }
    // Save updated array.
    $this->configSaveReport($new);
    return $new;
  }

  /**
   * Check that node meets conditions to use in report data.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return bool
   *
   * @throws
   */
  public function checkWatch(EntityInterface $entity) {
    if ($this->paraSettings->get('watch_content')
      && in_array($entity->bundle(), $this->getTypes())) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Add report data from new node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Node to check.
   *
   * @throws
   */
  public function insertParagraphs(EntityInterface $entity) {
    if ($this->checkWatch($entity)) {
      // Send node to get parsed for paragraph fields/sub-fields.
      $json = Json::decode($this->paraSettings->get('report'));
      $updated = $this->getParasFromNid($entity->id(), $json);
      $this->configSaveReport($updated);
    }
  }

  /**
   * Update report data with paragraph changes in node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Node to check.
   *
   * @throws
   */
  public function updateParagraphs(EntityInterface $entity) {
    if ($this->checkWatch($entity)) {
      // Send node to get parsed for paragraph fields/sub-fields.
      $json = $this->configRemoveNode($entity->id());
      $updated = $this->getParasFromNid($entity->id(), $json);
      $this->configSaveReport($updated);
    }
  }

  /**
   * Remove deleted node path from report data.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Node to check.
   *
   * @throws
   */
  public function deleteParagraphs(EntityInterface $entity) {
    if ($this->checkWatch($entity)) {
      $json = $this->configRemoveNode($entity->id());
      $this->configSaveReport($json);
    }
  }

  /**
   * Build quick paragraphs type drop down form.
   *
   * @return string
   *   HTML used for select form element.
   */
  public function filterForm() {
    // Build filter form.
    // Check and set filters.
    $paraNames = $this->getParaTypes();
    $current_path = $this->currentPath->getPath();
    $filterForm = '<form method="get" action="' . $current_path . '">';
    $filterForm .= 'Filter by Type: <select name="ptype">';
    $filterForm .= '<option value="">All</option>';
    foreach ($paraNames as $machine => $label) {
      $selected = isset($_GET['ptype']) && $_GET['ptype'] == $machine ? ' selected' : '';
      $filterForm .= '<option name="' . $machine . '" value="' . $machine . '"' . $selected . '>' . $label . '</option>';
    }
    $filterForm .= '</select> <input type="submit" value="Go"></form><br>';
    return $filterForm;
  }

  /**
   * Format the stored JSON config var into a rendered table.
   *
   * @param array $json
   *   Stored paragraph report data.
   *
   * @return array
   *   Table render array.
   */
  public function formatTable($json = []) {
    $paraNames = $this->getParaTypes();
    $filter = isset($_GET['ptype']) ? trim($_GET['ptype']) : '';
    // Get paragraphs label info, translate machine name to label.
    // Loop results into the table.
    $total = 0;
    $rows = [];
    if (!empty($json)) {
      foreach ($json as $name => $set) {
        // Skip if we are filtering out all but one.
        if (!empty($filter) && $filter != $name) {
          continue;
        }
        // Be mindful of the parent field.
        foreach ($set as $parent => $nids) {
          // Turn duplicates into counts.
          if (!empty($nids)) {
            $counts = array_count_values($nids);
            foreach ($counts as $nid => $count) {
              $alias = $this->aliasManager->getAliasByPath('/node/' . $nid);
              $link = $this->t('<a href="@alias">@alias</a>', ['@alias' => $alias]);
              $label = $paraNames[$name];
              $rows[] = [$label, $parent, $link, $count];
              $total++;
            }
          }
        }
      }
    }
    $header = [
      $this->t('Paragraph'),
      $this->t('Parent'),
      $this->t('Path'),
      $this->t('Count'),
    ];
    // Setup pager.
    $per_page = 10;
    $current_page = pager_default_initialize($total, $per_page);
    // Split array into page sized chunks, if not empty.
    $chunks = !empty($rows) ? array_chunk($rows, $per_page, TRUE) : 0;
    // Table output.
    $table['table'] = [
      '#type' => 'table',
      '#title' => $this->t('Paragraphs Report'),
      '#header' => $header,
      '#sticky' => TRUE,
      '#rows' => $chunks[$current_page],
      '#empty' => $this->t('No components found. You may need to run the report.'),
    ];
    $table['pager'] = [
      '#type' => 'pager',
    ];
    return $table;
  }

  /**
   * Return a rendered table ready for output.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Table markup returned.
   */
  public function showReport() {
    // Build report from stored JSON in module config.
    $btn['run_button'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<div style="float:right"><a class="button" href="/admin/reports/paragraphs-report/update" onclick="return confirm(\'Update the report data with current node info?\')">Update Report Data</a></div>'),
    ];
    $json = Json::decode($this->paraSettings->get('report'));
    // Force type to be array.
    $json = is_array($json) ? $json : [];
    $filters = [];
    $filters['filter'] = [
      '#type' => 'markup',
      '#markup' => $this->filterForm(),
      '#allowed_tags' => array_merge(Xss::getHtmlTagList(),
        ['form', 'option', 'select', 'input', 'br']),
    ];
    $table = $this->formatTable($json);
    return [
      $btn,
      $filters,
      $table,
    ];
  }

}

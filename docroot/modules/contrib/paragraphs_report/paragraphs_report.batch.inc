<?php

/**
 * @file
 * Batch API Methods.
 */

/**
 * Batch method to load node and check for para field content.
 *
 * @param array $nids
 *   Node ids to process per batch run.
 * @param array $context
 *   Batch param.
 *
 * @throws
 */
function batchGetParaFields($nids, &$context) {
  $paraReport = \Drupal::service('paragraphs_report.report');
  // Initiate multistep processing and any custom batch vars.
  if (empty($context['sandbox'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['max'] = count($nids);
  }
  // Counter var.
  if (empty($context['results']['count'])) {
    $context['results']['count'] = 0;
  }
  if (!empty($nids)) {
    foreach ($nids as $nid) {
      $current = isset($context['results']['paras']) ? $context['results']['paras'] : [];
      $context['results']['paras'] = $paraReport->getParasFromNid($nid, $current);
      $context['results']['count']++;
      $context['sandbox']['progress']++;
    }
    // Multistep processing : report progress.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }
}

/**
 * After batch process has completed, save results to db.
 *
 * @param $success
 * @param $results
 * @param $operations
 */
function batchSave($success, $results, $operations) {
  // JSON encode and save to module config.
  $paraReport = \Drupal::service('paragraphs_report.report');
  $paraReport->configSaveReport($results['paras']);
  if ($success) {
    $message = \Drupal::translation()
      ->formatPlural($results['count'], 'One node processed.', '@count nodes processed.');
    \Drupal::logger('Paragraphs Report')->notice($message);
  }
  else {
    $message = t('Finished with an error.');
    \Drupal::logger('Paragraphs Report')->error($message);
  }
  \Drupal::logger('Paragraphs Report')->info($message);
}

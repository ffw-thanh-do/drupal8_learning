<?php

namespace Drupal\Tests\acquia_contenthub\Kernel;

use Acquia\ContentHubClient\Settings;

/**
 * Class NodeImportChannelSpecificContentTest.
 *
 * @group acquia_contenthub
 * @group orca_ignore
 *
 * @requires module metatag
 *
 * @package Drupal\Tests\acquia_contenthub\Kernel
 */
class NodeImportChannelSpecificContentTest extends ImportExportTestBase {

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $fixtures = [
    // Test case for node import from publisher with channel related language.
    [
      'cdf' => 'node/node_channel_specific.json',
      'expectations' => 'expectations/node/node_channel_specific.php',
    ],
    // Test cas for node import from publisher with channels. The import fixture
    // contains not related channel translations as well.
    [
      'cdf' => 'node/node_publisher_with_channels_not_related.json',
      'expectations' => 'expectations/node/node_publisher_with_channels_not_related.php',
    ],
    // Test cases for node import and update. Import node with default channel
    // and update with channel specific translation.
    [
      'cdf' => 'node/node_publisher_with_channels.json',
      'expectations' => 'expectations/node/node_publisher_with_channels.php',
    ],
    [
      'cdf' => 'node/node_publisher_with_channels_update.json',
      'expectations' => 'expectations/node/node_publisher_with_channels_update.php',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'node',
    'field',
    'depcalc',
    'acquia_contenthub',
    'acquia_contenthub_subscriber',
    'taxonomy',
    'image',
    'metatag',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('user', ['users_data']);
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installSchema('acquia_contenthub_subscriber', 'acquia_contenthub_subscriber_import_tracking');

    $settings = $this->prophesize(Settings::class);
    $settings->getName()->willReturn('Subscriber');

    $this->container->set('acquia_contenthub.settings', $settings->reveal());
  }

  /**
   * Tests Channel specific Node create.
   *
   * @param int $delta
   *   Fixture delta.
   * @param array $validate_data
   *   Data.
   * @param string $export_type
   *   Exported entity type.
   * @param string $export_uuid
   *   Entity UUID.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @dataProvider nodeChannelSpecificImportDataProvider
   */
  public function testNodeChannelSpecificImport($delta, array $validate_data, $export_type, $export_uuid) {
    $this->contentEntityImport($delta, $validate_data, $export_type, $export_uuid);
  }

  /**
   * Tests Channel specific Node create.
   *
   * @param int $delta
   *   Fixture delta.
   * @param int $update_delta
   *   Fixture delta.
   * @param array $validate_data
   *   Data.
   * @param string $export_type
   *   Exported entity type.
   * @param string $export_uuid
   *   Entity UUID.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @dataProvider nodeChannelSpecificUpdateImportDataProvider
   */
  public function testNodeChannelSpecificUpdate($delta, $update_delta, array $validate_data, $export_type, $export_uuid) {
    $this->contentEntityImport($delta, $validate_data, $export_type, $export_uuid);

    // @todo Need fix.
    // Need to run the prophesize again before the second contenEntityImport or
    // the getName method will return NULL, no matter what.
    $settings = $this->prophesize(Settings::class);
    $settings->getName()->willReturn('Subscriber');
    $this->container->set('acquia_contenthub.settings', $settings->reveal());

    $this->contentEntityImport($update_delta, $validate_data, $export_type, $export_uuid);
  }

  /**
   * Import content.
   *
   * @param int $delta
   *   Fixture delta.
   * @param array $validate_data
   *   Data.
   * @param string $export_type
   *   Exported entity type.
   * @param string $export_uuid
   *   Entity UUID.
   * @param bool $compare_exports
   *   Runs extended fixture/export comparison. FALSE for mismatched uuids.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function contentEntityImport(int $delta, array $validate_data, $export_type, $export_uuid, $compare_exports = TRUE) {
    $expectations = $this->importFixture($delta);
    /** @var \Drupal\Core\Entity\EntityRepositoryInterface $repository */
    $repository = $this->container->get('entity.repository');
    foreach ($validate_data as $datum) {
      $entity_type = $datum['type'];
      $validate_uuid = $datum['uuid'];
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $entity = $repository->loadEntityByUuid($entity_type, $validate_uuid);
      if (!isset($expectations[$validate_uuid])) {
        throw new \Exception(sprintf("You are missing validation for the entity of type %s of uuid %s.",
          $entity_type, $validate_uuid));
      }
      /** @var \Drupal\Tests\acquia_contenthub\Kernel\Stubs\CdfExpectations $expectation */
      $expectation = $expectations[$validate_uuid];
      foreach ($entity->getTranslationLanguages() as $language) {
        $trans = $entity->getTranslation($language->getId());
        /** @var \Drupal\Core\Field\FieldItemListInterface $field */
        foreach ($trans as $field_name => $field) {
          if ($expectation->isExcludedField($field_name)) {
            continue;
          }

          $actual_value = $this->handleFieldValues($field);
          $expected_value = $expectation->getFieldValue($field_name, $language->getId());
          $message = 'File: ' . $this->fixtures[$delta]['expectations'];
          $message .= "\nEntity: " . $trans->uuid();
          $message .= "\nField name: " . $field_name;
          $message .= "\nExpected:\n" . print_r($expected_value,
              TRUE) . "\nActual:\n" . print_r($actual_value, TRUE);

          $expected_value = $this->cleanLineEndings($expected_value);
          $actual_value = $this->cleanLineEndings($actual_value);
          $this->assertEquals($expected_value, $actual_value, $message);
        }
      }
    }
  }

  /**
   * Data provider for testNodeChannelSpecificImport.
   *
   * @return array
   *   Data provider for testNodeChannelSpecificImport.
   */
  public function nodeChannelSpecificImportDataProvider() {
    return [
      [
        0,
        [['type' => 'node', 'uuid' => '0b3afb48-6f0c-45e5-a3a5-baefd36d65db']],
        'node',
        '0b3afb48-6f0c-45e5-a3a5-baefd36d65db',
      ],
      [
        1,
        [['type' => 'node', 'uuid' => '5c67b87c-6ded-4724-94f4-6f5098b87409']],
        'node',
        '5c67b87c-6ded-4724-94f4-6f5098b87409',
      ],
    ];
  }

  /**
   * Data provider for testNodeChannelSpecificUpdate.
   *
   * @return array
   *   Data provider for testNodeChannelSpecificUpdate.
   */
  public function nodeChannelSpecificUpdateImportDataProvider() {
    return [
      [
        2,
        3,
        [['type' => 'node', 'uuid' => 'aa40053e-d9fe-420b-a439-a9a550e90568']],
        'node',
        'aa40053e-d9fe-420b-a439-a9a550e90568',
      ],
    ];
  }

}

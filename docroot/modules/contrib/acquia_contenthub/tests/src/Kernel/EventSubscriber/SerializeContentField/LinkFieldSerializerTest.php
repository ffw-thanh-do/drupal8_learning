<?php

namespace Drupal\Tests\acquia_contenthub\Kernel\EventSubscriber\SerializeContentField;

use Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent;
use Drupal\acquia_contenthub\EventSubscriber\SerializeContentField\LinkFieldSerializer;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\link\LinkItemInterface;
use Drupal\Tests\acquia_contenthub\Kernel\Stubs\DrupalVersion;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Class LinkFieldSerializerTest.
 *
 * @group acquia_contenthub
 * @coversDefaultClass \Drupal\acquia_contenthub\EventSubscriber\SerializeContentField\LinkFieldSerializer
 *
 * @requires module depcalc
 *
 * @package Drupal\Tests\acquia_contenthub\Kernel\EventSubscriber\SerializeContentField
 */
class LinkFieldSerializerTest extends EntityKernelTestBase {

  use DrupalVersion;
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'acquia_contenthub',
    'depcalc',
    'filter',
    'language',
    'link',
    'node',
    'text',
  ];

  /**
   * The LinkFieldSerializer object.
   *
   * @var \Drupal\acquia_contenthub\EventSubscriber\SerializeContentField\LinkFieldSerializer
   */
  protected $serializer;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    if (version_compare(\Drupal::VERSION, '8.8.0', '>=')) {
      $this->installEntitySchema('path_alias');
    }
    $this->installEntitySchema('node_type');
    $this->installConfig(['node', 'filter']);
    $this->installEntitySchema('field_config');
    $this->installSchema('node', 'node_access');
    $this->installSchema('user', 'users_data');

    $this->createContentType([
      'type' => 'page',
      'name' => 'Page',
    ])->save();

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'link',
      'entity_type' => 'node',
      'type' => 'link',
      'cardinality' => -1,
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'page',
      'label' => 'link',
      'settings' => ['link_type' => LinkItemInterface::LINK_GENERIC],
    ]);
    $field->save();

    $this->serializer = new LinkFieldSerializer();
  }

  /**
   * Test cases when empty data should be returned.
   *
   * @param string $field
   *   The field name.
   * @param string $message
   *   The message to display in case of failure.
   *
   * @covers ::onSerializeContentField
   * @dataProvider emptyDataCases
   *
   * @throws \Exception
   */
  public function testOnSerializeContentFieldEmptyData(string $field, string $message): void {
    $node = $this->createNode();
    $node_cdf = $this->container->get('acquia_contenthub_common_actions')
      ->getLocalCdfDocument($node)
      ->getCdfEntity($node->uuid());

    $event = new SerializeCdfEntityFieldEvent($node, $field, $node->get($field), $node_cdf);
    $this->serializer->onSerializeContentField($event);
    $data = $event->getFieldData();
    $this->assertEmpty($data, $message);
  }

  /**
   * Provides cases when empty field data should be returned.
   *
   * @return array
   *   The test cases.
   */
  public function emptyDataCases(): array {
    return [
      ['link', 'No link has been added to the node.'],
      ['body', 'The field is not link type, data is empty.'],
    ];
  }

  /**
   * @covers ::onSerializeContentField
   */
  public function testOnSerializeContentFieldMultiLang(): void {
    ConfigurableLanguage::create([
      'id' => 'hu',
      'label' => 'HU',
    ])->save();
    ConfigurableLanguage::create([
      'id' => 'es',
      'label' => 'ES',
    ])->save();

    // Create main node with translations.
    $node = $this->createNode();
    $node->addTranslation('hu', [
      'title' => 'Title - HU',
      'body' => 'Body - HU',
    ])->save();
    $node->addTranslation('es', [
      'title' => 'Title - ES',
      'body' => 'Body - ES',
    ])->save();

    // Provide a couple of referable nodes with translations.
    $referred_node = $this->createNode();
    $referred_node->addTranslation('hu', [
      'title' => 'Title - HU',
      'body' => 'Body - HU',
    ])->save();
    $translated_node = $referred_node->getTranslation('hu');

    $referred_node_2 = $this->createNode();
    $referred_node_2->addTranslation('es', [
      'title' => 'Title - ES',
      'body' => 'Body - ES',
    ])->save();
    $translated_node_2 = $referred_node_2->getTranslation('es');

    // Add referable nodes to the main node with their translation and
    // original language.
    $trans_node_hu = $node->getTranslation('hu');
    $trans_node_hu->get('link')->appendItem([
      'uri' => "entity:node/{$translated_node->id()}",
      'title' => $translated_node->label(),
    ]);
    $trans_node_es = $node->getTranslation('es');
    $trans_node_es->get('link')->appendItem([
      'uri' => "entity:node/{$translated_node_2->id()}",
      'title' => $translated_node_2->label(),
    ]);

    $node->get('link')->appendItem([
      'uri' => "entity:node/{$referred_node->id()}",
      'title' => $referred_node->label(),
    ]);
    $node->get('link')->appendItem([
      'uri' => "entity:node/{$referred_node_2->id()}",
      'title' => $referred_node_2->label(),
    ]);
    $node->save();

    $node_cdf = $this->container->get('acquia_contenthub_common_actions')
      ->getLocalCdfDocument($node)
      ->getCdfEntity($node->uuid());

    $event = new SerializeCdfEntityFieldEvent($node, 'link', $node->get('link'), $node_cdf);
    $this->serializer->onSerializeContentField($event);
    $data = $event->getFieldData()['value'];
    // All translation must be present in the field data.
    $this->assertArraySubset(['en', 'es', 'hu'], array_keys($data));
    $this->assertCount(2, $data['en']);
    $this->assertCount(1, $data['es']);
    $this->assertCount(1, $data['hu']);
    $expected = [
      ['uri' => $referred_node->uuid()],
      ['uri' => $referred_node_2->uuid()],
    ];
    $this->assertArraySubset($expected, $data['en']);

    $referred_node_2->delete();
    $event = new SerializeCdfEntityFieldEvent($node, 'link', $node->get('link'), $node_cdf);
    $this->serializer->onSerializeContentField($event);
    $data = $event->getFieldData()['value'];
    $this->assertArraySubset(['en', 'hu'], array_keys($data));
    $this->assertCount(1, $data['hu']);
    $this->assertCount(1, $data['en']);
    $expected = [
      ['uri' => $referred_node->uuid()],
    ];
    $this->assertArraySubset($expected, $data['en']);
  }

}

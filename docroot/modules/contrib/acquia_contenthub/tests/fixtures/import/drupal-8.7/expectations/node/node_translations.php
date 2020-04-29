<?php

/**
 * @file
 * Expectation for node translations scenario.
 */

use Drupal\Tests\acquia_contenthub\Kernel\Stubs\CdfExpectations;

$data = [
  'uuid' => [
    'en' => [
      0 => [
        'value' => 'b0137bab-a80e-4305-84fe-4d99ffd906c5',
      ],
    ],
    'be' => [
      0 => [
        'value' => 'b0137bab-a80e-4305-84fe-4d99ffd906c5',
      ],
    ],
  ],
  'langcode' => [
    'en' => [
      0 => [
        'value' => 'en',
      ],
    ],
    'be' => [
      0 => [
        'value' => 'be',
      ],
    ],
  ],
  'type' => [
    'en' => [
      0 => [
        'target_id' => 'e226ade7-fe77-4b5b-b534-7ca14302fbc0',
      ],
    ],
    'be' => [
      0 => [
        'target_id' => 'e226ade7-fe77-4b5b-b534-7ca14302fbc0',
      ],
    ],
  ],
  'revision_timestamp' => [
    'en' => [
      0 => [
        'value' => '1579287921',
      ],
    ],
    'be' => [
      0 => [
        'value' => '1579287921',
      ],
    ],
  ],
  'revision_uid' => [
    'en' => [
      0 => [
        'target_id' => '3bfd0410-7d82-4078-b5b6-6d9ce77d6e00',
      ],
    ],
    'be' => [
      0 => [
        'target_id' => '3bfd0410-7d82-4078-b5b6-6d9ce77d6e00',
      ],
    ],
  ],
  'revision_log' => [
    'en' => [],
    'be' => [],
  ],
  'status' => [
    'en' => [
      0 => [
        'value' => '1',
      ],
    ],
    'be' => [
      0 => [
        'value' => '1',
      ],
    ],
  ],
  'title' => [
    'en' => [
      0 => [
        'value' => 'Test page node',
      ],
    ],
    'be' => [
      0 => [
        'value' => 'Тэставая старонка',
      ],
    ],
  ],
  'uid' => [
    'en' => [
      0 => [
        'target_id' => '4eda3a4b-4f2a-4c51-bbb8-dc46b5566412',
      ],
    ],
    'be' => [
      0 => [
        'target_id' => '4eda3a4b-4f2a-4c51-bbb8-dc46b5566412',
      ],
    ],
  ],
  'created' => [
    'en' => [
      0 => [
        'value' => '1547719026',
      ],
    ],
    'be' => [
      0 => [
        'value' => '1547719101',
      ],
    ],
  ],
  'changed' => [
    'en' => [
      0 => [
        'value' => '1579287902',
      ],
    ],
    'be' => [
      0 => [
        'value' => '1579287921',
      ],
    ],
  ],
  'content_translation_source' => [
    'en' => [
      0 => [
        'value' => 'und',
      ],
    ],
    'be' => [
      0 => [
        'value' => 'en',
      ],
    ],
  ],
  'content_translation_outdated' => [
    'en' => [
      0 => [
        'value' => 0,
      ],
    ],
    'be' => [
      0 => [
        'value' => 0,
      ],
    ],
  ],
  'promote' => [
    'en' => [
      0 => [
        'value' => '1',
      ],
    ],
    'be' => [
      0 => [
        'value' => '1',
      ],
    ],
  ],
  'sticky' => [
    'en' => [
      0 => [
        'value' => '1',
      ],
    ],
    'be' => [
      0 => [
        'value' => '0',
      ],
    ],
  ],
  'default_langcode' => [
    'en' => [
      0 => [
        'value' => '1',
      ],
    ],
    'be' => [
      0 => [
        'value' => '0',
      ],
    ],
  ],
  'revision_default' => [
    'en' => [
      0 => [
        'value' => '1',
      ],
    ],
    'be' => [
      0 => [
        'value' => '1',
      ],
    ],
  ],
  'revision_translation_affected' => [
    'en' => [
      0 => [
        'value' => '1',
      ],
    ],
    'be' => [
      0 => [
        'value' => '1',
      ],
    ],
  ],
  'path' => [
    'en' => [
      0 => [
        'langcode' => 'en',
      ],
    ],
    'be' => [
      0 => [
        'langcode' => 'be',
      ],
    ],
  ],
  'body' => [
    'en' => [
      0 => [
        'value' => "<p>A test page node</p>\r\n",
        'summary' => '',
        'format' => 'basic_html',
      ],
    ],
    'be' => [
      0 => [
        'value' => "<p>Гэта тэст</p>\r\n",
        'summary' => '',
        'format' => 'basic_html',
      ],
    ],
  ],
  'field_image_label' => [
    'en' => [
      0 => [
        'target_id' => 'f182f484-4555-4958-aa47-fc444d213844',
        'alt' => 'Alt_text_en',
        'title' => '',
        'width' => '768',
        'height' => '512',
      ],
    ],
    'be' => [
      0 => [
        'target_id' => '3a219fb6-8e15-4bba-9c3e-a77a2429baae',
        'alt' => 'Alt_text_be',
        'title' => '',
        'width' => '768',
        'height' => '512',
      ],
    ],
  ],
];

$expectations = [
  'b0137bab-a80e-4305-84fe-4d99ffd906c5' => new CdfExpectations($data, [
    'nid',
    'vid',
  ]),
];

return $expectations;

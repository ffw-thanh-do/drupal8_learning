<?php

// Remove any .git directories which may have been added by Composer.
// Adapted from https://github.com/drupal-composer/drupal-project/issues/223#issuecomment-266417254
$roots = [__DIR__ . '/docroot', __DIR__ . '/vendor'];

foreach ($roots as $root) {
    exec('find ' . $root . ' -name \'.git\' | xargs rm -rf');
}

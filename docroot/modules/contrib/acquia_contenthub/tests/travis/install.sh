#!/usr/bin/env bash

# NAME
#     install.sh - Install Travis CI dependencies
#
# SYNOPSIS
#     install.sh
#
# DESCRIPTION
#     Creates the test fixture.

set -ev

cd "$(dirname "$0")" || exit; source _includes.sh

# Create a fixture for the DEPLOY job.
if [[ "$DEPLOY" ]]; then
  mysql -e 'CREATE DATABASE drupal;'
  orca fixture:init \
    -f \
    --sut=drupal/acquia_contenthub \
    --sut-only \
    --core="$CORE" \
    --no-sqlite \
    --no-site-install
  cd "$ORCA_FIXTURE_DIR/docroot/sites/default" || exit 1
  cp default.settings.php settings.php
  chmod 775 settings.php
  drush site:install \
    minimal \
    --db-url=mysql://root:@127.0.0.1/drupal \
    --site-name=ORCA \
    --account-name=admin \
    --account-pass=admin \
    --no-interaction \
    --verbose \
    --ansi
fi

# Create a fixture for the DEPLOY job.
if [[ "$DO_DEV" ]]; then
  mysql -e 'CREATE DATABASE drupal;'
  orca fixture:init \
    -f \
    --sut=drupal/acquia_contenthub \
    --sut-only \
    --core="$CORE" \
    --dev \
    --no-sqlite \
    --no-site-install
  cd "$ORCA_FIXTURE_DIR/docroot/sites/default" || exit 1
  cp default.settings.php settings.php
  chmod 775 settings.php
  drush site:install \
    minimal \
    --db-url=mysql://root:@127.0.0.1/drupal \
    --site-name=ORCA \
    --account-name=admin \
    --account-pass=admin \
    --no-interaction \
    --verbose \
    --ansi
fi

# Exit early in the absence of a fixture.
[[ -d "$ORCA_FIXTURE_DIR" ]] || exit 0

if [[ "$ORCA_JOB" == "D9_READINESS" ]]; then
composer -d"$ORCA_FIXTURE_DIR" require --dev \
  drupal/paragraphs \
  drupal/metatag \
  symfony/phpunit-bridge:^4.4 \
  mikey179/vfsStream:~1.2 \
  drupal/s3fs:^3
elif [[ "$ORCA_JOB" == "CORE_PREVIOUS" || "$CORE" == "~8.7.0" ]]; then
composer -d"$ORCA_FIXTURE_DIR" require --dev \
  drupal/paragraphs \
  drupal/metatag \
  symfony/phpunit-bridge:^3.4.3 \
  mikey179/vfsStream:~1.2
else
composer -d"$ORCA_FIXTURE_DIR" require --dev \
  drupal/paragraphs \
  drupal/metatag \
  symfony/phpunit-bridge:^3.4.3 \
  mikey179/vfsStream:~1.2 \
  drupal/s3fs:^3
fi

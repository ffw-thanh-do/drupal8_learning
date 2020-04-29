#!/usr/bin/env bash

set -ev

cd "$(dirname "$0")" || exit; source _includes.sh

if [[ "$ORCA_JOB" == "CORE_PREVIOUS" || "$CORE" == "~8.7.0" ]]; then
  rm -Rf "$ORCA_SUT_DIR/modules/acquia_contenthub_s3"
fi

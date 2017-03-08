#!/bin/bash
set -e

DATABASE_HOST="$1"
DATABASE_PORT="$2"

composer config -g repositories.pkgundertest path ${PACKAGE_PATH}
composer config -g repositories.libundertest path ${LIB_PATH}
composer require --ignore-platform-reqs --dev --no-suggest phing/phing:2.*
composer require --ignore-platform-reqs --prefer-source ${PACKAGE_NAME} @dev

./bin/magento module:enable --all

retry -w 30 -n 120 -s "mysql -e 'SELECT 1' --user=root --host=$DATABASE_HOST --port=$DATABASE_PORT;" "echo 'Database ready'"

# Moved to ../../build.sh for compatibility reasons
# ./bin/magento setup:di:compile
# ./bin/magento setup:upgrade

echo "Startup finished!"

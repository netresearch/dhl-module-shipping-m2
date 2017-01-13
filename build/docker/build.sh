#!/bin/bash
export COMPOSE_HTTP_TIMEOUT=3600
( cd $(dirname $(readlink -f $0))


for dir in `ls -d */`;
do
    (
        echo "Building container for $dir";
        cd $dir;
        sudo docker-compose -p $BUILD_TAG up -d
        until sudo docker-compose -p $BUILD_TAG logs app | grep 'Startup finished'; do echo "Waiting on all containers being ready..."; sleep 30; done;

        echo "Run Unittests"
        sudo docker-compose -p $BUILD_TAG exec env /var/www/magetwo.nrdev.de/vendor/phpunit/phpunit/phpunit -c /var/www/magetwo.nrdev.de/vendor/dhl/module-versenden-m2/Test/Unit/phpunit.xml --filter /^Dhl/ --bootstrap /var/www/magetwo.nrdev.de/dev/tests/unit/framework/bootstrap.php

        echo "Run CodeSniffer"
        sudo docker-compose -p $BUILD_TAG exec env /var/www/magetwo.nrdev.de/vendor/squizlabs/php_codesniffer/scripts/phpcs -p --report-checkstyle=/opt/reports/checkstyle.xml --standard=MEQP2 --ignore=Test --runtime-set ignore_warnings_on_exit true /var/www/magetwo.nrdev.de/vendor/dhl/module-versenden-m2/

        echo "Shutdown containers"
        sudo docker-compose -p $BUILD_TAG down --rmi local

        cd ..
    );
done

)
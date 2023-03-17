#!/usr/bin/env bash

# Get composer
EXPECTED_SIGNATURE="c252c2a2219956f88089ffc242b42c8cb9300a368fd3890d63940e4fc9652345"
php -r "copy('https://getcomposer.org/download/2.4.4/composer.phar', 'composer.phar');"
ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha256', 'composer.phar');")"

if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]
then
    >&2 echo 'ERROR: Invalid installer signature'
    rm composer-setup.php
    exit 1
fi

# Run composer
php composer-setup.php
rm composer-setup.php

# Remove vendor directory
rm -rf vendor
rm -rf build

# Run composer install
php composer.phar install

# Require and run php-scoper
# Locked to version compatible with version of composer
php composer.phar global require humbug/php-scoper
COMPOSER_BIN_DIR="$(composer global config bin-dir --absolute)"
"$COMPOSER_BIN_DIR"/php-scoper add-prefix

# Dump composer autoload for build directory
php composer.phar dump-autoload --working-dir build --classmap-authoritative

# Remove composer
rm composer.phar

# Remove existing build zip file
rm openmage-onpay.zip

# Rsync contents of directory to new directory that we will use for the build
rsync -Rr ./* ./openmage-onpay

# Remove directories and files from newly created directory, that we won't need in final build
rm -rf ./openmage-onpay/vendor
rm ./openmage-onpay/build.sh
rm ./openmage-onpay/composer.json
rm ./openmage-onpay/composer.lock
rm ./openmage-onpay/scoper.inc.php
rm ./openmage-onpay/LICENSE
rm ./openmage-onpay/CHANGELOG.md
rm ./openmage-onpay/README.md
rm ./openmage-onpay/map
rm ./openmage-onpay/mapper.php

mv ./openmage-onpay/build ./openmage-onpay/app/code/community/Onpayio/Onpay/Model/

# Perform file map check and cleanup afterwards
FILE_MAP_RESULT="$(php -f mapper.php path='./openmage-onpay')"
if [ "$FILE_MAP_RESULT" != "" ]
then
    >&2 echo 'ERROR: check of mapped files failed'
    echo "$FILE_MAP_RESULT"

    # Clean up
    rm -rf openmage-onpay
    rm -rf vendor
    rm -rf build

    exit 1
fi

# Navigate to build directory
cd openmage-onpay

# Zip contents of build directory
zip -r openmage-onpay.zip ./*
mv openmage-onpay.zip ../

# Navigate up
cd ..

# Clean up
rm -rf openmage-onpay
rm -rf vendor
rm -rf build
FROM php:7.1-cli

RUN pecl install xdebug && \
    echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20160303/xdebug.so" >> /usr/local/etc/php/php.ini
VOLUME /usr/src/lib
WORKDIR /usr/src/lib
ENTRYPOINT vendor/bin/phpunit --coverage-clover=coverage.clover

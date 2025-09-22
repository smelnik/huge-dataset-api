FROM php:8.4-fpm

RUN \
    apt-get update && \
    apt-get install -y --no-install-recommends \
      git unzip zip curl libicu-dev zlib1g-dev libssl-dev && \
    docker-php-ext-install bcmath opcache intl && \
    pecl install redis xdebug && \
    docker-php-ext-enable redis xdebug && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    rm -rf /var/lib/apt/lists/* /tmp/pear

WORKDIR /app

ARG USER_ID

RUN /bin/bash -c "if [[ ${USER_ID} -ne 0 ]]; then usermod -u ${USER_ID} www-data; fi"


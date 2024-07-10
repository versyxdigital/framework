FROM php:8.2-apache

# Update
RUN apt-get update --fix-missing && \
    apt-get upgrade -y && \
    apt-get --no-install-recommends install -y apt-utils && \
    rm -rf /var/lib/apt/lists/*

# Install stuff
RUN apt-get -y update && \
    apt-get -y --no-install-recommends install nano wget iproute2 && \
    apt-get -y install libsqlite3-dev libsqlite3-0 && \
    apt-get -y --no-install-recommends install default-mysql-client zlib1g-dev libzip-dev libicu-dev && \
    apt-get -y --no-install-recommends install --fix-missing apt-utils build-essential git curl libonig-dev && \
    apt-get -y --no-install-recommends install --fix-missing libcurl4 libcurl4-openssl-dev zip openssl && \
    rm -rf /var/lib/apt/lists/* && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \

# Install PHP extensions
RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd && \
    docker-php-ext-configure mysqli --with-mysqli=mysqlnd && \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install pdo_sqlite && \
    docker-php-ext-install mysqli && \
    docker-php-ext-install zip && \
    docker-php-ext-install -j$(nproc) intl && \
    docker-php-ext-install gettext && \
    docker-php-ext-install calendar && \
    docker-php-ext-install exif

# Setup apache
COPY start-apache /usr/local/bin
RUN a2enmod headers rewrite
RUN chown -R www-data:www-data /var/www
CMD ["start-apache"]
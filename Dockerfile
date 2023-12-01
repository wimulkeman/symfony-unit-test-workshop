# src: https://github.com/qlico/qlico/blob/main/project-examples/Dockerfile.php80-libvips
FROM php:8.2-fpm-alpine3.16 as base
LABEL maintainer="Qlico <hello@qlico.dev>"

# PHP-FPM defaults
ENV FCGI_CONNECT=9000
ENV FCGI_BACKLOG=128
ENV PHP_FPM_PM=dynamic
ENV PHP_FPM_PM_MAX_CHILDREN=5
ENV PHP_FPM_PM_START_SERVERS=2
ENV PHP_FPM_PM_MIN_SPARE_SERVERS=1
ENV PHP_FPM_PM_MAX_SPARE_SERVERS=3
ENV PHP_FPM_PM_PROCESS_IDLE_TIMEOUT=10
ENV PHP_FPM_PM_MAX_REQUESTS=1000

# PHP-OPcache defaults
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_MEMORY_CONSUMPTION=256
ENV PHP_OPCACHE_MAX_ACCELERATED_FILES=20000
ENV PHP_OPCACHE_REVALIDATE_FREQUENCY=0
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
ENV PHP_OPCACHE_PRELOAD=/var/www/html/config/preload.php
ENV PHP_OPCACHE_PRELOAD_USER=qlico

# Datadog
ENV DDTRACE_VERSION=0.86.3
ENV DDTRACE_DOWNLOAD_URL=https://github.com/DataDog/dd-trace-php/releases/download/${DDTRACE_VERSION}/datadog-php-tracer_${DDTRACE_VERSION}_noarch.apk

ARG LOCAL_USER_ID=1000
ARG LOCAL_GROUP_ID=1000

# persistent / runtime depsfmusl
ENV PHPIZE_DEPS \
    autoconf \
    cmake \
    file \
    freetype-dev \
    g++ \
    gcc \
    gettext-dev \
    git \
    icu-dev \
    imagemagick-dev \
    libc-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    make \
    pcre-dev \
    pkgconf \
    postgresql-dev \
    rabbitmq-c-dev \
    re2c \
    vips-dev

RUN apk add --no-cache --virtual .persistent-deps \
    fcgi \
    freetype \
    gettext \
    git \
    gnu-libiconv \
    icu \
    icu-data-full \
    imagemagick \
    libintl \
    libjpeg-turbo \
    libpng \
    libpq \
    libtool \
    libzip \
    vips \
    rabbitmq-c \
    && apk add --no-cache --virtual .build-deps \
       $PHPIZE_DEPS \
    && docker-php-ext-configure bcmath --enable-bcmath \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-configure soap --enable-soap \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j "$(nproc)" \
       bcmath \
       exif \
       gd \
       gettext \
       intl \
       opcache \
       pcntl \
       pdo_mysql \
       pdo_pgsql \
       shmop \
       soap \
       sockets \
       sysvmsg \
       sysvsem \
       sysvshm \
       zip \
    && pecl install \
       amqp-1.11.0 \
       APCu \
       ds \
       imagick \
       redis \
       vips \
    && docker-php-ext-enable \
       apcu \
       amqp \
       ds \
       imagick \
       redis \
       vips \
    && apk del .build-deps \
    && docker-php-source delete \
    && apk --no-cache -U upgrade \
    && rm -rf /tmp/* \
    && addgroup -g $LOCAL_GROUP_ID -S qlico \
    && adduser -u $LOCAL_USER_ID -S qlico -G qlico

# Install composer
COPY --from=composer:lts /usr/bin/composer /usr/local/bin/composer.phar

# Necessary for iconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

# Add composer and php scripts for aliases.
COPY qlico/services/php/scripts /usr/local/sbin
RUN chmod +x /usr/local/sbin/composer \
             /usr/local/sbin/php \
             /usr/local/sbin/xphp

# Disabled access logs for php-fpm
RUN sed -i 's/access.log = \/proc\/self\/fd\/2/access.log = \/proc\/self\/fd\/1/g' /usr/local/etc/php-fpm.d/docker.conf

# php.ini
COPY qlico/services/php/prod/php.ini $PHP_INI_DIR

# Remove configuration files which are templated during the entrypoint command
RUN rm /usr/local/etc/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/www.conf.default

# www.cnf
COPY qlico/services/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Don't run as the default (root) user.
USER qlico

CMD ["php-fpm"]

FROM base as dev
USER root

RUN apk add --no-cache --virtual . \
    # Local mail handling
    msmtp

RUN set -xe \
    && apk add --no-cache --virtual .build-deps \
       $PHPIZE_DEPS \
    && pecl install \
       xdebug \
    && docker-php-ext-enable \
       xdebug \
    && apk del .build-deps \
    # Install Blackfire
    && version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && architecture=$(case $(uname -m) in i386 | i686 | x86) echo "i386" ;; x86_64 | amd64) echo "amd64" ;; aarch64 | arm64 | armv8) echo "arm64" ;; *) echo "amd64" ;; esac) \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/alpine/$architecture/$version \
    && mkdir -p /tmp/blackfire \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8307\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz \
    && mkdir -p /tmp/blackfire \
    && architecture=$(case $(uname -m) in i386 | i686 | x86) echo "i386" ;; x86_64 | amd64) echo "amd64" ;; aarch64 | arm64 | armv8) echo "arm64" ;; *) echo "amd64" ;; esac) \
    && curl -A "Docker" -L https://blackfire.io/api/v1/releases/cli/linux/$architecture | tar zxp -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
    && rm -Rf /tmp/blackfire \
    && rm -rf /tmp/*

# MSMTP config.
COPY qlico/services/php/dev/msmtprc /etc/msmtprc

# Xdebug config.
COPY qlico/services/php/dev/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# php.ini
COPY qlico/services/php/dev/php.ini /usr/local/etc/php

# Don't run as the default (root) user.
USER qlico

CMD ["php-fpm"]

FROM base as prod
USER root

RUN set -xe \
    # Remove packages we don't want in production
    && rm -rf /usr/local/sbin/composer \
    && rm -rf /usr/local/bin/composer.phar \
    && rm -rf /usr/local/sbin/xphp \
    && apk del git \
    && rm -rf /usr/bin/git \
    # Install Datadog APM \
    && curl -LO https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php \
    && php datadog-setup.php --php-bin=all \
    # create app folder
    && mkdir /app \
    && chown qlico: /app

COPY qlico/services/php/zzz-opcache.ini $PHP_INI_DIR

# Copy the application to the Docker image.
COPY --chown=qlico:qlico . /app

# Remove qlico folder.
RUN rm -rf /app/qlico

# Don't run as the default (root) user.
USER qlico

CMD ["php-fpm"]

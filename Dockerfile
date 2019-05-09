ARG COMPOSER_FLAGS="--no-interaction --no-suggest --no-progress --ansi"

###### base stage ######
FROM php:7.3-fpm-alpine as base

ARG COMPOSER_FLAGS

# persistent deps
RUN apk add --no-cache bash fcgi postgresql-dev

# php extensions
RUN apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
    && docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) pdo_pgsql \
    && apk del .phpize-deps

# global dependencies
RUN curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=1.8.5 && \
    curl -fsSL https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/v0.2.0/php-fpm-healthcheck \
         -o /usr/local/bin/php-fpm-healthcheck && chmod +x /usr/local/bin/php-fpm-healthcheck && \
    curl -fsSL https://raw.githubusercontent.com/vishnubob/wait-for-it/9995b721327eac7a88f0dce314ea074d5169634f/wait-for-it.sh \
         -o /usr/local/bin/wait-for && chmod +x /usr/local/bin/wait-for


# setup user
WORKDIR /app
ARG APP_UID=1000
ARG APP_GID=1000
RUN addgroup -g $APP_GID app && adduser -D -G app -u $APP_UID app && chown app:app .
USER app

# environment
ENV HOME /home/app
ENV PATH ${PATH}:${HOME}/.composer/vendor/bin:${HOME}/bin:/app/vendor/bin:/app/bin

# global composer deps
RUN composer global require hirak/prestissimo $COMPOSER_FLAGS

# custom php config
COPY infra/php.ini /usr/local/etc/php/
COPY infra/php-fpm.conf /usr/local/etc/php-fpm.d/zz-custom.conf


###### dev stage ######
FROM base as dev

ARG COMPOSER_FLAGS

# project composer deps
COPY --chown=app:app composer.* ./
RUN composer install $COMPOSER_FLAGS --no-scripts --no-autoloader

# copy project sources
COPY --chown=app:app . ./

# rerun composer to trigger scripts and dump the autoloader
RUN composer install $COMPOSER_FLAGS


###### production stage ######
FROM base

ARG COMPOSER_FLAGS

# project composer deps
COPY --chown=app:app composer.* ./
RUN composer install $COMPOSER_FLAGS --no-scripts --no-autoloader --no-dev

# copy project sources cherry picking only production files
COPY --chown=app:app index.php ./
COPY --chown=app:app src ./
COPY --chown=app:app vendor ./

# rerun composer to trigger scripts and dump the autoloader
RUN composer install $COMPOSER_FLAGS --no-dev --optimize-autoloader

FROM php:7.2-fpm-alpine
MAINTAINER Quentin Bonaventure <q.bonaventure@gmail.com>

RUN apk --update --no-cache add \
    git \
    gmp-dev \
    postgresql-dev && \
    /usr/local/bin/docker-php-ext-configure gmp && \
    docker-php-ext-install bcmath pgsql pdo pdo_pgsql gmp && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
    mkdir /app && \
    rm -rf /var/cache/apk/*
   
ENV PATH="/app/vendor/bin:/app/bin:${PATH}"

WORKDIR /app

COPY ./composer.* /app/
RUN  cd /app && composer install --no-dev -o

COPY ./src/ /app/src/
COPY ./public/ /app/public/
COPY ./config/ /app/config/
COPY ./bin/ /app/bin
COPY ./data/ /app/data/
COPY entrypoint.sh /
RUN ls /app/config
RUN ls /app/config/autoload
RUN cp /app/config/autoload/bot.local.php.dist /app/config/autoload/bot.local.php && \
    cp /app/config/autoload/db.local.php.dist /app/config/autoload/db.local.php && \
    cp /app/config/autoload/session.local.php.dist /app/config/autoload/session.local.php && \
    cp /app/config/autoload/cache.local.php.dist /app/config/autoload/cache.local.php 

RUN chmod +x /entrypoint.sh && \
  chmod a+w /app/data/cache/ && \
  chown -R www-data:www-data /app
  

  
ENTRYPOINT ["/entrypoint.sh"]

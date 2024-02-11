FROM php:8.2-cli-bullseye

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /usr/local/dependency-visualizer

COPY . /usr/local/dependency-visualizer

RUN apt-get update && apt-get install -y --no-install-recommends \
        zip unzip git vim \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pcntl opcache
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN cp /usr/local/dependency-visualizer/php.ini /usr/local/etc/php/php.ini

CMD ["/bin/bash"]

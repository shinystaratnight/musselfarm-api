FROM php:7.4-fpm

ENV MASTER_DIR=/var/www/phppgadmin

RUN apt-get update && apt-get install -y \
    build-essential \
    curl \
    git \
    jpegoptim optipng pngquant gifsicle \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libpq-dev \
    locales \
    unzip \
    vim \
    zip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

COPY . ${MASTER_DIR}

WORKDIR ${MASTER_DIR}

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pgsql pdo_pgsql

RUN docker-php-ext-install bcmath mbstring exif pcntl pdo_mysql zip && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]

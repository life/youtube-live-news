FROM php:8.3-fpm

RUN apt-get update && apt-get install -y libpq-dev libcurl4-openssl-dev \
    ffmpeg python3 python3-pip \
    && docker-php-ext-install pdo pdo_pgsql curl \
    && pip3 install --break-system-packages yt-dlp \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

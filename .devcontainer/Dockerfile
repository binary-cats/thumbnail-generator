# Base image is from Microsoft
FROM mcr.microsoft.com/devcontainers/base:ubuntu26.04

# Avoid interactive prompts (e.g. tzdata) during installation
ENV DEBIAN_FRONTEND=noninteractive

RUN mkdir -p /var/www/html

# Install Dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    ffmpeg \
    ghostscript \
    imagemagick \
    php8.5-cli \
    php8.5-gd \
    php8.5-imagick \
    php8.5-bcmath \
    php8.5-curl \
    php8.5-mbstring \
    php8.5-pcov \
    php8.5-xml \
    php8.5-xsl \
    php8.5-zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && composer --version
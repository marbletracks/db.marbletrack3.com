FROM php:8.1-cli

# Install PHP extensions needed for your project
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install git (needed by composer)
RUN apt-get update && apt-get install -y git zip unzip

WORKDIR /app
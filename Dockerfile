FROM php:8.2-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo pdo_mysql zip gd

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Activer les modules Apache
RUN a2enmod rewrite

# Copier les fichiers
WORKDIR /var/www/html
COPY . .

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN composer install --optimize-autoloader --no-dev

# Configurer les permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configurer Apache
COPY .docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Démarrer Apache
EXPOSE 80
CMD ["apache2-foreground"]

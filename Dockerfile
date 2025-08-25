FROM php:8.2-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd zip

# Nettoyer le cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurer Apache
RUN a2enmod rewrite
COPY .docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers nécessaires pour l'installation des dépendances
COPY composer.json composer.lock ./

# Installer les dépendances
RUN composer install --no-scripts --no-autoloader --no-dev --no-progress --no-interaction --ignore-platform-reqs

# Copier le reste des fichiers
COPY . .

# Configurer les permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Créer le fichier de base de données SQLite
RUN mkdir -p database && touch database/database.sqlite

# Copier manuellement le fichier .env
COPY .env.example .env

# Générer la clé d'application
RUN php artisan key:generate

# Mettre en cache la configuration
RUN php artisan config:cache
RUN php artisan route:cache

# Exposer le port 80 et démarrer Apache
EXPOSE 80
CMD ["apache2-foreground"]

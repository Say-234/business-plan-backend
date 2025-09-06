FROM php:8.2-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite zip

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

# Supprimer l'ancien fichier s'il existe
RUN rm -f database/database.sqlite

# Créer la base SQLite
RUN touch database/database.sqlite
RUN chmod 777 database/database.sqlite
RUN chown -R www-data:www-data database/database.sqlite

# Configurer Apache
COPY .docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Démarrer Apache
EXPOSE 80
CMD ["apache2-foreground"]

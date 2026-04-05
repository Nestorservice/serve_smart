FROM php:8.2-apache

# Activer le module de réécriture d'URL (obligatoire pour le routeur MVC)
RUN a2enmod rewrite

# Installer les certificats SSL (requis pour Aiven/cloud DB) et les extensions PHP
RUN apt-get update && apt-get install -y \
    ca-certificates \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP nécessaires pour la BDD (PDO MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# Configurer Apache ServerName pour supprimer le warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copier tous les fichiers du projet 
COPY . /var/www/html/

# Configurer les droits d'accès
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposer le port HTTP 80
EXPOSE 80

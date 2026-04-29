# Use the official PHP image with Apache web server
FROM php:8.2-apache

# Install PDO and the MySQL extension so PHP can connect to the database
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache's rewrite module for URL routing
RUN a2enmod rewrite

# Copy all PHP scripts into the Apache web directory
COPY . /var/www/html/

# Set appropriate permissions for the web server
RUN chown -R www-data:www-data /var/www/html/
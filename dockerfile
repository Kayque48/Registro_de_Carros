# Base: PHP 8 + Apache
FROM php:8.0-apache

# Instala a extensão mysqli
RUN docker-php-ext-install mysqli

# Ativa mod_rewrite do Apache (opcional)
RUN a2enmod rewrite

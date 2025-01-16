# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# Instala las extensiones requeridas para Laravel
RUN apt-get update && apt-get install -y \
    zip unzip git curl libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configura el directorio de trabajo
WORKDIR /var/www/html

# Copia los archivos del proyecto
COPY . .

# Establece los permisos correctos para las carpetas de almacenamiento
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Habilita el módulo de reescritura de Apache
RUN a2enmod rewrite

# Expone el puerto 80
EXPOSE 80

# Comando para iniciar Apache
CMD ["apache2-foreground"]

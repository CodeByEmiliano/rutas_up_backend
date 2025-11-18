FROM php:8.2-apache

# 1. Instalar dependencias del sistema y librerías de Postgres
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# 2. Activar mod_rewrite de Apache (Necesario para Laravel)
RUN a2enmod rewrite

# 3. Configurar la raíz de Apache a la carpeta /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 4. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Copiar todo el código del proyecto
WORKDIR /var/www/html
COPY . .

# 6. Instalar dependencias de Laravel y render
RUN composer install --no-dev --optimize-autoloader

# 7. Dar permisos a las carpetas de almacenamiento
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
FROM php:8.2-apache

# 1. Instalar dependencias del sistema y librerías de Postgres
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# 2. Activar mod_rewrite de Apache (Necesario para Laravel)
RUN a2enmod rewrite

# 3. Configurar la raíz de Apache a la carpeta /public y habilitar .htaccess
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Habilitar AllowOverride All en la configuración del sitio para que funcione .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/sites-available/000-default.conf

# Apuntar el DocumentRoot a la carpeta public de Laravel
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 4. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Copiar todo el código del proyecto
WORKDIR /var/www/html
COPY . .

# 6. Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader
# NOTA: La limpieza de caché ocurre cuando el contenedor arranca

# 7. Dar permisos a las carpetas de almacenamiento (Storage)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
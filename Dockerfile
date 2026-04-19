FROM php:8.2-apache

# Install PHP extensions for MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Apache DocumentRoot to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set timezone
RUN ln -sf /usr/share/zoneinfo/Asia/Kuala_Lumpur /etc/localtime

# Copy application files
COPY . /var/www/html/

# Create required directories
RUN mkdir -p /var/www/html/logs/admin \
    && mkdir -p /var/www/html/public/assets/uploads \
    && chown -R www-data:www-data /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/public/assets/uploads \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

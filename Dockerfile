# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    nodejs \
    npm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot to point to Laravel's public directory
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf && \
    echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Copy application files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Install Node dependencies and build assets
RUN npm ci && npm run build && npm cache clean --force

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    /var/www/html/database && \
    touch /var/www/html/database/database.sqlite && \
    chown -R www-data:www-data /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/database && \
    chmod -R 775 /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/database

# Copy environment file and generate key
RUN cp .env.example .env || true && \
    php artisan key:generate --force

# Run migrations
RUN php artisan migrate --force

# Clear any config cache and optimize for production
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Ensure build directory has correct permissions
RUN chown -R www-data:www-data /var/www/html/public/build

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]


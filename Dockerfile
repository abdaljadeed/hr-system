FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    nodejs \
    npm \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    sqlite-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql pdo_sqlite mbstring zip gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN cp .env.example .env

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN npm install && npm run build

RUN php artisan key:generate --force

RUN touch database/database.sqlite \
    && php artisan migrate --force --seed \
    && php artisan storage:link \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8080

CMD ["sh", "-c", "php artisan config:cache && php artisan serve --host=0.0.0.0 --port=8080"]

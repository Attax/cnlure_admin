# Dockerfile

# 使用 PHP 8.4 FPM Alpine 镜像
FROM php:8.4-fpm-alpine

# 安装系统依赖和 PHP 扩展
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev

# 配置并安装 PHP 扩展
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /var/www/html

# 复制 composer 文件并安装依赖（利用 Docker 缓存层）
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# 复制项目文件
COPY . .

# 设置存储目录权限
RUN chown -R www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap/cache

# 创建非 root 用户
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www
USER www

# 暴露端口
EXPOSE 9000

# 启动 PHP-FPM
CMD ["php-fpm"]

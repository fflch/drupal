FROM php:7.4-apache

# Pacotes do sistema
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    unixodbc \
    unixodbc-dev \
    freetds-bin \
    freetds-dev \
    libicu-dev \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libjpeg-dev \
    libfreetype6-dev \ 
    curl

# Cria um link simbólico para que o PHP encontre as bibliotecas do FreeTDS
RUN ln -s /usr/lib/x86_64-linux-gnu/libsybdb.a /usr/lib/

# Limpeza
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalação das extensões PHP
RUN docker-php-ext-install \
    intl \
    pdo_mysql \
    soap \
    zip \
    mbstring \
    bcmath \
    pdo_dblib

# Configuração da GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

# php memory e outros ajustes
RUN echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-ram.ini \
    && echo 'upload_max_filesize = 512M' >> /usr/local/etc/php/conf.d/docker-php-ram.ini \
    && echo 'post_max_size = 512M' >> /usr/local/etc/php/conf.d/docker-php-ram.ini

# Restante das configurações (Apache, Memory, etc) seguem iguais...
RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/web|' /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html
CMD ["apache2-foreground"]
FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

# 1. Instalação de dependências do sistema e adição do PPA com suporte ao PHP 8.1
RUN apt-get update && apt-get install -y \
    software-properties-common \
    curl \
    git \
    unzip \
    mysql-client \
    unixodbc \
    freetds-bin \
    && add-apt-repository ppa:ondrej/php -y \
    && apt-get update

# 2. Instalação do Apache e Extensões PHP 8.1 (pacotes equivalentes aos do Dockerfile original)
RUN apt-get install -y \
    apache2 \
    libapache2-mod-php8.1 \
    php8.1-cli \
    php8.1-intl \
    php8.1-mysql \
    php8.1-soap \
    php8.1-zip \
    php8.1-mbstring \
    php8.1-bcmath \
    php8.1-sybase \
    php8.1-gd \
    php8.1-curl \
    php8.1-xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Link simbólico para FreeTDS (se necessário)
RUN ln -s /usr/lib/x86_64-linux-gnu/libsybdb.a /usr/lib/ || true

# 3. Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Ajustes de memória e upload no php.ini do Apache
RUN echo 'memory_limit = 512M' >> /etc/php/8.1/apache2/conf.d/docker-php-ram.ini \
    && echo 'upload_max_filesize = 512M' >> /etc/php/8.1/apache2/conf.d/docker-php-ram.ini \
    && echo 'post_max_size = 512M' >> /etc/php/8.1/apache2/conf.d/docker-php-ram.ini

# 5. Configurações do Apache
RUN a2enmod rewrite php8.1
RUN sed -i 's|/var/www/html|/var/www/html/web|' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

ENV COMPOSER_HOME=/tmp/composer
ENV COMPOSER_ALLOW_UNSAFE_PHAR_METADATA=1

# Permite que o git (usado pelo composer) opere em diretórios de outro dono,
# ex.: volume montado e cache VCS quando se roda `docker exec ... composer` como root
RUN git config --system --add safe.directory '*'

RUN chown www-data:www-data /var/www/html

USER www-data
COPY --chown=www-data . .
RUN composer install --no-dev --optimize-autoloader --no-interaction
USER root

# Não é a melhor solução...
RUN mkdir -p /var/www/html/config/sync /var/www/html/web/sites/default/files \
    && chown -R www-data:www-data /var/www/html/config /var/www/html/web/sites/default/files \
    && chmod -R 775 /var/www/html/config /var/www/html/web/sites/default/files

EXPOSE 80
CMD ["apache2ctl", "-D", "FOREGROUND"]
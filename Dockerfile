FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

# 1. Instalação de dependências do sistema e adição do PPA com suporte ao PHP 7.4
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

# 2. Instalação do Apache e Extensões PHP 7.4 (pacotes equivalentes aos do Dockerfile original)
RUN apt-get install -y \
    apache2 \
    libapache2-mod-php7.4 \
    php7.4-cli \
    php7.4-intl \
    php7.4-mysql \
    php7.4-soap \
    php7.4-zip \
    php7.4-mbstring \
    php7.4-bcmath \
    php7.4-sybase \
    php7.4-gd \
    php7.4-curl \
    php7.4-xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Link simbólico para FreeTDS (se necessário)
RUN ln -s /usr/lib/x86_64-linux-gnu/libsybdb.a /usr/lib/ || true

# 3. Composer (Versão 2.2 LTS ideal para PHP 7.4)
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# 4. Ajustes de memória e upload no php.ini do Apache
RUN echo 'memory_limit = 512M' >> /etc/php/7.4/apache2/conf.d/docker-php-ram.ini \
    && echo 'upload_max_filesize = 512M' >> /etc/php/7.4/apache2/conf.d/docker-php-ram.ini \
    && echo 'post_max_size = 512M' >> /etc/php/7.4/apache2/conf.d/docker-php-ram.ini

# 5. Configurações do Apache
RUN a2enmod rewrite php7.4
RUN sed -i 's|/var/www/html|/var/www/html/web|' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

EXPOSE 80
CMD ["apache2ctl", "-D", "FOREGROUND"]
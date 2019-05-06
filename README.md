# Procedimentos para deploy no ambiente dev:

    v='7.3'
    apt-get install php$v php$v-common php$v-cli php$v-gd php$v-curl php$v-xml php$v-mbstring php-sqlite3
    apt-get install sqlite3

Composer:

    curl -s https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer

Download e instalação das dependências:

    git clone git@github.com:SEU-USERNAME/drupal8.git
    cd drupal8
    composer install

Instalação em pt-br usando o profile fflch:

    ./vendor/bin/drupal site:install fflchprofile --db-type="sqlite" \
           --site-name="tests" --site-mail="admin@example.com" \
           --account-name="admin" --account-mail="admin@example.com" --account-pass="admin" \
           --no-interaction

Servidor http básico:

    cd drupal8
    ./vendor/bin/drupal serve -vvv

Caso queira escolher ip e porta:

    ./vendor/bin/drupal server 0.0.0.0:8000 -vvv

Se quiser apagar o banco para fazer uma instalação zerada:

    cd drupal8
    rm web/sites/default/files/.ht.sqlite

## Observação:

Os módulos, temas e bibliotecas realmente requeridos, isto é, que por default são instalados e configurados no site modelo entregue aos usuários estão no profile fflch,
disponível em [https://github.com/fflch/fflchprofile](https://github.com/fflch/fflchprofile). Neste repositório estão apenas os módulos, temas e
bibliotecas usados em apenas alguns sites.

## Exemplos de instalação de novos módulos:

    cd drupal8
    composer require drupal/webform:5.1
    composer require drupal/smtp:1.0-beta4

## libraries são instaladas usando assest-packgist:

Consulte o nome da biblioteca em https://asset-packagist.org e
depois instale:

    composer require npm-asset/datetimepicker:0.1.38

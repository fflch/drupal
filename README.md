# Procedimentos para deploy no ambiente dev:

Download e instalação das dependências:

    git clone git@github.com:SEU-USERNAME/drupal8.git
    cd drupal8
    composer install

Instalação em pt-br usando o profile fflch:

    cd drupal8
    ./vendor/bin/drupal site:install fflch --langcode="pt-br" --db-type="sqlite" \
           --site-name="tests" --site-mail="admin@example.com" \
           --account-name="admin" --account-mail="admin@example.com" --account-pass="admin" \
           --no-interaction

Servidor http básico:

    cd drupal8
    ./vendor/bin/drupal serve -vvv

Se quiser apagar o banco para fazer uma instalação zerada:

    cd drupal8
    rm web/sites/default/files/.ht.sqlite

## Observação:

Os módulos, temas e bibliotecas realmente requeridos, isto é, que por default são instalados e configurados no site modelo entregue aos usuários estão no profile fflch,
disponível em [https://github.com/fflch/drupal-profile-fflch](https://github.com/fflch/drupal-profile-fflch). Neste repositório estão apenas os módulos, temas e 
bibliotecas usados em apenas alguns sites.

## Exemplos de instalação de novos módulos:

    cd drupal8
    composer require drupal/webform:5.1
    composer require drupal/smtp:1.0-beta4

## libraries são instaladas usando assest-packgist:

Consulte o nome da biblioteca em https://asset-packagist.org e
depois instale:

    composer require npm-asset/datetimepicker:0.1.38

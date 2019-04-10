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
    ./vendor/bin/drupal serve

Caso queira forçar que as configurações voltem aos valores defaults:

    cd drupal8
    ./vendor/bin/drush cim --partial --source='profiles/contrib/drupal-profile-fflch/config/mandatory'

Se quiser apagar o banco para fazer uma instalação zerada:

    cd drupal8
    rm web/sites/default/files/.ht.sqlite

# Exemplos de instalação de novos módulos:

    cd drupal8
    composer require drupal/webform:5.1
    composer require drupal/smtp:1.0-beta4

# Bibliotecas de frontend são instaladas usando assest-packgist

Consulte o nome da biblioteca em https://asset-packagist.org e
depois instale:

    composer require npm-asset/datetimepicker:0.1.38

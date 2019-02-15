# Procedimentos de deploy local:

    git clone git@github.com:SEU-USERNAME/drupal8.git
    cd drupal8
    composer install
    cd web
    php -S 0.0.0.0:8888

# Instalação de novo módulo:

    cd drupal8
    composer require drupal/smtp

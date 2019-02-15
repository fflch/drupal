# Procedimentos de deploy local:

    git clone git@github.com:SEU-USERNAME/drupal8.git
    cd drupal8
    composer install
    cd web
    php -S 0.0.0.0:8888

# Exemplos de instalação de novos módulos:

    cd drupal8
    composer require drupal/webform:5.1
    composer require drupal/smtp:1.0-beta4

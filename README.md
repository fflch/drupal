# Procedimentos de deploy local

Download e instalação de dependências:

    git clone git@github.com:SEU-USERNAME/drupal8.git
    cd drupal8
    composer install

Servidor http básico para testes:

    cd web
    php -S 0.0.0.0:8888

Subir as configurações defaults da FFLCH:

    cd drupal8
    ./vendor/bin/drush cim --partial --yes

# Exemplos de instalação de novos módulos:

    cd drupal8
    composer require drupal/webform:5.1
    composer require drupal/smtp:1.0-beta4

# Bibliotecas de frontend são instaladas usando assest-packgist

Consulte o nome da biblioteca em https://asset-packagist.org e
depois instale:

    composer require npm-asset/datetimepicker:0.1.38

# Sempre quando habilitar ou desabilitar um módulo atualizar:

 - core.extension.yml

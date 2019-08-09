# Profile Drupal usado na FFLCH

## Adicionando novas configurações

Há dois tipos de configurações: instalação e sincronização.
As configurações de instalação são carregadas assim que o site é criado
e estão definidas em arquivos *.yml* no diretório *fflchprofile/config/install*.

As configurações de sinconização são rodadas sempre no cron e estão
em *modules/fflch_configs/config/mandatory*.

Passos para fazer modificações no site modelo:

 - Identificar os arquivos yml que executam a modificação
 - Salvar e commitar esses arquivos na pasta *modules/fflch_configs/config/mandatory*

 - Se quiser testar as configurações antes de mandar para a produção, coloque-a em um pasta,
por exemplo, /tmp/novas/.yml e rode:

    ./vendor/bin/drush cim --partial --source='/tmp/novas'

## Configurações:

### Editor de texto:

 - Somente o full_html está disponível
 - Botão com atríbutos do link usando módulo editor_advanced_link
 - Botão de arquivo usando o módulo editor_file
 - Tamanho e tipo de fonte usando editor_font

### Segurança

 - somente administradores podem criar novas contas
 - role fflch pode alterar nome do site

### Módulos disponíveis na role fflch:

 - Google Analytics
 - Assets

### Formato de datas disponíveis:

 - dia/mes/ano: d/m/Y
 - extenso: l, j \d\e F \d\e Y

### Gestão de conteúdo

 - página básica (com url baseada no título)
 - clone dos nodes
 - conditional fields
 - webform
 - blocos
 - menus
 - views

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

Plugins ckeditor:

    cd web/libraries
    ln -s ckeditor/plugins/* .

Instalação em pt-br usando o profile fflch com *sqlite*:

    ./vendor/bin/drupal site:install fflchprofile --db-type="sqlite" \
           --site-name="tests" --site-mail="admin@example.com" \
           --account-name="admin" --account-mail="admin@example.com" --account-pass="admin" \
           --no-interaction

Instalação em pt-br usando o profile fflch com *mysql*:

    ./vendor/bin/drupal site:install fflchprofile --db-type="mysql" \
           --db-port="3306" --db-user="master" --db-pass="master"   \
           --db-host="127.0.0.1" --db-name="drupal8site" \
           --site-name="tests" --site-mail="admin@example.com" \
           --account-name="admin" --account-mail="admin@example.com" --account-pass="admin" \
           --no-interaction

Servidor http básico:

    cd drupal8
    ./vendor/bin/drupal serve -vvv

Caso queira escolher ip e porta:

    ./vendor/bin/drupal server 0.0.0.0:8000 -vvv

Se quiser apagar o banco para fazer uma instalação zerada:

    # mysql
    ./vendor/bin/drupal database:drop

    # sqlite
    rm web/sites/default/files/.ht.sqlite*

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

 ## Configurações

 As vezes, novas configurações são incorporadas ao site modelo, para aplicar essa
 nova configuração pode-se fazer:

     drush @cjc.fflch.usp.br config-set aegan.settings slideshow_display '0' --yes

 Ou mesmo comando para todos sites na pasta sites:

     for i in $(ls|grep fflch); do drush @$i config-set aegan.settings slideshow_display '0' --yes ;done

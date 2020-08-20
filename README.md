## Drupal FFLCH

Plataforma Drupal usada nos sites da FFLCH. Os módulos e bibliotecas
estão em composer.json. Principais diretórios:

 - web/profiles/contrib/fflchprofile: profile com módulos e configurações customizações
 - web/modules/custom: módulos especifícos de cada site
 - web/themes/contrib/aegan-subtheme: tema default

## deploy em um ambiente dev:

Biblioteca do php:

    apt-get install php php-common php-cli php-gd php-curl php-xml php-mbstring php-zip php-sybase

Bancos de dados:

    apt-get install mariadb-server php-mysql sqlite3 php-sqlite3

Instalação do composer:

    curl -s https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer

Download e instalação das dependências:

    git clone git@github.com:SEU-USERNAME/drupal.git
    cd drupal
    composer install

Plugins ckeditor:

    cd web/libraries
    ln -s ckeditor/plugins/colorbutton .
    ln -s ckeditor/plugins/panelbutton .

Instalação em pt-br usando o profile fflch com *sqlite*:

    ./vendor/bin/drupal site:install fflchprofile --db-type="sqlite" \
           --site-name="fflch" --site-mail="admin@example.com" \
           --account-name="fflch" --account-mail="admin@example.com" --account-pass="fflch" \
           --no-interaction

Instalação em pt-br usando o profile fflch com *mysql*:

    ./vendor/bin/drupal site:install fflchprofile --db-type="mysql" \
           --db-port="3306" --db-user="fflch" --db-pass="fflch"   \
           --db-host="127.0.0.1" --db-name="fflch" \
           --site-name="fflch" --site-mail="admin@example.com" \
           --account-name="fflch" --account-mail="admin@example.com" --account-pass="fflch" \
           --no-interaction

Servidor http básico:

    cd drupal
    ./vendor/bin/drupal serve -vvv

Caso queira escolher ip e porta:

    ./vendor/bin/drupal serve 0.0.0.0:8000 -vvv

Criando nodes aleatórios:

    ./vendor/bin/drupal create:nodes

Deletando todos nodes:

    ./vendor/bin/drupal entity:delete node --all

Se quiser apagar o banco para fazer uma instalação zerada:

    # mysql
    ./vendor/bin/drupal database:drop

    # sqlite
    rm web/sites/default/files/.ht.sqlite*

## Adicionando temas, módulos e bibliotecas

Exemplos de instalação de novos módulos:

    cd drupal
    composer require drupal/webform:5.1
    composer require drupal/smtp:1.0-beta4

Libraries são instaladas usando assest-packgist, assim,
consulte o nome da biblioteca em https://asset-packagist.org e
depois instale desta forma:

    composer require npm-asset/datetimepicker:0.1.38

Verificando atualizações para os módulos/temas/biliotecas:

    composer outdated -D

Módulos que estão em composer.json para avaliar:

    composer show -D | tr -s ' ' | cut -d' ' -f1| grep ^drupal | cut -d'/' -f2

## Configurações

As vezes, novas configurações são incorporadas ao site modelo, para aplicar essa
nova configuração pode-se fazer:

    drush @cjc.fflch.usp.br config-set aegan.settings slideshow_display '0' --yes

O mesmo comando para todos sites na pasta sites:

    for i in $(ls|grep fflch); do drush @$i config-set aegan.settings slideshow_display '0' --yes ;done

Situação contrária: configurações que precisaram ser removidas,
algumas recorrentes:

    drush @cea.fflch.usp.br config-delete languageicons.settings
    drush @cea.fflch.usp.br config-delete captcha.captcha_point.user_pass
    drush @cea.fflch.usp.br config-delete captcha.settings
    drush @cea.fflch.usp.br config-delete captcha.captcha_point.user_login_form

## Adicionando novas configurações no fflchprofile

Há dois tipos de configurações:

 - instalação: aplicada somente na criação do site
 - sincronização: aplicadas a cada rodada do cron

As configurações de *instalação* estão definidas em arquivos
*.yml* no diretório *fflchprofile/config/install*.

As configurações de *sincronização* estão
em *fflchprofile/modules/fflch_configs/config/mandatory*.

Passos para fazer modificações:

- Identificar os arquivos *.yml* que executam a modificação
- Salvar e commitar esses arquivos na pasta *modules/fflch_configs/config/mandatory* ou *fflchprofile/config/install


Dica para capturar os arquivos yml que estão relacionados as configurações:

    ./vendor/bin/drush config-export --destination="~/antes"

Fazer mudanças na interface do site e exportar nova configuração:

    ./vendor/bin/drush config-export --destination="~/depois"

Vejam os arquivos alterados:

    diff -qr ~/antes/ ~/depois

Suponha que teve alteração em editor.editor.full_html.yml:

    vimdiff ~/antes/editor.editor.full_html.yml ~/depois/editor.editor.full_html.yml

Usando o meld para fazer as comparações:

    sudo apt install meld
    meld ~/antes ~/depois

## Problemas conhecidos, patches e workarounds

### quando o sitename e slogan ficam inalteráveis

Essa correção dever ser feita no ambiente dev e depois
transposta para produção. Aplicar o patch disponínel
https://www.drupal.org/project/drupal/issues/3011276#comment-13228934
e depois rodar:

    ./vendor/bin/drush config-get language.pt-br:system.site
    ./vendor/bin/drush config-delete language.pt-br:system.site slogan
    ./vendor/bin/drush config-delete language.pt-br:system.site name
    ./vendor/bin/drush config-delete language.pt-br:system.site page.front home-pt-br
    
### Patch no webform para exportar pdfs do tipo attachments (até atualizarmos para 6.x):

- https://www.drupal.org/project/webform/issues/3165998#comment-13794557
- https://www.drupal.org/files/issues/2020-08-19/3165998-10.patch


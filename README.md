## Drupal FFLCH

Plataforma Drupal usada nos sites da FFLCH. Os módulos e bibliotecas
estão em composer.json. Principais diretórios:

 - web/profiles/contrib/fflchprofile: profile com módulos e configurações customizações

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

Instalação usando o profile fflch com *sqlite*:

    ./vendor/bin/drush site-install fflchprofile \
        --db-url=sqlite://sites/default/files/.ht.sqlite \
        --site-name="fflch" \
        --site-mail="fflch@localhost" \
        --account-name="fflch" \
        --account-pass="fflch" \
        --account-mail="fflch@localhost" --yes

Instalação usando o profile fflch com *mysql*:

    ./vendor/bin/drush site-install fflchprofile \
        --db-url=mysql://admin:admin@localhost/drupal \
        --site-name="admin" \
        --site-mail="admin@localhost" \
        --account-name="admin" \
        --account-pass="admin" \
        --account-mail="admin@localhost" --yes

Instalação usando o profile fflch para o site sti.fflch.usp.br com *mysql*:

    ./vendor/bin/drush site-install fflchprofile \
        --sites-subdir=sti.fflch.usp.br \
        --db-url=mysql://admin:admin@localhost/sti \
        --site-name="admin" \
        --site-mail="admin@localhost" \
        --account-name="admin" \
        --account-pass="admin" \
        --account-mail="admin@localhost" --yes

Servidor http básico (usuário: fflch e senha: admin):

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

Verificando se há atualizações para os módulos/temas/biliotecas:

    composer outdated -D

Lembre-se que ao alterar a versão de um módulo deve-se verificar se há 
patches aplicados no mesmo na seção extra.patches do composer.json.

Módulos que estão em composer.json para avaliar:

    composer show -D | tr -s ' ' | cut -d' ' -f1| grep ^drupal | cut -d'/' -f2
    
Removendo módulo:
    
- Remover do /web/profiles/contrib/fflchprofile/modules/fflch_configs/src/installed.txt caso lá ele esteja
- Remover do composer.json
- "A quente" remover da plataforma que está no ar em web/profiles/contrib/fflchprofile/modules/fflch_configs/src/installed.txt para ele não ser instalado novamente na rodada do cron
- com drush pm-uninstall desabilitar o módulos de todos sites que estão no ar
- subir nova plataforma já sem o módulo e migrar os sites

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

Modelo de como acrescentar patches no composer.json:

    "extra": {
      "patches": {
            "drupal/editor_file": {
                "3057895 - Uploaded files are not permanent":
                    "https://www.drupal.org/files/issues/2019-05-29/file-upload-marked-permanent-3057895-2.patch"
            }
        }
    }

## Problemas conhecidos e workarounds

### quando o sitename e slogan ficam inalteráveis

Essa correção dever ser feita no ambiente dev e depois
transposta para produção. Aplicar o patch disponínel
https://www.drupal.org/project/drupal/issues/3011276#comment-13228934
e depois rodar:

    ./vendor/bin/drush config-get language.pt-br:system.site
    ./vendor/bin/drush config-delete language.pt-br:system.site slogan
    ./vendor/bin/drush config-delete language.pt-br:system.site name
    ./vendor/bin/drush config-delete language.pt-br:system.site page.front home-pt-br

Para subir um dump na máquina local, algumas configurações devem ser deletadas:

    ./vendor/bin/drush config-delete system.file path
    
#### campos que não podem ser traduzidos

sites migrados do d7 contém um problema de campos não poderem ser 
traduzidos pois o langcode está com und (undefinided). Para corrigir:

    drush @filosofia.fflch.usp.br config-set  field.storage.node.field_banca langcode pt-br --yes

## Equipe

- @thiagogomesverissimo
- @kevinlf-usp
- Augusto César Freire Santiago
- @nelimaximino
- Isaac R. L. Martins
- @annavalim


# Primeira rodada de atualização - core para 9.0.0

- remover módulo media_entity: ./vendor/bin/drush pm-uninstall media_entity media_entity_slideshow

- remover módulo form_placeholder: ./vendor/bin/drush pm-uninstall form_placeholder --> Já estava desinstalado

- remover módulo cpf: ./vendor/bin/drush pm-uninstall cpf --> Já estava desinstalado
    - O módulo webform_cpf requer drupal/cpf, então tive que tirar também

- remover módulo term_reference_tree: ./vendor/bin/drush pm-uninstall term_reference_tree
    Funciona ^9.1, mas não tem nenhuma release que funcione 9.0

Depois que o "composer update" funcionar:

    ./vendor/bin/drupal updb
    ./vendor/bin/drush entup

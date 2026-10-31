# Drupal FFLCH

Plataforma Drupal usada nos sites da FFLCH.

## deploy em um ambiente dev:

Construindo a imagem e subindo o ecosistema com o banco de dados:

    docker compose up --build
    docker exec -it drupalfflch composer install
    docker exec -it drupalfflch ./vendor/bin/drush site-install fflchprofile \
        --db-url=mysql://drupalfflch:drupalfflch@mariadb/drupalfflch \
        --site-name="fflch" \
        --site-mail="fflch@localhost" \
        --account-name="fflch" \
        --account-pass="fflch" \
        --account-mail="fflch@localhost" --yes

Trocar senha:

    docker exec -it drupalfflch ./vendor/bin/drush user:password fflch "fflch"

Acessar http://127.0.0.1:8000 com usuário: fflch e senha: fflch

## Adicionando temas, módulos e bibliotecas

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

# Primeira rodada de atualização - core para 9.0.0

No servidor antigo já foram removidos os seguintes módulos:

Não ativados, remoção direta na produção:

    cd web/modules/contrib/
    rm -rf media_entity feeds_youtube

O módulo theme_permission vamos desativar, mas quando estivermos no drupal 10 podemos ativá-lo novamente.

Os módulos cpf, libraries e form_placeholder serão desativados e removidos

    for i in `ls|grep fflch`; do drush @$i pm-uninstall cpf form_placeholder theme_permission libraries --yes; done

    cd web/modules/contrib/
    rm -rf cpf form_placeholder

Removendo markdown:

    for i in `ls|grep fflch`; do drush @$i cim --partial --source=/var/aegir/platforms/drupal8916a/web/profiles/contrib/fflchprofile/modules/fflch_configs/config/mandatory --yes; done

    for i in `ls|grep fflch`; do drush @$i pm-uninstall ckeditor_markdown markdown --yes; done
    for i in `ls|grep fflch`; do drush @$i config:set system.theme default fflch --yes; done

    for i in `ls|grep fflch`; do drush @$i config:set languageicons.settings path 'modules/contrib/languageicons/flags/*.png' -y; done

Apagar configurações órfãs nos sites com probelma:  

    site='SITE.fflch.usp.br'
    for j in `drush @$site cim --partial --source=/var/aegir/platforms/drupal8916a/web/profiles/contrib/fflchprofile/modules/fflch_configs/config/mandatory --yes 2>&1 | paste -sd ' '| grep -oP 'Configuration <em[^>]*>\K[^<]+'`; do
      drush @$site config:set $j chave valor --yes
      drush @$site config:delete $j --yes
    done

Pós removoção da configurações problemáticas:

    drush @ppgh.fflch.usp.br config:set system.theme default fflch --yes
    drush @ppgh.fflch.usp.br config:set languageicons.settings path 'modules/contrib/languageicons/flags/*.png' -y

Considerar usar:

    drush webform:repair

Habilitar sanitização?

drush config:set file.settings filename_sanitization.replace_whitespace true -y
drush config:set file.settings filename_sanitization.lowercase true -y



#  S3

Inicializa S3:

    NODE_ID=$(docker exec drupalfflch_garage /garage status | grep -E "^[0-9a-f]" | awk '{print $1}')
    docker exec drupalfflch_garage /garage layout assign $NODE_ID -c 10G -z zone1
    docker exec drupalfflch_garage /garage layout apply --version 1

key:

    docker exec drupalfflch_garage /garage key create drupal

cria bucket e expoe na porta 3902:

    docker exec drupalfflch_garage /garage bucket create drupal
    docker exec drupalfflch_garage /garage bucket website --allow drupal

Permissões:

    docker exec drupalfflch_garage /garage bucket allow --read --write drupal --key drupal

Key ID: GKb718b6060b294443377e41ee
Secret key: 02a250b5b0bedf5408717334f953aea86c68b701209562d551734423f810c793


docker exec -it drupalfflch composer require 'drupal/s3fs:^3.0'
docker exec -it drupalfflch ./vendor/bin/drush en s3fs --yes
docker exec -it drupalfflch vendor/bin/drush s3fs:refresh-cache


# Ajusta o proprietário para o usuário padrão do servidor web
docker exec -it drupalfflch chown -R www-data:www-data /var/www/html/web/sites/default/files /tmp

# Libera permissão de leitura e escrita
docker exec -it drupalfflch chmod -R 775 /var/www/html/web/sites/default/files /tmp


docker exec -it drupalfflch vendor/bin/drush config:set s3fs.settings use_s3_for_public true -y
docker exec -it drupalfflch vendor/bin/drush config:set s3fs.settings use_s3_for_private true -y
docker exec -it drupalfflch vendor/bin/drush config:set system.file default_scheme s3fs -y

docker exec -it drupalfflch vendor/bin/drush s3fs:copy-local --scheme=all --yes
docker exec -it drupalfflch vendor/bin/drush s3fs-refresh-cache

Exemplo de  web/sites/default/settings.php:


// --- Configuração do S3 File System (s3fs) com Prefixos (dev) ---

// 1. Credenciais
$settings['s3fs.access_key'] = 'GKb718b6060b294443377e41ee';
$settings['s3fs.secret_key'] = '02a250b5b0bedf5408717334f953aea86c68b701209562d551734423f810c793';

// Fallback no objeto de configuração do s3fs
$config['s3fs.settings']['key'] = 'GKb718b6060b294443377e41ee';
$config['s3fs.settings']['secret'] = '02a250b5b0bedf5408717334f953aea86c68b701209562d551734423f810c793';

// 2. Bucket e Região
$config['s3fs.settings']['bucket'] = 'drupal';
$config['s3fs.settings']['region'] = 'garage';
$config['s3fs.settings']['use_customlocation'] = TRUE;
$config['s3fs.settings']['customlocation'] = 'garage';

// 3. Pastas / Prefixos
$config['s3fs.settings']['public_folder'] = 'public';
$config['s3fs.settings']['private_folder'] = 'private';

// 4. Parâmetros de Protocolo e Endpoint para Garage S3
$config['s3fs.settings']['use_customhost'] = TRUE;
$config['s3fs.settings']['hostname'] = 'garage:3900';
$config['s3fs.settings']['use_path_style_endpoint'] = TRUE;
$config['s3fs.settings']['use_https'] = FALSE; // HTTP para dev local

// 5. Ajustes de Compatibilidade e Performance
$config['s3fs.settings']['ignore_s3_check'] = TRUE;
$config['s3fs.settings']['use_versioning'] = FALSE;
$config['s3fs.settings']['disable_version_sync'] = TRUE;
$config['s3fs.settings']['presigned_urls'] = FALSE; // Drupal faz a entrega dos privados em dev
$config['s3fs.settings']['no_rewrite_cssjs'] = TRUE;

// 6. Redirecionar cache de traduções para o disco local (evita lentidão)
$config['locale.settings']['translation']['path'] = '/tmp/languages';
$config['system.file']['path']['temporary'] = '/tmp';

// 7. Domínio público das imagens (Porta 3900 para dev)
$config['s3fs.settings']['domain'] = '127.0.0.1:3900/drupal';

// 8. Ativar a substituição dos esquemas nativos
$config['s3fs.settings']['use_s3_for_public'] = TRUE;
$config['s3fs.settings']['use_s3_for_private'] = TRUE;
$settings['s3fs.use_s3_for_public'] = TRUE;
$settings['s3fs.use_s3_for_private'] = TRUE;

// 9. Instruct s3fs to not request public-read ACLs on PutObject
$settings['s3fs.upload_as_private'] = TRUE;
$config['s3fs.settings']['upload_as_private'] = TRUE;
$settings['file_private_path'] = 'sites/default/files/private';


# parei aqui:

docker exec -it drupalfflch_garage /garage bucket alias drupal localhost -> não entendi direito, mas funcionou

acessa arquivo: http://localhost:3902/public/arquivo.pdf






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
        --account-mail="fflch@localhost" --yes \
        install_configure_form.enable_update_status_module=NULL

Trocar senha:

    docker exec -it drupalfflch ./vendor/bin/drush user:password fflch "fflch"
    docker exec -it drupalfflch ./vendor/bin/drush cr
    docker exec -it drupalfflch ./vendor/bin/drush cron

Acessar http://127.0.0.1:8000 com usuário: fflch e senha: fflch

## Adicionando temas, módulos e bibliotecas

Verificando se há atualizações para os módulos/temas/biliotecas:

    composer outdated -D

Lembre-se que ao alterar a versão de um módulo deve-se verificar se há 
patches aplicados no mesmo na seção extra.patches do composer.json.

Módulos que estão em composer.json para avaliar:

    composer show -D | tr -s ' ' | cut -d' ' -f1| grep ^drupal | cut -d'/' -f2
    
Removendo módulo (detalhes em [Instalação e remoção de módulos](#instalação-e-remoção-de-módulos)):

1. Release N: tirar o módulo do `installed.txt` e colocá-lo no `uninstalled.txt`
   (ambos em `web/profiles/contrib/fflchprofile/modules/fflch_configs/src/`),
   mantendo-o no composer.json. O cron de cada site desinstala o módulo.
2. Conferir nos logs (canal `fflch_configs`) que todos os sites desinstalaram.
3. Release N+1: remover o módulo do composer.json (e os patches dele em
   extra.patches). O nome pode continuar no `uninstalled.txt`.

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

## fflchprofile

Perfil de instalação usado por todos os sites da FFLCH. Fica versionado neste
repositório em `web/profiles/contrib/fflchprofile` (é o único conteúdo de `web/`
que está no git). Ele faz duas coisas:

- **na criação do site** (`drush site-install fflchprofile`): instala módulos,
  temas e configurações iniciais e cria uma página inicial de exemplo;
- **em todo site já existente, a cada cron**: o módulo `fflch_configs` aplica
  as configurações obrigatórias e garante quais módulos estão ou não instalados.
  É assim que uma mudança chega às centenas de sites sem rodar comandos em cada um.

### Estrutura

    fflchprofile/
    ├── fflchprofile.info.yml      módulos do core e temas (fflch, seven) instalados com o perfil
    ├── fflchprofile.install       hook_install: ajustes iniciais (ver abaixo)
    ├── config/install/            configurações aplicadas só na criação do site
    └── modules/
        ├── fflch_configs/         configurações e módulos obrigatórios (cron)
        │   ├── config/mandatory/partial/
        │   ├── config/mandatory/full/
        │   └── src/
        │       ├── Configs.php
        │       ├── installed.txt       módulos que devem estar instalados
        │       ├── uninstalled.txt     módulos que não podem estar instalados
        │       └── centros.txt         estrutura hierárquica usada pelo boleto
        ├── fflch_fakecontent/     conteúdo de exemplo da criação do site
        └── loginbytoken/          login via token de um serviço externo

### O que acontece na criação do site

1. O Drupal instala os módulos do core listados em `fflchprofile.info.yml`, os
   temas `fflch` (padrão) e `seven` (administração) e aplica `config/install/`.
2. `fflchprofile_install()`:
   - exige aprovação do administrador para novos cadastros;
   - dá o papel `administrator` ao usuário 1;
   - usa o tema de administração na edição de conteúdo;
   - instala `fflch_configs` e `fflch_fakecontent`;
   - desinstala o módulo `update`.
3. Ao ser instalado, o `fflch_configs` roda a sincronização completa (ver
   abaixo) e cria os idiomas `en` e `pt-br`, com `pt-br` como padrão e sem
   prefixo na URL. Os idiomas só são criados nesse momento, não no cron.
4. O `fflch_fakecontent` cria a página inicial (node/1, em pt-br com tradução
   em inglês) a partir de `default_content/frontpage_*.html` e o item "Home" no
   menu principal. Ao desinstalá-lo, o conteúdo criado é apagado.

### Sincronização a cada cron (fflch_configs)

A cada cron (exceto durante um `config:import`), `Configs::doConfig()` executa,
nesta ordem:

1. **Módulos**: desinstala os do `uninstalled.txt` e instala os do
   `installed.txt` que estiverem faltando (ver próxima seção).
2. **Configurações parciais** (`config/mandatory/partial/`): só as chaves
   presentes no YAML são gravadas; o resto da configuração de cada site fica
   como está.
3. **Configurações completas** (`config/mandatory/full/`): o objeto inteiro é
   substituído pelo YAML, mantendo apenas o `uuid` e o `_core` de cada site.

   Nos passos 2 e 3, um YAML só é aplicado se o dono da configuração estiver
   instalado (o prefixo do nome: `captcha` em `captcha.settings`; `core` vale
   sempre) e também as dependências declaradas nele (`dependencies.module`,
   `.theme` e `.config`). Se o **dono** não estiver instalado e a
   configuração existir no site, ela é apagada: é órfã e impediria a
   instalação do módulo depois (`PreExistingConfigException`).
4. **Usuário 1**: nome `fflch` e idioma do site; a senha vem de
   `/.tokens/user1.txt`, se o arquivo existir.
5. **SMTP**: senha de `/.tokens/noreply.txt`, se existir.
6. **Boleto** (`webform_boleto_usp`): token de `/.tokens/boleto.txt`, se
   existir, e estrutura hierárquica de `src/centros.txt`.

Os arquivos de `/.tokens/` ficam no servidor, fora do repositório.

### Instalação e remoção de módulos

Os dois arquivos ficam em `modules/fflch_configs/src/` e têm um módulo por
linha; linhas iniciadas por `#` são comentários.

**`installed.txt`**: módulos que todo site deve ter. O cron instala os que
estiverem faltando, inclusive se alguém desinstalar um deles manualmente:

- um por vez, para que a falha de um não impeça os outros;
- se a instalação for bloqueada por configurações do módulo que já existem
  no site (`PreExistingConfigException`), elas são apagadas e a instalação é
  refeita;
- um módulo que depende (direta ou indiretamente) de algo do
  `uninstalled.txt` não é instalado e gera um aviso no log, pois traria de
  volta o módulo removido.

**`uninstalled.txt`**: módulos que nenhum site pode ter. Tem prioridade sobre o
`installed.txt`. Para cada módulo da lista, o cron:

1. ignora o módulo se ele não está instalado;
2. ignora o módulo se ele está registrado no site mas o código já saiu do
   codebase;
3. desinstala o módulo junto com os módulos que dependem dele;
4. se o Drupal bloquear a desinstalação por haver conteúdo do módulo,
   **apaga esse conteúdo** e tenta de novo:
   - as entidades de conteúdo dos tipos que o módulo fornece (se a exclusão
     pela API falhar, esvazia as tabelas delas);
   - os campos cujo tipo o módulo fornece, com os dados;
5. se ainda assim falhar, registra um erro no log.

Tudo vai para o log do Drupal no canal `fflch_configs`
(`drush watchdog:show --type=fflch_configs`).

> **Atenção:** colocar um módulo no `uninstalled.txt` apaga, em todos os sites,
> os dados que dependem dele (ex.: feeds cadastrados, comentários).

As permissões dos papéis também ficam em `config/mandatory/partial/`
(`user.role.*.yml`). Ao remover um módulo, tire as permissões dele desses
arquivos; se ficarem, o cron as recoloca no papel.

### Partial ou full?

Use **partial** quando só algumas chaves precisam ser iguais em todos os sites
e o resto pode variar (ex.: permissões de um papel, algumas opções de um
módulo). Use **full** quando o objeto inteiro deve ser idêntico em todos os
sites (ex.: a barra do editor de texto `full_html`).

Cuidados:

- Não coloque `uuid` nem `_core` nos YAMLs: são próprios de cada site.
- No partial, listas são gravadas item a item pelo índice (`permissions.0`,
  `permissions.1`, ...). Remover um item do meio desloca os seguintes, e as
  últimas posições antigas continuam no site com valores repetidos. Para
  remover algo de uma lista em todos os sites, considere usar full.
- Uma configuração removida do YAML não é apagada dos sites. Use
  `drush config:delete` (ver [Configurações](#configurações)).

### Adicionando ou alterando configurações

1. Identifique os arquivos *.yml* afetados pela mudança (dica abaixo).
2. Para valer só em sites novos, salve em `fflchprofile/config/install/`.
3. Para valer também nos sites existentes, salve em
   `modules/fflch_configs/config/mandatory/partial/` ou `.../full/`, mantendo
   no partial só as chaves que devem ser forçadas.
4. Remova `uuid` e `_core` do arquivo e faça o commit.

### loginbytoken

Permite que um serviço externo logue um usuário no site:
`/loginbytoken?codpes=NUMERO_USP&temp_token=TOKEN`. O site consulta
`<base_uri>/check/` com a chave secreta; se a resposta autorizar, o usuário é
criado (ou atualizado) com o e-mail retornado e o papel `fflch`, sem senha
local, e é logado. A chave secreta e a URL do serviço ficam em
*Configuração → Sistema → Login by Token*
(`/admin/config/system/loginbytoken`).

### Dica para descobrir os YAMLs de uma mudança

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

## Patches

Modelo de como acrescentar patches no composer.json (de uma issue do
drupal.org ou um arquivo local em `patches/`):

    "extra": {
      "patches": {
            "drupal/tamper": {
                "Define um novo filtro de parser de data no tamper":
                    "https://www.drupal.org/files/issues/2020-09-13/3018621-5.patch"
            },
            "drupal/s3fs": {
                "fflch":
                    "patches/s3fs/fflch.patch"
            }
        }
    }

Ao atualizar um módulo, confira se os patches dele ainda aplicam. Um patch
que dá conflito ou cuja correção já entrou no módulo deve ser removido do
composer.json e da pasta `patches/`.

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
docker exec -it drupalfflch vendor/bin/drush s3fs:refresh-cache








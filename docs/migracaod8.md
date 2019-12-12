# Migração do Drupal 7 para o Drupal 8

0 - Criação de super usuário no mysql:

    GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%'  IDENTIFIED BY 'admin' WITH GRANT OPTION;

1 - Escolher um site e bloquear usuários na administração do site

2 - Clonar o site modelo isolado somente para essa migração:

    mkdir ~/migrate
    cd ~/migrate
    git clone https://github.com/fflch/drupal.git [site_escolhido].fflch.usp.br
    cd [site_escolhido].fflch.usp.br
    composer install
    cd web/libraries
    ln -s ckeditor/plugins/* .
    cd ../../

### Ricardo, Thiago ou Augusto:

3 - Gerar backup no aegir e colocar na pasta /var/copia e corrigir dono/grupo. Exemplo:

    /var/copia/[site_escolhido].tar.gz
### .

4 - Baixar estrutura do site, exemplo:

    mkdir d7
    cd d7
    scp -P 47112 copia@cloud.fflch.usp.br:[site_escolhido].tar.gz .
    tar -vzxf [site_escolhido].tar.gz

5 - Supondo que você tem usuário admin com senha admin, crie os bancos:

    mysql -uadmin -padmin
    create database [site_escolhido]d7;
    create database [site_escolhido]d8;
    quit

6 - Importar o banco de dados do site em drupal 6:

    mysql -uadmin [site_escolhido]d7 -padmin < database.sql

7 - Instalar modelo do drupal 8, lembre-se de trocar --db-name:

    ./vendor/bin/drupal site:install fflchprofile --db-type="mysql" \
       --db-port="3306" --db-user="admin" --db-pass="admin"   \
       --db-host="127.0.0.1" --db-name="[site_escolhido]d8" \
       --site-name="tests" --site-mail="admin@example.com" \
       --account-name="admin" --account-mail="admin@example.com" --account-pass="admin" \
       --no-interaction

8 - Habilitar módulos para migração:

    ./vendor/bin/drush en migrate_plus migrate_tools migrate_upgrade migrate_manifest --yes

9 - Executa a importação do conteúdo:

    ./vendor/bin/drush migrate-manifest --legacy-db-url=mysql://admin:admin@localhost/[site_escolhido]d7 ../docs/d7manifest.yml

10 - Procedimentos manuais:

 - Substituir menu principal pelo primário e ativar submenus
 - Configurar o nome do site para aparecer na região do logo
 - redefinir a página inical
 - arrumar e recolocar blocos nas regiões
 - Deletar tipos de conteúdos desnecessários
 - Apagar usuários
 - recriar views


11 - gerar o dump do site migrado:

    mysqldump -uadmin [site_escolhido]d8 -padmin > [site_escolhido]_migrado.sql

12 - Enviar dump para servidor:

    scp -P47114 [site_escolhido]_migrado.sql copia@cloud.fflch.usp.br:



13 - No aegirsti migrar o site. Exemplo: d7[site_escolhido].fflch.usp.br

14 - Criar o mesmo site no aegird8. Exemplo: [site_escolhido].fflch.usp.br

### Ricardo, Thiago ou Augusto:

15 - Copiar pasta files do aegirsti para aegird8

16 - Dropar e recriar o banco de dados:

    mysql -uaegir_root -h cloud.fflch.usp.br -p
    drop database [site_escolhido]fflchuspbr;
    create database [site_escolhido]fflchuspbr;
    exit

17 - Copiar o arquivo [site_escolhido]_migrado.sql para máquina local e sobe
para produção:

    mysql -uaegir_root [site_escolhido]fflchuspbr -h cloud.fflch.usp.br -p < [site_escolhido]_migrado.sql
###

18 - cadastrar o site e os responsáveis em: sites.fflch.usp.br

19 - mandar e-mail comunicando a migração

# E-mails

# E-mail para migração direta:

Prezado(a) __fulano(a)__

A Seção Técnica de Informática e o Serviço de Comunicação Social da Faculdade de Filosofia Letras e Ciências Humanas informam por meio deste, a adequação dos sites com a nova identidade visual conforme divulgada em [1].

Você consta como responsável pelo site [site_escolhido] e, sendo assim, comunicamos que o mesmo foi migrado para nova identidade.

Observações sobre a migração:

 - O template padrão é o mesmo da FFLCH: https://www.fflch.usp.br. Com as mesmas cores, fonte, tipologia e logo da página principal, conforme está na página 31 do manual de identidade visual [2].
A nova identidade visual da Faculdade estipula que os demais sites - dos Departamentos, Centros, Núcleos, Setores Administrativos etc - devem estar alinhados com a página principal, fazendo com que o usuário(a) ao navegar por eles não pareça estar visitando a página de outra instituição, mas sim lembrem que a página destes sites fazem parte da Faculdade, de uma forma integrada.
 - Nodes, Arquivos, Blocos e Menus serão migrados;
 - Webforms e Views não serão migrados devido a uma mudança de arquitetura entre as versões 6 e 8 do Drupal. Caso necessite de auxílio, podemos agendar data para suporte;
 - O site antigo ficará no ar por 10 dias para auxiliar nos ajustes pós migração no endereço d7[site_escolhido]
 - O acesso como administrador(a) deve ser efetuado com senha única USP através do site https://sites.fflch.usp.br/ e depois na opção "Logon no site" correspondente ao seu site
 - Neste tutorial é mostrado como abrir um chamado para seu site: https://www.youtube.com/watch?v=ZbloDQ59u-s
 - Neste tutorial é mostrado como adicionar ou remover administradores: https://www.youtube.com/watch?v=Z1zUpHHeQgg

[1] https://www.fflch.usp.br/1276
[2] https://www.fflch.usp.br/sites/fflch.usp.br/files/2019-04/manual_identidade_fflch.pdf

Atenciosamente,
__nome__
STI-FFLCH







## E-mail para agendamento:

Prezado(a) __fulano(a)__

A Seção Técnica de Informática e o Serviço de Comunicação Social da Faculdade de Filosofia Letras e Ciências Humanas informam por meio deste, a adequação dos sites com a nova identidade visual conforme divulgada em [1].

Você consta como responsável pelo site __site__ e, sendo assim, gostaríamos de agendar uma reunião para planejamento da migração do mesmo para a nova identidade.

Observações sobre a migração:

 - O template padrão é o mesmo da FFLCH: https://www.fflch.usp.br. Com as mesmas cores, fonte, tipologia e logo da página principal, conforme está na página 31 do manual de identidade visual [2].
A nova identidade visual da Faculdade estipula que os demais sites - dos Departamentos, Centros, Núcleos, Setores Administrativos etc - devem estar alinhados com a página principal, fazendo com que o usuário(a) ao navegar por eles não pareça estar visitando a página de outra instituição, mas sim lembrem que a página destes sites fazem parte da Faculdade, de uma forma integrada;
 - Nodes, Arquivos, Blocos e Menus serão migrados;
 - Webforms e Views não serão migrados devido a uma mudança de arquitetura entre as versões 6 e 8 do Drupal;
 - O site antigo ficará disponível por 10 dias para auxiliar nos ajustes pós migração no endereço __endereço__;
 - O acesso como administrador(a) deve ser efetuado com senha única USP através do site https://sites.fflch.usp.br e depois na opção "Logon no site" correspondente ao seu site;

[1] https://www.fflch.usp.br/1276
[2] https://www.fflch.usp.br/sites/fflch.usp.br/files/2019-04/manual_identidade_fflch.pdf

Atenciosamente,
__nome__
STI-FFLCH









# Migração do Drupal 8 para o Drupal 8

 - Migrar site para d8 e gerar backup
 - Instalação limpa, trocar banco, files e private

Atualizar banco de dados:

    i='exemple.fflch.usp.r'
    drush @$i updb --entity-updates --yes
    drush @$i cr
    
Trocar profile:

    drush @$i en profile_switcher --yes
    drush @$i switch-profile fflchprofile --yes
    drush @$i pm-uninstall profile_switcher --yes
    drush @$i en fflch_configs loginbytoken --yes
    
Na interface:   

 - Desabilitar agregação de css e js
 - Colocar tema FFLCH aegan como padrão
 - Remover bloco do logo antigo
 - Desabilitar slideshow
 - Corrigir nome do site



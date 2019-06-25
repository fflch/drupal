# Migração do Drupal 6 para o Drupal 8

### Marisa ou Vitor:

0 - Criação de super usuário no mysql:

    GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%'  IDENTIFIED BY 'admin' WITH GRANT OPTION;

1 - Escolher um site e bloquear usuários na administração do site

2 - Clonar o site modelo isolado somente para essa migração:

    cd ~/migrate
    git clone https://github.com/fflch/drupal.git treinamento.fflch.usp.br
    cd treinamento.fflch.usp.br
    composer install

### Ricardo, Thiago ou Augusto:

3 - Gerar backup no aegir e colocar na pasta /home/copia e corrigir dono/grupo. Exemplo:

    /home/copia/treinamento.tar.gz

### Marisa ou Vitor:

4 - Baixar estrutura do site, exemplo:

    mkdir d6
    cd d6
    scp copia@aegirsti.fflch.usp.br:treinamento.tar.gz .
    tar -vzxf treinamento.tar.gz

5 - Supondo que você tem usuário admin com senha admin, crie os bancos:

    mysql -uadmin -padmin
    create database treinamentod6;
    create database treinamentod8;
    quit

6 - Importar o banco de dados do site em drupal 6:

    mysql -uadmin treinamentod6 -padmin < database.sql

7 - Instalar modelo do drupal 8, lembre-se de trocar --db-name:

    ./vendor/bin/drupal site:install fflchprofile --db-type="mysql" \
       --db-port="3306" --db-user="admin" --db-pass="admin"   \
       --db-host="127.0.0.1" --db-name="treinamentod8" \
       --site-name="tests" --site-mail="admin@example.com" \
       --account-name="admin" --account-mail="admin@example.com" --account-pass="admin" \
       --no-interaction

8 - Habilitar módulos para migração:

    ./vendor/bin/drush en migrate_plus migrate_tools migrate_upgrade migrate_manifest --yes

9 - Executa a importação do conteúdo:

    ./vendor/bin/drush migrate-manifest --legacy-db-url=mysql://admin:admin@localhost/treinamentod6 ../docs/manifestd6.yml

10 - Procedimentos manuais:

 - Substituir menu principal pelo primário e ativar submenus
 - Configurar o nome do site para aparecer na região do logo
 - redefinir a página inical
 - arrumar e recolocar blocos nas regiões
 - Deletar tipos de conteúdos desnecessários
 - Apagar usuários
 - recriar views


11 - gerar o dump do site migrado:

    mysqldump -uadmin treinamentod8 -padmin > treinamento_migrado.sql

12 - Enviar dump para servidor:

    scp treinamento_migrado.sql copia@aegirsti.fflch.usp.br:

### Ricardo, Thiago ou Augusto:

13 - No aegirsti migrar o site. Exemplo: d6treinamento.fflch.usp.br

14 - Criar o mesmo site no aegird8. Exemplo: treinamento.fflch.usp.br

15 - Copiar pasta files do aegirsti para aegird8

16 - Dropar e recriar o banco de dados:

    mysql -uaegir_root -h cloud.fflch.usp.br -p
    drop database treinamentofflchuspbr;
    create database treinamentofflchuspbr;
    exit

17 - Copiar o arquivo treinamento_migrado.sql para máquina local e sobe
para produção:

    mysql -uaegir_root treinamentofflchuspbr -h cloud.fflch.usp.br -p < treinamento_migrado.sql

### Marisa ou Vitor:

18 - cadastrar o site e os responsáveis em: sites.fflch.usp.br

19 - mandar e-mail comunicando a migração

# Migração do Drupal 8 para o Drupal 8

- Exportar configurações do site em produção
- subir dump localmente

Comandos:

    ./vendor/bin/drush updb --entity-updates
    ./vendor/bin/drush pm-uninstall webform loginbytoken

- copiar os arquivos de yaml relativos as posições dos blocos do tema usado
- remover ids dos arquivos yaml?
- find and grep nos arquivos yaml trocando o tema que está para fflch_aegan
- colocar fflch_aegan como tema default
- Aplicar os arquivos de configurações de bloco, supondo que você colocouos arquivos yaml modificados em /tmp/blocos:

    ./vendor/bin/drush cim --partial --source='/tmp/blocos'
   
Trocar profile para fflchprofile:

1 - usar esse módulo https://www.drupal.org/project/profile_switcher ?

2 - ou usar drupal shell:
    
    ./vendor/bin/drupal shell
    ??


# E-mails

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


E-mail para migração direta:

Prezado(a) __fulano(a)__

A Seção Técnica de Informática e o Serviço de Comunicação Social da Faculdade de Filosofia Letras e Ciências Humanas informam por meio deste, a adequação dos sites com a nova identidade visual conforme divulgada em [1].

Você consta como responsável pelo site __site__ e, sendo assim, comunicamos que o mesmo foi migrado para nova identidade.

Observações sobre a migração:

 - O template padrão é o mesmo da FFLCH: https://www.fflch.usp.br. Com as mesmas cores, fonte, tipologia e logo da página principal, conforme está na página 31 do manual de identidade visual [2].
A nova identidade visual da Faculdade estipula que os demais sites - dos Departamentos, Centros, Núcleos, Setores Administrativos etc - devem estar alinhados com a página principal, fazendo com que o usuário(a) ao navegar por eles não pareça estar visitando a página de outra instituição, mas sim lembrem que a página destes sites fazem parte da Faculdade, de uma forma integrada.
 - Nodes, Arquivos, Blocos e Menus serão migrados;
 - Webforms e Views não serão migrados devido a uma mudança de arquitetura entre as versões 6 e 8 do Drupal. Caso necessite de auxílio, podemos agendar data para suporte;
 - O site antigo ficará no ar por 10 dias para auxiliar nos ajustes pós migração no endereço __endereço__
 - O acesso como administrador(a) deve ser efetuado com senha única USP através do site https://sites.fflch.usp.br/ e depois na opção "Logon no site" correspondente ao seu site

[1] https://www.fflch.usp.br/1276
[2] https://www.fflch.usp.br/sites/fflch.usp.br/files/2019-04/manual_identidade_fflch.pdf

Atenciosamente,
__nome__
STI-FFLCH

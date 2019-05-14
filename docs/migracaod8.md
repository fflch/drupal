1 - Baixar estrutura do site, por exemplo, site treinamento:

    scp copia@aegirsti.fflch.usp.br:treinamento.tar.gz .
    tar -vzxf treinamento.tar.gz

2 - Criar o banco de dados do site em drupal 6 que será migrado localmente:

    sudo mysql -p
    create database treinamentod6;
    grant all privileges on treinamentod6.* to treinamentod6@localhost identified by 'treinamentod6';

3 - Criar um banco de dados para o so site em drupal 8:

    sudo mysql -p
    create database treinamentod8;
    grant all privileges on treinamentod8.* to treinamentod8@localhost identified by 'treinamentod8';
    quit

4 - Importar o banco de dados do site em drupal 6:

    mysql -u treinamentod6 treinamentod6 -ptreinamentod6 < database.sql

5 - Clonar o site modelo isolado somente para essa migração:

    git clone https://github.com/fflch/drupal8.git treinamento
    cd treinamento
    composer install

6 - Instalar modelo com o banco de dados no mysql - não pode ser sqlite, pois vamos mandar para a produção o resultado da migração. Aqui você pode fazer a instalação com comando (olhe README do drupal8) ou manualmente subindo um server local:

    ./vendor/bin/drupal serve

7 - Habilitar módulos para migração:

    ./vendor/bin/drush en migrate_plus migrate_tools migrate_upgrade migrate_manifest --yes

8 - Importar conteúdo

    ./vendor/bin/drush migrate-manifest --legacy-db-url=mysql://d6user:d6pass@localhost/drupal_6 ../docs/manifestd6.yml

9 - Procedimentos manuais:

 - Identificar responsável pelo site e entrar em contato
 - Substituir menu principal pelo primário e ativar submenus
 - Configurar o logo com o novo layout
 - redefinir a home
 - arrumar e recolocar blocos nas regiões
 - Deletar tipos de conteúdos desnecessários
 - Apagar usuários
 - recriar views ?
 - Orientar como novo webform funciona, em especial novo procedimento de "clone"

10 - observações de envio para produção:

 - migrar site para outro endereço no aegir, ex: d6dlm.fflch.usp.br
 - copiar a pasta files
 - fazer dump local e enviar para produção
 - rodar o cron para sincronizar as configurações padrões
 - Dar um prazo para o responsável checar o site em d6
 - cadastrar o site e os responsáveis em: sites.fflch.usp.br

11 - problemas encontrados

 - corrigir caminhos das imagens do bloco logo
 - desagregar assets

E-mail para agendamento:

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

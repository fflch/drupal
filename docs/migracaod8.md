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

    ./vendor/bin/drush migrate-manifest --legacy-db-url=mysql://d6user:d6pass@localhost/drupal_6 manifestd6.yml

9 - Procedimentos manuais:

 - Identificar responsável pelo site e entrar em contato
 - Substituir menu principal pelo primário e ativar submenus
 - Configurar o logo com o novo layout
 - redefinir a home
 - recolocar blocos nas regiões
 - Deletar tipos de conteúdos desnecessários
 - Apagar usuários
 - recriar views ?
 - Orientar como novo webform funciona

10 - observações de envio para produção:

 - migrar site para outro endereço no aegir, ex: d6dlm.fflch.usp.br
 - copiar a pasta files
 - Dar um prazo para o responsável checar o site em d6

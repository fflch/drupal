<?php

namespace Drupal\fflch_configs;

use Symfony\Component\Yaml\Yaml;

use Drupal\Core\Config\PreExistingConfigException;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;

use Drupal\language\Entity\ConfigurableLanguage;

use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

use Uspdev\Utils\Generic;

class Configs {

  public function doConfig($install = false){
    $this->modules();
    $this->applyPartialMandatoryConfigs();
    $this->applyFullMandatoryConfigs();
    if($install) {
        $this->idiomas();
    }
    $this->user1();
    $this->boleto();
  }

  // Só chama o installer quando há algo a fazer: install()/uninstall()
  // reescaneiam todas as extensões mesmo quando nada muda, o que pesa
  // a cada cron.
  private function modules(){
    $uninstalled = $this->readModuleList('uninstalled.txt');
    $this->uninstallModules($uninstalled);

    /* Módulos que devem estar instalados (a lista de remoção tem prioridade) */
    $installed = array_diff($this->readModuleList('installed.txt'), $uninstalled);
    $missing = array_filter($installed, function ($module) {
      return !$this->isInstalled($module);
    });
    // Instalar um módulo que depende de algo do uninstalled.txt traria esse
    // algo de volta, e o próximo cron o desinstalaria de novo, em ciclo.
    $module_list = \Drupal::service('extension.list.module');
    foreach ($missing as $key => $module) {
      if (!$module_list->exists($module)) {
        continue;
      }
      $conflicts = array_intersect(array_keys($module_list->get($module)->requires ?? []), $uninstalled);
      if ($conflicts) {
        unset($missing[$key]);
        \Drupal::logger('fflch_configs')->warning('Módulo @module não instalado: depende de @conflicts, que está em uninstalled.txt. Remova @module do installed.txt.', [
          '@module' => $module,
          '@conflicts' => implode(', ', $conflicts),
        ]);
      }
    }
    // Um por vez, para que a falha de um não impeça a instalação dos outros.
    foreach ($missing as $module) {
      // Pode ter sido instalado como dependência de um anterior.
      if (!$this->isInstalled($module)) {
        $this->installModule($module);
      }
    }
  }

  /**
   * Instala um módulo. Se a instalação for bloqueada por configurações que já
   * existem no site (PreExistingConfigException), elas são órfãs, pois o
   * módulo não está instalado: são apagadas e a instalação é refeita.
   */
  private function installModule($module) {
    $logger = \Drupal::logger('fflch_configs');
    try {
      try {
        \Drupal::service('module_installer')->install([$module], TRUE);
      }
      catch (PreExistingConfigException $e) {
        $names = $this->deleteConfigObjects($e->getConfigObjects());
        $logger->warning('Instalação de @module bloqueada por configurações pré-existentes, que foram apagadas: @names. Tentando de novo.', [
          '@module' => $module,
          '@names' => implode(', ', $names),
        ]);
        \Drupal::service('module_installer')->install([$module], TRUE);
      }
      $logger->notice('Módulo @module instalado.', ['@module' => $module]);
    }
    catch (\Throwable $e) {
      $logger->error('Não foi possível instalar o módulo @module: @message', [
        '@module' => $module,
        '@message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Apaga objetos de configuração, em qualquer coleção (ex.: traduções).
   *
   * @param array $config_objects
   *   Nomes agrupados por coleção, como em
   *   PreExistingConfigException::getConfigObjects().
   *
   * @return string[]
   *   Os nomes apagados.
   */
  private function deleteConfigObjects(array $config_objects) {
    $storage = \Drupal::service('config.storage');
    $deleted = [];
    foreach ($config_objects as $collection => $names) {
      $collection_storage = $collection === StorageInterface::DEFAULT_COLLECTION ? $storage : $storage->createCollection($collection);
      foreach ($names as $name) {
        $collection_storage->delete($name);
        $deleted[] = $collection === StorageInterface::DEFAULT_COLLECTION ? $name : "$collection:$name";
      }
    }
    \Drupal::configFactory()->reset();
    return $deleted;
  }

  /**
   * Diz se a configuração obrigatória $name deve ser aplicada no site.
   *
   * - Dono (prefixo do nome, ex.: "captcha" em captcha.settings) não
   *   instalado: não aplica e, se o objeto existir, o apaga. Ele é órfão e
   *   impediria a instalação futura do módulo.
   * - Dono instalado, mas falta alguma dependência declarada no YAML: só não
   *   aplica (a configuração do dono é legítima).
   */
  private function shouldApplyConfig($name, array $data) {
    $owner = strstr($name, '.', TRUE);
    if (!$this->isExtensionInstalled($owner)) {
      $config = \Drupal::configFactory()->getEditable($name);
      if (!$config->isNew()) {
        $config->delete();
        \Drupal::logger('fflch_configs')->notice('Configuração órfã @name apagada: @owner não está instalado.', [
          '@name' => $name,
          '@owner' => $owner,
        ]);
      }
      return FALSE;
    }

    $dependencies = $data['dependencies'] ?? [];
    foreach (array_merge($dependencies['module'] ?? [], $dependencies['theme'] ?? []) as $extension) {
      if (!$this->isExtensionInstalled($extension)) {
        return FALSE;
      }
    }
    foreach ($dependencies['config'] ?? [] as $config_name) {
      if (\Drupal::configFactory()->get($config_name)->isNew()) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Módulo, perfil ou tema instalado ("core" sempre está).
   */
  private function isExtensionInstalled($name) {
    if ($name === 'core') {
      return TRUE;
    }
    $extensions = \Drupal::config('core.extension');
    return $extensions->get("module.$name") !== NULL || $extensions->get("theme.$name") !== NULL;
  }

  /**
   * Lê uma lista de módulos (um por linha; linhas com # são comentários).
   */
  private function readModuleList($filename) {
    $path = __DIR__ . '/' . $filename;
    if (!file_exists($path)) {
      return [];
    }
    $modules = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
      $line = trim(preg_replace('/#.*/', '', $line));
      if ($line !== '') {
        $modules[] = $line;
      }
    }
    return array_values(array_unique($modules));
  }

  private function isInstalled($module) {
    return \Drupal::config('core.extension')->get("module.$module") !== NULL;
  }

  /**
   * Garante que os módulos da lista não estejam instalados.
   *
   * Cada módulo é tratado separadamente, para que a falha de um não impeça os
   * outros. Os módulos que dependem dele também são desinstalados.
   */
  private function uninstallModules(array $modules) {
    $logger = \Drupal::logger('fflch_configs');
    foreach ($modules as $module) {
      if (!$this->isInstalled($module)) {
        continue;
      }
      // Ainda registrado no site, mas o código já saiu do codebase: ignora.
      if (!\Drupal::service('extension.list.module')->exists($module)) {
        continue;
      }
      try {
        $this->forceUninstall($module);
        $logger->notice('Módulo @module desinstalado.', ['@module' => $module]);
      }
      catch (\Throwable $e) {
        $logger->error('Não foi possível desinstalar o módulo @module: @message', [
          '@module' => $module,
          '@message' => $e->getMessage(),
        ]);
      }
    }
  }

  /**
   * Desinstala o módulo; se o Drupal impedir por haver conteúdo, apaga o
   * conteúdo e tenta de novo.
   */
  private function forceUninstall($module) {
    try {
      \Drupal::service('module_installer')->uninstall([$module], TRUE);
      return;
    }
    catch (\Throwable $e) {
      // Bloqueio por conteúdo (ModuleUninstallValidatorException) ou erro do
      // próprio site, como tabela do módulo inexistente (sites migrados).
      \Drupal::logger('fflch_configs')->warning('Desinstalação de @module falhou (@message). Corrigindo tabelas, apagando o conteúdo do módulo e tentando de novo.', [
        '@module' => $module,
        '@message' => $e->getMessage(),
      ]);
    }

    // O módulo e os que dependem dele e também estão instalados.
    $modules = [$module];
    $info = \Drupal::service('extension.list.module')->get($module);
    foreach (array_keys($info->required_by ?? []) as $dependent) {
      if ($this->isInstalled($dependent)) {
        $modules[] = $dependent;
      }
    }
    $this->recreateMissingEntityTables($modules);
    $this->deleteModuleContent($modules);

    \Drupal::service('module_installer')->uninstall([$module], TRUE);
  }

  /**
   * Recria as tabelas de entidades dos módulos quando alguma estiver faltando.
   *
   * Em sites migrados, o módulo pode constar como instalado sem as tabelas
   * dele. A desinstalação consulta essas tabelas (para ver se há conteúdo) e
   * falha. As tabelas restantes do tipo de entidade são apagadas e todas são
   * recriadas vazias; a desinstalação, em seguida, apaga tudo de novo.
   */
  private function recreateMissingEntityTables(array $modules) {
    $logger = \Drupal::logger('fflch_configs');
    $entity_type_manager = \Drupal::entityTypeManager();
    $schema = \Drupal::database()->schema();

    foreach ($entity_type_manager->getDefinitions() as $entity_type_id => $definition) {
      if (!in_array($definition->getProvider(), $modules, TRUE) || !$definition instanceof ContentEntityTypeInterface) {
        continue;
      }
      try {
        $storage = $entity_type_manager->getStorage($entity_type_id);
        if (!method_exists($storage, 'getTableMapping')) {
          continue;
        }
        $tables = $storage->getTableMapping()->getTableNames();
        $missing = array_filter($tables, function ($table) use ($schema) {
          return !$schema->tableExists($table);
        });
        if (!$missing) {
          continue;
        }
        foreach (array_diff($tables, $missing) as $table) {
          $schema->dropTable($table);
        }
        \Drupal::service('entity_type.listener')->onEntityTypeCreate($definition);
        $logger->warning('Tabelas de @type ausentes (@tables); recriadas vazias para permitir a desinstalação.', [
          '@type' => $entity_type_id,
          '@tables' => implode(', ', $missing),
        ]);
      }
      catch (\Throwable $e) {
        $logger->warning('Falha ao recriar as tabelas de @type: @message', ['@type' => $entity_type_id, '@message' => $e->getMessage()]);
      }
    }
  }

  /**
   * Apaga entidades de conteúdo e campos cujos tipos são fornecidos pelos
   * módulos, que é o que impede a desinstalação.
   */
  private function deleteModuleContent(array $modules) {
    $entity_type_manager = \Drupal::entityTypeManager();
    $logger = \Drupal::logger('fflch_configs');

    // 1. Entidades de conteúdo dos tipos fornecidos pelos módulos. Vem antes
    // dos campos porque os hooks de exclusão do módulo (ex.: feeds) ainda
    // consultam as tabelas desses campos.
    foreach ($entity_type_manager->getDefinitions() as $entity_type_id => $definition) {
      if (!in_array($definition->getProvider(), $modules, TRUE) || !$definition instanceof ContentEntityTypeInterface) {
        continue;
      }
      try {
        $storage = $entity_type_manager->getStorage($entity_type_id);
        do {
          $ids = $storage->getQuery()->accessCheck(FALSE)->range(0, 100)->execute();
          if ($ids) {
            $storage->delete($storage->loadMultiple($ids));
          }
        } while ($ids);
      }
      catch (\Throwable $e) {
        // A exclusão pela API falhou (ex.: hooks do módulo quebrados). Esvazia
        // direto as tabelas da entidade: elas seriam removidas na
        // desinstalação de qualquer jeito.
        $logger->warning('Falha ao apagar entidades @type pela API (@message). Esvaziando as tabelas.', ['@type' => $entity_type_id, '@message' => $e->getMessage()]);
        try {
          $database = \Drupal::database();
          foreach ($entity_type_manager->getStorage($entity_type_id)->getTableMapping()->getTableNames() as $table) {
            if ($database->schema()->tableExists($table)) {
              $database->truncate($table)->execute();
            }
          }
          $entity_type_manager->getStorage($entity_type_id)->resetCache();
        }
        catch (\Throwable $e) {
          $logger->warning('Falha ao esvaziar as tabelas de @type: @message', ['@type' => $entity_type_id, '@message' => $e->getMessage()]);
        }
      }
    }

    // 2. Campos cujos tipos são fornecidos pelos módulos (ex.: feeds_item).
    // Obs.: loadByProperties() de entidades de configuração não aceita lista
    // de valores, por isso o filtro é feito aqui.
    $field_storages = array_filter(
      $entity_type_manager->getStorage('field_storage_config')->loadMultiple(),
      function ($field_storage) use ($modules) {
        return in_array($field_storage->getTypeProvider(), $modules, TRUE);
      }
    );
    foreach ($field_storages as $field_storage) {
      try {
        $field_storage->delete();
      }
      catch (\Throwable $e) {
        $logger->warning('Falha ao apagar o campo @field: @message', ['@field' => $field_storage->id(), '@message' => $e->getMessage()]);
      }
    }

    // 3. Os dados de campos apagados ficam pendentes até o purge, e o Drupal
    // também bloqueia a desinstalação enquanto houver dados pendentes.
    if ($field_storages) {
      field_purge_batch(10000);
    }
  }

  private function applyPartialMandatoryConfigs(){

    // Diretório com as configurações obrigatórias
    $dir = \Drupal::service('module_handler')->getModule('fflch_configs')->getPath();
    $dir = $dir . '/config/mandatory/partial/';

    // Arquivos .yml
    $file_system = \Drupal::service('file_system');
    $files = $file_system->scanDirectory($dir,'/^.*\.yml$/i');
  
    foreach ($files as $file) {
        $yml = $dir . $file->filename;
        $data = Yaml::parse(file_get_contents($yml)) ?: [];
        if (!$this->shouldApplyConfig($file->name, $data)) {
          continue;
        }
        $configs = Generic::flatten($data);
        $original_config = \Drupal::service('config.factory')->getEditable($file->name);
        foreach($configs as $name=>$config) {
            $original_config->set($name, $config);
        }
        $original_config->save();
    }
  }

  private function applyFullMandatoryConfigs() {

    // Caminho do módulo
    $module_path = \Drupal::service('module_handler')
      ->getModule('fflch_configs')
      ->getPath();

    $dir = $module_path . '/config/mandatory/full/';

    // Arquivos .yml
    $file_system = \Drupal::service('file_system');
    $files = $file_system->scanDirectory($dir,'/^.*\.yml$/i');


    foreach ($files as $file) {

      $yml = $dir . $file->filename;

      // Lê o YAML
      $data = Yaml::parse(file_get_contents($yml)) ?: [];
      if (!$this->shouldApplyConfig($file->name, $data)) {
        continue;
      }

      // Obtém config editável
      $config = \Drupal::configFactory()->getEditable($file->name);

      // Mantém o uuid e o _core de cada site: os YAMLs são compartilhados
      // entre todos os sites e não devem trazer esses valores.
      unset($data['uuid'], $data['_core']);
      if ($core = $config->get('_core')) {
        $data['_core'] = $core;
      }
      // Entidades de configuração precisam de uuid. Sites que receberam o
      // antigo "uuid: null" ganham um novo.
      $data = ['uuid' => $config->get('uuid') ?: \Drupal::service('uuid')->generate()] + $data;

      // Substitui completamente os dados
      $config->setData($data);
      $config->save();
    }
  }

  private function idiomas(){
    $langcodes = ['pt-br'];
    foreach ($langcodes as $langcode) {
      $languages = \Drupal::languageManager()->getLanguages();
      if (isset($languages[$langcode])) {
        continue;
      }
      $language = ConfigurableLanguage::createFromLangcode($langcode);
      $language->save();

    }

    // pt-br como default
    $system_site = \Drupal::service('config.factory')->getEditable('system.site');
    $system_site->set('default_langcode', 'pt-br')->save();

    // remove prefixo pt-br da url
    $language_negotiation = \Drupal::service('config.factory')->getEditable('language.negotiation');
    $language_negotiation->set('url.prefixes.pt-br', '')->save();
    \Drupal::languageManager()->reset();
  }

  // remover em breve
  // Grava a estrutura hierárquica (centros.txt). Os demais dados (token,
  // user_id, codigoUnidadeDespesa) vêm do settings.php do deploy.
  private function boleto(){
    // Sem o módulo, a config ficaria órfã e impediria a instalação dele.
    if (!$this->isExtensionInstalled('webform_boleto_usp')) {
      return;
    }
    $centros = file_get_contents(__DIR__ . '/centros.txt');
    $config = \Drupal::configFactory()->getEditable('webform_boleto_usp.settings');
    if ($config->get('estruturaHierarquica') !== $centros) {
      $config->set('estruturaHierarquica', $centros)->save();
    }
  }

  // renomeia usuário 1 para fflch e define como pt-br
  private function user1(){

    $user = \Drupal\user\Entity\User::load(1);
    $user->setUsername('fflch');

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $user->set("langcode", $language);
    $user->set("preferred_langcode", $language);
    $user->set("preferred_admin_langcode", $language);
    $user->save();
  }

}

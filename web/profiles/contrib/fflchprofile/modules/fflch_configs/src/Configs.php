<?php

namespace Drupal\fflch_configs;

use Symfony\Component\Yaml\Yaml;

use Drupal\language\Entity\ConfigurableLanguage;

use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

use Uspdev\Utils\Generic;

class Configs {

  public function doConfig($install = false){
    $this->modules();
    $this->mandatory();
    if($install) {
        $this->idiomas();
    }
    $this->user1();
    $this->smtp();
    $this->boleto();
  }

  private function modules(){
    /* Módulos que não devem estar instalados */
    $uninstalled = [ 'update', 'comment'];
    \Drupal::service('module_installer')->uninstall($uninstalled, TRUE);

    /* Módulos que devem estar instalados */
    $installed = file_get_contents(__DIR__. '/' . 'installed.txt');
    $installed = explode("\n",$installed);
    $installed = array_unique($installed);
    array_pop($installed);
    \Drupal::service('module_installer')->install($installed, TRUE);
  }

  private function mandatory(){

    // Diretório com as configurações obrigatórias
    $dir = \Drupal::service('module_handler')->getModule('fflch_configs')->getPath();
    $dir = $dir . '/config/mandatory/';

    // Arquivos .yml
    $file_system = \Drupal::service('file_system');
    $files = $file_system->scanDirectory($dir,'/^.*\.yml$/i');
  
    foreach ($files as $file) {;
        $yml = $dir . $file->filename;
        $configs = Generic::flatten(Yaml::parse(file_get_contents($yml)));
        $original_config = \Drupal::service('config.factory')->getEditable($file->name);
        foreach($configs as $name=>$config) {
            $original_config->set($name, $config);
        }
        $original_config->save();
    }
  }

  private function idiomas(){
    $langcodes = ['en','pt-br'];
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

  private function smtp(){

    $smtp_settings = \Drupal::service('config.factory')->getEditable('smtp.settings');

    $filename = '/.tokens/noreply.txt';
    if (file_exists($filename)) {
        $senha = file_get_contents($filename);
        $smtp_settings->set('smtp_password', $senha)->save();
    }
  }

  private function boleto(){

    $config = \Drupal::service('config.factory')->getEditable('webform_boleto_usp.settings');
    $centros = file_get_contents(__DIR__. '/' . 'centros.txt');

    $filename = '/.tokens/boleto.txt';
    if (file_exists($filename)) {
        $token = file_get_contents($filename);
        $token = trim( str_replace( PHP_EOL, '', $token ) );
        $config->set('user_id', 'fflch');
        $config->set('codigoUnidadeDespesa', 8);
        $config->set('estruturaHierarquica', $centros);
        $config->set('token', $token)->save();
    }
  }

  private function user1(){

    $user = \Drupal\user\Entity\User::load(1);
    $user->setUsername('fflch');

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $user->set("langcode", $language);
    $user->set("preferred_langcode", $language);
    $user->set("preferred_admin_langcode", $language);

    $filename = '/.tokens/user1.txt';
    if (file_exists($filename)) {
        $senha = file_get_contents($filename);
        $user->setPassword($senha);
    }
    $user->save();
  }

}

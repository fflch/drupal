<?php

namespace Drupal\fflch_configs;

use Symfony\Component\Yaml\Yaml;

use Drupal\language\Entity\ConfigurableLanguage;

use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

class Configs {

  public function doConfig(){
    $this->modules();
    $this->mandatory();
    $this->idiomas();
    $this->user1();
    $this->smtp();
  }

  private function modules(){

    $uninstalled = ['update'];
    $installed = [
    #### módulos core usados na FFLCH
      'language',
      'locale',
      'book',
      'config_translation',
      'content_translation',
    #### módulos contrib usados na FFLCH
      'captcha',
      'image_captcha',
      'smtp',
      'entity',
      'entity_clone',
      'asset_injector',
      'imce',
      'google_analytics',
      'pathauto',
      'webform',
      'webform_ui',
      'webform_attachment',
      'webform_scheduled_email',
      'theme_permission',
      'webform_node',
      'editor_advanced_link',
      'editor_file',
      'ckeditor_font',
      'colorbutton',
      'conditional_fields',
      'ctools',
      'collapse_text',
      'config_perms',
      'cpf',
      'languageicons',
      'loginbytoken',
      'csv_importer',
      'contact',
      'contact_storage',
      'menu_manipulator',
      'contact_emails',
    ];

    \Drupal::service('module_installer')->install($installed, TRUE);
    \Drupal::service('module_installer')->uninstall($uninstalled, TRUE);

  }

  private function mandatory(){

    // Diretório com as configurações obrigatórias
    $dir = \Drupal::service('module_handler')->getModule('fflch_configs')->getPath();
    $dir = $dir . '/config/mandatory/';

    // Arquivos .yml
    $files = file_scan_directory($dir,'/^.*\.yml$/i',[]);
    foreach ($files as $file) {
        $yml = $dir . $file->name . '.yml';
        $configs = Yaml::parse(file_get_contents($yml));
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

    $filename = '/var/aegir/.email.txt';
    if (file_exists($filename)) {
        $senha = file_get_contents($filename);
        $smtp_settings->set('smtp_password', $senha)->save();
    }
  }

  private function user1(){

    $user = \Drupal\user\Entity\User::load(1);
    $user->setUsername('fflch');

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $user->set("langcode", $language);
    $user->set("preferred_langcode", $language);
    $user->set("preferred_admin_langcode", $language);

    $filename = '/var/aegir/.user1.txt';
    if (file_exists($filename)) {
        $senha = file_get_contents($filename);
        $user->setPassword($senha);
    }
    $user->save();
  }

}

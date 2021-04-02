<?php

namespace Drupal\urbandatabrasil_userform\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'LanguagePlugin' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "language_plugin"
 * )
 */
class language extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    // Plugin logic goes here.
  $idioma = substr($value, 3);

    $array = [
      "portugues" => "^a Português",
      "espanhol" => "^a Espanhol",
      "frances" => "^a Francês",
      "ingles" => "^a Inglês",
      "italiano" => "^a Italiano",
      "alemao" => "^a Alemão",
      "outros" => "^a Outro",
    ];
    // Plugin logic goes here.
    return array_search($value,$array);
  }

}

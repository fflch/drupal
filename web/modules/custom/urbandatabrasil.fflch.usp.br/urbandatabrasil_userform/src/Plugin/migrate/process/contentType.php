<?php

namespace Drupal\urbandatabrasil_userform\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'contentType' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "content_type"
 * )
 */
class contentType extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Plugin logic goes here. producoes_periodicos_cientificos
    $array = [
      "Artigo" => "producoes_periodicos_cientificos",
      "Paper" => "producoes_periodicos_cientificos",
      "Anais" => "producoes_periodicos_cientificos",
      "Livro" => "livros",
      "Dissertação" => "trabalhos_de_conclusao",
      "Tese" => "trabalhos_de_conclusao",
      "Relatório" =>  "producoes_tecnicas",
    ];

    foreach($array as $key => $element) {
      if ($key == $value) {
        $bundle = $element;
      }
         
    }
    return $bundle;
  }

}

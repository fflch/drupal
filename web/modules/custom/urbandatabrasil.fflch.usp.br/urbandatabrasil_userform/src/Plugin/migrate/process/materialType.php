<?php

namespace Drupal\urbandatabrasil_userform\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'materialType' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "material_type"
 * )
 */
class materialType extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $field = null;
    $prop = null;
    $array = [
        "Artigo" => "artigo_periodico",
        "Paper" => "outros",
        "Anais" => "trabalho_eventos",
        "Livro" => "livro_coletanea",
        "Dissertação" => "dissertacao_mestrado",
        "Tese" => "outros",
        "Relatório" =>  "relatorio_tecnico",
      ];

    foreach($array as $key => $element) {
        if ($key == $value) {
            $field = $element;
        }
    }
    
    $fields = [
        "artigo_periodico" => "field_tipo_de_material",
        "outros" => "field_tipo_de_material",
        "trabalho_eventos" => "field_tipo_de_material",
        "livro_coletanea" => "field_tipo_de_material_livros",
        "dissertacao_mestrado" => "field_tipo_de_material_conclusao",
        "tese_doutorado" => "field_tipo_de_material_conclusao",
        "relatorio_tecnico" =>  "field_tipo_de_material_tecnicas",
      ];

      foreach($fields as $key => $element) {
        if ($key == $field && $element == $destination_property) {
            $prop = $key;
        }
      }
   
      return $prop;
    }

}
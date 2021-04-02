<?php

namespace Drupal\urbandatabrasil_userform\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'LanguagePlugin' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "year_publication"
 * )
 */
class yearPublication extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    // Plugin logic goes here.
    $year = explode('^c ', $value);
    return $year[1];
  }

}

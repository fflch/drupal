<?php

namespace Drupal\urbandatabrasil_userform\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'thematicArea' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "trans_value"
 * )
 */
class thematicArea extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $vocabulary = $this->configuration['vocabulary'];
    $term_name = null;
    $tid = null;
    $result = [];

    $map_area_tematica = [
      '^a Administração e Finanças Públicas' => 'Administração e finanças públicas',
      '^a Praticas e Processos de Planejamento Urbano' => 'Planejamento urbano',
      '^a Estrutura social urbana' => 'Estrutura social',
      '^a Estrutura Econômica' => 'Estrutura econômica e mercado de trabalho',
      '^a Espaço urbano' => 'Espaço urbano',
      '^a Violência Urbana' => 'Violência',
      '^a Processo de Urbanização' => 'Processos de urbanização',
      '^a Mobilidade urbana' => 'Mobilidade urbana',
      '^a Estrutura Urbana e Metropolitana' => 'Estrutura regional e metropolitana',
      '^a Movimentos Sociais' => 'Movimentos sociais',
      '^a Infra-estrutura, Equip.Coletivos' => 'Infraestrutura, serviços urbanos e equipamentos coletivos',
      '^a Solo Urbano' => 'Solo urbano',
      '^a Sistema Urbano' => 'Espaço urbano',
      '^a Estrutura Social Urbana' => 'Estrutura social',
      '^a Crescimento Populacional e Migração' => 'Fluxos populacionais e migrações',
      '^a Evolução Urbana' => 'Evolução urbana',
      '^a Meio Ambiente e Qualidade de Vida Urbana' => 'Meio ambiente e qualidade de vida',
      '^a Habitação' => 'Habitação',
      '^a Modo de Vida, Imaginário Social e Cotidiano/' => 'Modo de vida, imaginário social e cotidiano',
      '^a Construção Civil' => 'Construção civil',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Meio Ambiente e Qualidade de Vida Urbana' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Pobreza Urbana' => 'Modo de vida, imaginário social e cotidiano',
      '^a Pobreza Urbana' => 'Pobreza e desigualdade',
      '^a Praticas e processos de planejamento urbano' => 'Planejamento urbano',
      '^a Políticas Públicas' => 'Políticas públicas',
      '^a Experiências e Práticas Alternativas' => 'Modo de vida, imaginário social e cotidiano',
      '^a Setor Informal' => 'Setor informal/informalidades',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Habitação' => 'Modo de vida, imaginário social e cotidiano',
      '^a Poder Local' => 'Poder local e gestão urbana',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Estrutura Urbana e Metropolitana' => 'Modo de vida, imaginário social e cotidiano',
      '^a Estrutura urbana e metropolitana' => 'Estrutura regional e metropolitana',
      '^a Movimentos sociais urbanos' => 'Movimentos sociais',
      '^a Modo de Vida, Imaginário Social e Cotidiano' => 'Modo de vida, imaginário social e cotidiano',
      '^a Pobreza urbana' => 'Pobreza e desigualdade',
      '^a Violência urbana' => 'Violência',
      '^a Infância e Juventude' => 'Infância e juventude',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Poder Local' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Evolução Urbana' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Infância e Juventude' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Violência Urbana' => 'Modo de vida, imaginário social e cotidiano',
      '^a Crescimento populacional e migração' => 'Fluxos populacionais e migrações',
      '^a Infraestrutura, serviços urbanos e equipamentos coletivos' => 'Infraestrutura, serviços urbanos e equipamentos coletivos',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Estrutura Econômica' => 'Modo de vida, imaginário social e cotidiano',
      '^a Administração e finanças públicas' => 'Administração e finanças públicas',
      '^a Solo urbano' => 'Solo urbano',
      '^a Modo de vida, imaginário social e cotidiano' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Administração e Finanças Públicas' => 'Modo de vida, imaginário social e cotidiano',
      '^a ONGs e Cidadania' => 'Ongs e terceiro setor',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Novas Tecnologias e Meio Urbano' => 'Modo de vida, imaginário social e cotidiano',
      '^a Memória, patrimônio e preservação' => 'Memória, patrimônio e preservação',
      '^a Políticas públicas' => 'Políticas públicas',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Setor Informal' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Crescimento Populacional e Migração' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Estrutura Social Urbana' => 'Modo de vida, imaginário social e cotidiano',
      '^a Novas Tecnologias e Meio Urbano' => 'Novas tecnologias e meio urbano',
      '^a Infância e juventude' => 'Infância e juventude',
      '^a Evolução urbana' => 'Evolução urbana',
      '^a Estrutura econômica e mercado de trabalho' => 'Estrutura econômica e mercado de trabalho',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Movimentos Sociais' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Políticas Públicas' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Patrimônio e Preservação Histórica' => 'Modo de vida, imaginário social e cotidiano',
      '^a Processo de urbanização' => 'Processos de urbanização',
      '^a Arte e cultura' => 'Arte e estética',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Solo Urbano' => 'Modo de vida, imaginário social e cotidiano',
      '^a Novas tecnologias e meio urbano' => 'Novas tecnologias e meio urbano',
      '^a Práticas e processos de planejamento urbano' => 'Planejamento urbano',
      '^a Ideologia e Política Institucional' => 'Ideologia e política',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a ONGs e Cidadania' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Experiências e Práticas Alternativas' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Processo de Urbanização' => 'Modo de vida, imaginário social e cotidiano',
      '^a Meio ambiente e qualidade de vida urbana' => 'Meio ambiente e qualidade de vida',
      '^a Relações raciais' => 'Relações étnico-raciais',
      '^a Poder local e gestão urbana' => 'Poder local e gestão urbana',
      '^a Rituais e comemorações' => 'Religiões, rituais e comemorações',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Infra-estrutura, Equip.Coletivos' => 'Modo de vida, imaginário social e cotidiano',
      '^a Movimentos sociais' => 'Movimentos sociais',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Sistema Urbano' => 'Modo de vida, imaginário social e cotidiano',
      '^a Modo de Vida, Imaginário Social e Cotidiano/^a Praticas e Processos de Planejamento Urbano' => 'Modo de vida, imaginário social e cotidiano',
      '^a Turismo urbano' => 'Turismo e cultura de viagem',
      '^a Construção civil' => 'Construção civil',
      '^a Setor informal' => 'Setor informal/informalidades',
      '^a Mídia e comunicação' => 'Mídia e comunicação',
      '^a Ideologia e política institucional' => 'Ideologia e política',
      '^a Experiências e práticas alternativas' => 'Modo de vida, imaginário social e cotidiano',
      '^a Gênero' => 'Gênero/sexualidade',
      '^a Práticas e Processos de Planejamento Urbano' => 'Planejamento urbano',
      '^a Movimentos Sociais Urbanos' => 'Movimentos sociais',
      '^a Práticas de lazer e esporte' => 'Serviços, espaços e práticas de lazer',
      '^a Infraestrutura, Serviços Urbanos e Equipamentos Coletivos' => 'Infraestrutura, serviços urbanos e equipamentos coletivos',
      '^a Arte e Cultura' => 'Arte e estética',
      '^a Poder Local e Gestão Urbana' => 'Poder local e gestão urbana',
      '^a ONG e terceiro setor' => 'Ongs e terceiro setor',
      '^a Modo de vida, Imaginário social e cotidiano' => 'Modo de vida, imaginário social e cotidiano',
      '^a Estrutura urbana e Metropolitana' => 'Estrutura regional e metropolitana',
    ];

    foreach($map_area_tematica as $aux => $element) {
      if ($value == $aux) {
        $term_name = $element;
      }
    }
    
  if($term_name) {
    if ($tid = $this->getTidByName($term_name, $vocabulary)) {
      $term = Term::load($tid);
    }
    else {
      $term = Term::create([
        'name' => $term_name, 
        'vid'  => $vocabulary,
      ])->save();
      $tid =  $this->getTidByName($term_name, $vocabulary);
    }
    $result = ['target_id' => $tid];
  }
    return $result;
  }

  /**
   * Load term by name.
   */
  protected function getTidByName($name = NULL, $vocabulary = NULL) {
    $tid = null;
    $properties = [];
    if (!empty($name) && !empty($vocabulary)) {
      $properties['name'] = $name;
      $properties['vid'] = $vocabulary;
      $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
      $term = reset($terms);
      $tid = is_object($term) ? $term->id() : null;
    }
    return $tid;
  }

}

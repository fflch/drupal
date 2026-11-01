<?php

namespace Drupal\fflch_fakecontent;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Defines a helper class for importing default content.
 *
 * @internal
 *   This code is only for use to import Content.
 */
class InstallHelper implements ContainerInjectionInterface {

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * State.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a new InstallHelper object.
   *
   * @param \Drupal\Core\Path\AliasManagerInterface $aliasManager
   *   The path alias manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler.
   * @param \Drupal\Core\State\StateInterface $state
   *   State service.
   */
  public function __construct(AliasManagerInterface $aliasManager, EntityTypeManagerInterface $entityTypeManager, ModuleHandlerInterface $moduleHandler, StateInterface $state) {
    $this->aliasManager = $aliasManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->moduleHandler = $moduleHandler;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path_alias.manager'),
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('state')
    );
  }

  /**
   * Imports default contents.
   */
  public function importContent() {
    $this->importPages();

    // Cria um botão de exemplo no menu
    $menu_link = MenuLinkContent::create([
      'title' => "Home",
      'link' => ['uri' => "internal:/"],
      'menu_name' => 'main',
      'expanded' => TRUE,
    ]);
    $menu_link->save();

    // defina a página inicial
    $system_site = \Drupal::configFactory()->getEditable('system.site');
    $system_site->set('page.front', '/node/1')->save(TRUE);
  }

  /**
   * Imports pages.
   *
   * @return $this
   */
  protected function importPages() {

    $module_path = $this->moduleHandler->getModule('fflch_fakecontent')->getPath();

    $title = [
        'pt-br' => 'Faculdade de Filosofia, Letras e Ciências Humanas',
        'en' => 'Faculty of Philosophy, Languages and Literature, and Human Sciences',
        'es' => 'Facultad de Filosofía, Letras y Ciencias Humanas',
        'fr' => 'Faculté de Philosophie, Lettres et Sciences Humaines ',    
    ];

    // pt-br frontpage
    $file = $module_path . '/default_content/frontpage_pt.html';
    $body = file_get_contents($file);
    $uuids = [];

    // Prepare content.
    $values = [
        'type' => 'page',
        'title' => $title['pt-br'],
        'moderation_state' => 'published',
        'langcode' => 'pt-br',
        'body' => [['value' => $body, 'format' => 'full_html']],
        'uid' => 1
    ];
      
    // Create Node.
    $node = $this->entityTypeManager->getStorage('node')->create($values);   
    $node->save();

    // other languages
    $langcodes = ['en'];
    foreach ($langcodes as $langcode) {
        $file = $module_path . "/default_content/frontpage_{$langcode}.html";
        $body = file_get_contents($file);
        $values = [
            'title' => $title[$langcode],
            'moderation_state' => 'published',
            'uid' => 1,
            'body' => [['value' => $body, 'format' => 'full_html']],
        ];
        $node->addTranslation($langcode, $values)->save();
    }

    $uuids[$node->uuid()] = 'node';     
    $this->storeCreatedContentUuids($uuids);

    return $this;
  }

  /**
   * Deletes any content imported by this module.
   *
   * @return $this
   */
  public function deleteImportedContent() {
    $uuids = $this->state->get('fflch_fakecontent_uuids', []);
    $by_entity_type = array_reduce(array_keys($uuids), function ($carry, $uuid) use ($uuids) {
      $entity_type_id = $uuids[$uuid];
      $carry[$entity_type_id][] = $uuid;
      return $carry;
    }, []);
    foreach ($by_entity_type as $entity_type_id => $entity_uuids) {
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
      $entities = $storage->loadByProperties(['uuid' => $entity_uuids]);
      $storage->delete($entities);
    }
    return $this;
  }

  /**
   * Stores record of content entities created by this import.
   *
   * @param array $uuids
   *   Array of UUIDs where the key is the UUID and the value is the entity
   *   type.
   */
  protected function storeCreatedContentUuids(array $uuids) {
    $uuids = $this->state->get('fflch_fakecontent_uuids', []) + $uuids;
    $this->state->set('fflch_fakecontent_uuids', $uuids);
  }

}
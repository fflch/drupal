<?php

namespace Drupal\urbandatabrasil_userform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * Class ValidaEmailController.
 */
class ValidaEmailController extends ControllerBase {

  /**
   * Drupal\Core\Database\Connection definition.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Symfony\Component\HttpFoundation\Request;.
   *
   * @var Symfony\Component\HttpFoundation\Requestn
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database, RequestStack $request) {
    $this->database = $database;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {


    if (!empty($_POST['email'])) {
      $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
      $query =  $this->database->select('webform_submission_data', 'webform');
      $query->condition('webform.name', 'email', '=');
      $query->condition('webform.value', $email, '=');
      $query->fields('webform', ['sid']);
      $sid = $query->execute()->fetchField();
      if (!empty($sid)) {
        $webformSubmission = \drupal::entityTypeManager()->getStorage('webform_submission')->load($sid);
        $email = $webformSubmission->getData()['email'];
        $session = \Drupal::request()->getSession();
        $session->set('usuario_validado', $email);
      }
      else {
        $email = false;
      }
    }
    $response['retorno'] = $email;
    return new JsonResponse( $response );
  }

  /**
   * {@inheritdoc}
   */
  public function sessao(Request $request) {

    $email = false;
    if (!empty($_POST['email'])) {
      $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
      $session = \Drupal::request()->getSession();
      $session->set('usuario_validado', $email);
    }
    $response['retorno'] = $email;
    return new JsonResponse( $response );
  }

}

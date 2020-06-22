<?php

namespace Drupal\loginbytoken\Controller;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;

/**
 * Class LoginController.
 */
class LoginController extends ControllerBase {

  public function login(Request $request) {

    $temp_token = $request->query->get('temp_token');
    $codpes = $request->query->get('codpes');

    // Verifica se o usuário em questão tem permissão para logar
    $site = $request->server->get('HTTP_HOST');

    $filename = '/var/aegir/.secretkey.txt';
    if (file_exists($filename)) {
        # ambiente prod
        $secretkey = file_get_contents($filename);
        $base_uri = 'https://sites.fflch.usp.br/';

    } else {
        # ambiente dev
        $secretkey = '123';
        $base_uri = 'http://127.0.0.1:8000/';
    }

    $client = new Client([
        'base_uri' => $base_uri,
    ]);

    $res = $client->request('GET',"/check/",
        ['query' => ['secretkey'  => $secretkey,
                     'codpes'     => $codpes,
                     'site'       => $site,
                     'temp_token' => $temp_token,
        ]]);

    $response = json_decode($res->getBody());

    if($response[0] == true) {
      $user = user_load_by_name($codpes);
      if (empty($user)) {
        $user = \Drupal\user\Entity\User::create();
        $user->setUsername($codpes);
        $user->enforceIsNew();
      }
      $user->setEmail($response[1]);

      // Configura língua default do sistema
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $user->set("langcode", $language);
      $user->set("preferred_langcode", $language);
      $user->set("preferred_admin_langcode", $language);

      // não sei o que faz, mas se não colocarmos não cria o usuário
      $user->set("init", 'email');

      // Ativa usuário
      $user->activate();

      // role
      $user->addRole('fflch');

      // Bem, user não deve ter sem senha local...
      $user->setPassword(FALSE);

      //Save user.
      $user->save();

      // Loga usuário
      user_login_finalize($user);
      $this->messenger()->addMessage('Login efetuado com sucesso');
    } else {
        $this->messenger()->addMessage("Desculpe-nos! Você não permissão para logar nesse site. {$response[1]}");
    }
    return $this->redirect('<front>');

  }
}

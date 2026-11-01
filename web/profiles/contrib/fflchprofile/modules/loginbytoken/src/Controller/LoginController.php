<?php

namespace Drupal\loginbytoken\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LoginController.
 */
class LoginController extends ControllerBase {

  public function login(Request $request) {

    $temp_token = $request->query->get('temp_token');
    $codpes = $request->query->get('codpes');

    // Recupera as configurações salvas no Drupal (com fallback default)
    $config = $this->config('loginbytoken.settings');
    $secretkey = $config->get('secretkey') ?: 'token123';
    $base_uri = $config->get('base_uri') ?: 'https://sites.local/';

    $site = $request->server->get('HTTP_HOST');

    $client = new Client([
      'base_uri' => $base_uri,
    ]);

    $res = $client->request('GET', "/check/", [
      'query' => [
        'secretkey'  => $secretkey,
        'codpes'     => $codpes,
        'site'       => $site,
        'temp_token' => $temp_token,
      ],
    ]);

    $response = json_decode($res->getBody());

    if (!empty($response) && $response[0] == TRUE) {
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

      $user->set("init", 'email');

      // Ativa usuário
      $user->activate();

      // Role
      $user->addRole('fflch');

      // User sem senha local
      $user->setPassword(FALSE);

      // Save user.
      $user->save();

      // Loga usuário
      user_login_finalize($user);
      $this->messenger()->addMessage('Login efetuado com sucesso');
    }
    else {
      $message = isset($response[1]) ? $response[1] : '';
      $this->messenger()->addMessage("Desculpe-nos! Você não tem permissão para logar nesse site. {$message}");
    }

    return $this->redirect('<front>');

  }

}
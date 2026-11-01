<?php

namespace Drupal\loginbytoken\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configurações administrativas do módulo Login by Token.
 */
class LoginByTokenSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['loginbytoken.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'loginbytoken_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('loginbytoken.settings');

    $form['secretkey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret Key (Token)'),
      '#default_value' => $config->get('secretkey') ?? 'token123',
      '#required' => TRUE,
      '#description' => $this->t('Chave secreta utilizada para validar a requisição de login.'),
    ];

    $form['base_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URI'),
      '#default_value' => $config->get('base_uri') ?? 'https://sites.fflch.usp.br/',
      '#required' => TRUE,
      '#description' => $this->t('URL base do serviço remoto de autenticação.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('loginbytoken.settings')
      ->set('secretkey', $form_state->getValue('secretkey'))
      ->set('base_uri', $form_state->getValue('base_uri'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
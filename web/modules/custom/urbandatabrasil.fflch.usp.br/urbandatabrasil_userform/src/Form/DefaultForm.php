<?php

namespace Drupal\urbandatabrasil_userform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DefaultForm.
 */
class DefaultForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'controle-acesso';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['email'] = [
      '#type' => 'email',
      '#weight' => '0',
      '#required' => TRUE,
      '#placeholder' => 'Email',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enviar'),
    ];
    $form['#theme'] = 'urbandata_template';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }


}

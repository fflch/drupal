<?php


/**
 * @file
 * Contains \Drupal\form_overwrite\Form\NewUserLoginForm.
 */

namespace Drupal\valida_email\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Form\UserLoginForm;

/**
 * Provides a user login form.
 */
class NewUserLoginForm extends UserLoginForm {

    public function buildForm(array $form, FormStateInterface $form_state) {
        $form = parent::buildForm($form, $form_state);
        $form['email'] = [
            '#type' => 'email',
            '#default_value' => ‘’
        ];
        return $form;
    }
}
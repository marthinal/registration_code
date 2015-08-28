<?php

/**
 * @file
 * Contains \Drupal\registration_code\Proxy\RegistrationCodeProxy.
 */

namespace Drupal\registration_code\Proxy;

use Drupal\registration_code\Utility\RegistrationCodeHelper;

/**
 * Class RegistrationCodeProxy
 * @package Drupal\registration_code\Utility
 */
class RegistrationCodeProxy {
  public function registrationCodeProcess($email) {
    return RegistrationCodeHelper::registrationCodeProcess($email);
  }
}
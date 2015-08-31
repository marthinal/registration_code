<?php

/**
 * @file
 * Contains \Drupal\registration_code\Proxy\RegistrationCodeProxy.
 */

namespace Drupal\registration_code\Proxy;

use Drupal\registration_code\Utility\RegistrationCodeHelper;
use Drupal\Core\Mail\MailManagerInterface;
use Egulias\EmailValidator\EmailValidator;
use Drupal\Core\Database\Connection;

/**
 * Class RegistrationCodeProxy
 * @package Drupal\registration_code\Proxy
 */
class RegistrationCodeProxy {
  public function registerCode($email, EmailValidator $emailValidator, MailManagerInterface $emailManager, Connection $connection, $sender) {
    return RegistrationCodeHelper::registerCode($email, $emailValidator, $emailManager, $connection, $sender);
  }
}
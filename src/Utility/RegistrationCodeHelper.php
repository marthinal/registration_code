<?php

/**
 * @file
 * Contains \Drupal\registration_code\Utility\RegistrationCodeHelper.
 */

namespace Drupal\registration_code\Utility;


/**
 * Defines a class containing utility methods for generating email codes.
 */
class RegistrationCodeHelper {

  /**
   * @param $email
   */
  public static function registrationCodeProcess($email) {
    // Verify that the email is valid. Please validate the email before call this method.
    if(!\Drupal::service('email.validator')->isvalid($email)) {
      throw new \UnexpectedValueException('The email is not valid.');
    }

    // Generate Code.
    $code = self::generateCode();

    // Insert the new code into the DB.
    self::setCode($email, $code);

    // Send the code by email.
    \Drupal::service('plugin.manager.mail')->mail('registration_code', 'send_code', $email, 'en', $params = array('code' => $code), \Drupal::config('system.site')->get('mail'));

  }

  /**
   * Generates a random code that will be sent to the user by email.
   *
   * @return int
   */
  public static function generateCode() {
    return rand(10000, 100000);
  }

  /**
   * @param $email
   * @param $code
   */
  protected static function setCode($email, $code) {
    $connection = \Drupal::service('database');

    // Verify if the email exists.
    $query = $connection->select('registration_code', 'rc');
    $query->fields('rc', ['email']);
    $query->condition('email', $email);
    if (empty($query->execute()->fetchAll())) {
      // Insert the code.
      self::insertCode($connection, $email, $code);
    }
    // Update the code.
    self::updateCode($connection, $email, $code);
  }

  /**
   * @param $connection
   * @param $email
   * @param $code
   */
  protected static function insertCode($connection, $email, $code) {
    $connection->insert('registration_code')
      ->fields(['email' => $email, 'code' => $code])
      ->execute();
  }

  /**
   * @param $connection
   * @param $email
   * @param $code
   */
  protected static function updateCode($connection, $email, $code) {
    $connection->update('registration_code')
      ->fields(['code' => $code])
      ->condition('email', $email)
      ->execute();
  }

}


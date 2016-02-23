<?php

/**
 * @file
 * Contains \Drupal\registration_code\Utility\RegistrationCode.
 */

namespace Drupal\registration_code\Utility;

use Drupal\Core\Mail\MailManagerInterface;
use Egulias\EmailValidator\EmailValidator;
use Drupal\Core\Database\Connection;

/**
 * Defines a class containing utility methods to generate and send codes by email.
 */
class RegistrationCode {

  /**
   * Validates the email, generates and inserts the code into DB or updates it if
   * the registry already exists. If everything works as expected then sends
   * an email with the code.
   *
   * @param string $email
   * @param \Egulias\EmailValidator\EmailValidator $emailValidator
   * @param \Drupal\Core\Mail\MailManagerInterface $emailManager
   * @param \Drupal\Core\Database\Connection $connection
   * @param string $sender
   */
  public function registerCode($email, EmailValidator $emailValidator, MailManagerInterface $emailManager, Connection $connection, $sender) {
    // Verify that the email is valid. Please validate the email before call this method.
    if(!$emailValidator->isvalid($email)) {
      throw new \UnexpectedValueException('The email is not valid.');
    }

    // Generate Code.
    $code = self::generateCode();

    // Insert the new code into the DB.
    self::setCode($email, $code, $connection);

    // Send the code by email.
    $emailManager->mail('registration_code', 'send_code', $email, 'en', $params = array('code' => $code), $sender);
  }

  /**
   * Generates a random code that will be sent to the user by email.
   *
   * @return int
   */
  public function generateCode() {
    return rand(10000, 100000);
  }

  /**
   * Verifies if the email exists in the registration_code table. If it
   * exits then update the registry with the new code, otherwise inserts
   * the new code.
   *
   * @param string $email
   * @param integer $code
   * @param \Drupal\Core\Database\Connection $connection
   */
  protected function setCode($email, $code, Connection $connection) {
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
   * Inserts the code.
   *
   * @param \Drupal\Core\Database\Connection $connection
   * @param string $email
   * @param integer $code
   */
  protected function insertCode(Connection $connection, $email, $code) {
    $connection->insert('registration_code')
      ->fields(['email' => $email, 'code' => $code])
      ->execute();
  }

  /**
   * Updates the code.
   *
   * @param \Drupal\Core\Database\Connection $connection
   * @param string $email
   * @param integer $code
   */
  protected function updateCode(Connection $connection, $email, $code) {
    $connection->update('registration_code')
      ->fields(['code' => $code])
      ->condition('email', $email)
      ->execute();
  }

}


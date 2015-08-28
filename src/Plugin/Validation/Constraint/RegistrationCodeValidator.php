<?php
/**
 * @file
 * Contains \Drupal\registration_code\Plugin\Validation\Constraint\RegistrationCodeValidator.
 */

namespace Drupal\registration_code\Plugin\Validation\Constraint;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the RegistrationCode constraint.
 */
class RegistrationCodeValidator extends ConstraintValidator implements ContainerInjectionInterface {

  protected $database;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The current user.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    $codeToVerify = $item->getValue();
    // Obtain the mail from $context.
    $fields = $this->context->getRoot()->getValue()->getTypedData()->getProperties('entity');
    // If the user entity has no uid then it means that we are creating the account and
    // we need to validate the field in this case.
    if (empty($fields['uid']->getValue())) {
      //$this->context->addViolation($constraint->message);
      $mail = $fields['mail']->getValue();

      // Verify if the email exists.
      $query = $this->database->select('registration_code', 'rc');
      $query->fields('rc', ['code']);
      $query->condition('email', $mail[0]['value']);
      $code = $query->execute()->fetchfield();
      // Missing the code for the current email.
      if (!$code) {
        $this->context->addViolation($constraint->missingCodeMessage);
        return;
      }
      // The code is not correct.
      if ($code !== $codeToVerify[0]['value']) {
        $this->context->addViolation($constraint->message);
      }
    }
  }

}

<?php

/**
 * @file
 * Contains \Drupal\registration_code\Plugin\Validation\Constraint\RegistrationCode.
 */

namespace Drupal\registration_code\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if the code is the expected for anonymous registration.
 *
 * @Constraint(
 *   id = "registrationCode",
 *   label = @Translation("Verify the code for anonymous registration.", context = "Validation")
 * )
 */
class RegistrationCode extends Constraint {

  /**
   * Violation message.
   *
   * @var string
   */
  public $missingCodeMessage = "Hey request for the code again!";

  /**
   * Violation message.
   *
   * @var string
   */
  public $message = "Hey your code is not correct at all!";

}


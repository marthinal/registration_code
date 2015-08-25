<?php

/**
 * @file
 * Contains Drupal\registration_code\Plugin\rest\resource\RegistrationCodeResource.
 */

namespace Drupal\registration_code\Plugin\rest\resource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\EmailValidator;

/**
 * Represents user registration as resource.
 *
 * @RestResource(
 *   id = "registration_code",
 *   label = @Translation("Registration Code")
 * )
 *
 * @see \Drupal\rest\Plugin\Derivative\EntityDerivative
 */
class RegistrationCodeResource extends ResourceBase {

  /**
   * The email validator.
   *
   * @var \Symfony\Component\Validator\Constraints\EmailValidator
   */
  protected $emailValidator;


  /**
   * Constructs a new RegistrationCodeResource instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param LoggerInterface $loggery
   *   A logger instance.
   * @param \Symfony\Component\Validator\Constraints\EmailValidator
   *   The email validator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $loggery, EmailValidator $emailValidator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $loggery);
    $this->emailValidator = $emailValidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('email.validator')
    );
  }

  public function post($email) {
    // Empty email.
    if ($email == NULL) {
      throw new BadRequestHttpException('Missing email address.');
    }

    // Invalid email.
    if (!$this->emailValidator->isvalid($email)) {
      throw new BadRequestHttpException('Please insert a valid email address.');
    }

    return new ResourceResponse(NULL, 201);

  }

  /**
   * Generates
   */
  protected function generateCode() {
    return rand(10000, 100000);
  }

}


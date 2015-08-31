<?php

/**
 * @file
 * Contains Drupal\registration_code\Plugin\rest\resource\RegistrationCodeResource.
 */

namespace Drupal\registration_code\Plugin\rest\resource;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Egulias\EmailValidator\EmailValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Drupal\registration_code\Proxy\RegistrationCodeProxy;
use Drupal\Core\Database\Connection;

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
   * @var \Egulias\EmailValidator\EmailValidator
   */
  protected $emailValidator;

  /**
   * Proxy class used to create and insert the code.
   *
   * @var RegistrationCodeProxy
   */
  protected $codeProxy;

  /**
   * The flood control mechanism.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * The mail manager
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The database connection to use.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

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
   * @param \Egulias\EmailValidator\EmailValidator
   *   The email validator.
   * @param \Drupal\registration_code\Proxy\RegistrationCodeProxy
   *   The proxy class for register code methods.
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood control mechanism.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Service Object.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $loggery,
    EmailValidator $emailValidator,
    RegistrationCodeProxy $codeProxy,
    FloodInterface $flood,
    ConfigFactory $configFactory,
    MailManagerInterface $mail_manager,
    Connection $connection
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $loggery);

    $this->emailValidator = $emailValidator;
    $this->codeProxy = $codeProxy;
    $this->flood = $flood;
    $this->configFactory = $configFactory;
    $this->mailManager = $mail_manager;
    $this->connection = $connection;
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
      $container->get('email.validator'),
      new RegistrationCodeProxy(),
      $container->get('flood'),
      $container->get('config.factory'),
      $container->get('plugin.manager.mail'),
      $container->get('database')
    );
  }

  /**
   * Responds to the registration code POST requests, generating and sending the code by email.
   *
   * @param array $email
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   */
  public function post(array $email) {
    // Empty email.
    if ($email['email'][0]['value'] == NULL || $email['email'][0]['value'] == '') {
      throw new BadRequestHttpException('Missing email address.');
    }
    // Invalid email.
    if (!$this->emailValidator->isvalid($email['email'][0]['value'])) {
      throw new BadRequestHttpException('Please insert a valid email address.');
    }
    // Control the limit of code requests.
    $this->floodControl();
    // Generate the code and send by email.
    $this->codeProxy->registerCode($email['email'][0]['value'], $this->emailValidator, $this->mailManager, $this->connection,$this->config('system.site')->get('mail'));
    // Register each request to verify if the limit is exceeded.
    $this->flood->register('registration_code', $this->config('registration_code.settings')->get('flood.interval'));

    return new ResourceResponse(NULL, 204);

  }

  /**
   * Verify if the user can request for a code again.
   */
  protected function floodControl() {
    $limit = $this->config('registration_code.settings')->get('flood.limit');
    $interval = $this->config('registration_code.settings')->get('flood.interval');

    if (!$this->flood->isAllowed('registration_code', $limit, $interval)) {
      throw new BadRequestHttpException('Code requests limit exceeded.');
    }
  }

  /**
   * Get the config by name.
   *
   * @param $name
   * @return mixed
   */
  protected function config($name) {
    return $this->configFactory->get($name);
  }

}


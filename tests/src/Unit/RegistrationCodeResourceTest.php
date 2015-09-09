<?php

/**
 * @file
 * Contains \Drupal\Tests\registration_code\Unit\RegistrationCodeResourceTest.
 */

namespace Drupal\Tests\registration_code\Unit;

use Drupal\registration_code\Plugin\rest\resource\RegistrationCodeResource;
use Drupal\Tests\UnitTestCase;

/**
 * @group registration_code
 */
class RegistrationCodeResourceTest extends UnitTestCase {

  protected $testClass;
  protected $testClassMock;
  protected $logger;
  protected $emailValidator;
  protected $reflection;
  protected $codeProxy;
  protected $flood;
  protected $configFactory;
  protected $configStub;
  protected $emailManager;
  protected $connection;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->codeProxy = $this->getMock('\Drupal\registration_code\Proxy\RegistrationCodeProxy');

    $this->configFactory =  $this->getMockBuilder('\Drupal\Core\Config\ConfigFactory')
      ->disableOriginalConstructor()
      ->getMock();

    $this->flood = $this->getMockBuilder('\Drupal\Core\Flood\DatabaseBackend')
      ->setMethods(['register'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->logger = $this->getMock('Psr\Log\LoggerInterface');

    $this->emailManager = $this->getMockBuilder('\Drupal\Core\Mail\MailManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->emailValidator = $this->getMockBuilder('\Egulias\EmailValidator\EmailValidator')
      ->setMethods(['isValid'])
      ->getMock();

    $this->connection = $this->getMockBuilder('\Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();

    $this->testClass = new RegistrationCodeResource([], 'plugin_id', '', [], $this->logger, $this->emailValidator, $this->codeProxy, $this->flood, $this->configFactory, $this->emailManager, $this->connection);

    $this->testClassMock = $this->getMockBuilder('\Drupal\registration_code\Plugin\rest\resource\RegistrationCodeResource')
      ->setMethods(['floodControl', 'config'])
      ->setConstructorArgs([[], 'plugin_id', '', [], $this->logger, $this->emailValidator, $this->codeProxy, $this->flood, $this->configFactory, $this->emailManager, $this->connection])
      ->getMock();

    $this->reflection = new \ReflectionClass($this->testClass);
  }

  /**
   * Gets a protected method from current class using reflection.
   *
   * @param $method
   * @return mixed
   */
  public function getProtectedMethod($method) {
    $method = $this->reflection->getMethod($method);
    $method->setAccessible(TRUE);

    return $method;
  }

  /**
   * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   * @expectedExceptionMessage Missing email address.
   */
  public function testEmptyPost() {

    $this->testClass->post(['email' => [0 => ['value' => '']]]);
  }

  /**
   * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   * @expectedExceptionMessage Please insert a valid email address.
   */
  public function testPostInvalidEmailAddress() {
    // Invalid Email address.
    $this->emailValidator->expects($this->any())
      ->method('isValid')
      ->willReturn(0);

    $this->testClass->post(['email' => [0 => ['value' => 'YesIKnowThisIsAnInvalidEmailAddress']]]);
  }

  /**
   * Tests that the response object is correct.
   */
  public function testReturnsCorrectObject() {
    $this->configStub = $this->getConfigFactoryStub([
      'flood' => [
        'limit' => 5,
        'interval' => 3600
      ],
    ]);

    // Valid Email address.
    $this->emailValidator->expects($this->any())
      ->method('isValid')
      ->willReturn(1);

    $this->flood->expects($this->any())
      ->method('register')
      ->willReturn(1);

    $this->testClassMock->expects($this->any())
      ->method('config')
      ->willReturn($this->configStub);

    $response = $this->testClassMock->post(['email' => [0 => ['value' => 'druplicon@mysitesuperpoweredbydrupal.com']]]);

    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $response);
  }

  /**
   * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   * @expectedExceptionMessage The email is already registered.
   */
  public function testEmailAddressAlreadyExists() {

  }

}

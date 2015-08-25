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
  protected $logger;
  protected $emailValidator;
  protected $reflection;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->logger = $this->getMock('Psr\Log\LoggerInterface');
    $this->emailValidator = $this->getMockBuilder('\Symfony\Component\Validator\Constraints\EmailValidator')
      ->setMethods(array('isValid'))
      ->getMock();

    $this->testClass = new RegistrationCodeResource([], 'plugin_id', '', [], $this->logger, $this->emailValidator);
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
    $this->testClass->post(NULL);
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

    $this->testClass->post('YesIKnowThisIsAnInvalidEmailAddress');
  }

  /**
   * Tests that the response object is correct.
   */
  public function testReturnsCorrectObject() {
    // Valid Email address.
    $this->emailValidator->expects($this->any())
      ->method('isValid')
      ->willReturn(1);

    $response = $this->testClass->post('druplicon@mysitesuperpoweredbydrupal.com');
    $this->assertInstanceOf('Drupal\rest\ResourceResponse', $response);
  }

  public function testGenerateCodeExists() {
    $method = $this->getProtectedMethod('generateCode');
    // No exception is thrown.
    $method->invokeArgs($this->testClass, array());

  }

  public function testCodeIsIntegerAndOfNumberDigits() {
    $method = $this->getProtectedMethod('generateCode');
    // No exception is thrown.
    $code = $method->invokeArgs($this->testClass, array());
    $this->assertInternalType("int", $code);
    $this->assertGreaterThanOrEqual( 5, strlen($code), "Code has the right number of digits" );
  }

}

<?php

/**
 * @file
 * Contains \Drupal\Tests\registration_code\Unit\RegistrationCodeHelperTest.
 */

namespace Drupal\Tests\registration_code\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\registration_code\Utility\RegistrationCodeHelper;

/**
 * @group registration_code
 */
class RegistrationCodeHelperTest extends UnitTestCase {

  protected $flood;
  protected $emailManager;
  protected $emailValidator;
  protected $connection;

  protected function setUp() {
    parent::setUp();

    $this->flood = $this->getMockBuilder('\Drupal\Core\Flood\DatabaseBackend')
      ->setMethods(['register'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->emailManager = $this->getMockBuilder('\Drupal\Core\Mail\MailManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->emailValidator = $this->getMockBuilder('\Egulias\EmailValidator\EmailValidator')
      ->setMethods(['isValid'])
      ->getMock();

    $this->connection = $this->getMockBuilder('\Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * @expectedException \UnexpectedValueException
   * @expectedExceptionMessage The email is not valid.
   */
  public function testRegisterCodeWrongEmail() {
    RegistrationCodeHelper::registerCode('invalidEmail', $this->emailValidator, $this->emailManager, $this->connection, 'druplicon@superSitePoweredByDrupal.com');
  }

}

<?php
namespace BoilerAppTest\PHPUnit\TestCase;
abstract class AbstractHttpControllerTestCase extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase{
	use \BoilerAppTest\Doctrine\DoctrineUtilsTrait;

	/**
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	public function setUp(){
		if(class_exists($sBootstrapClass = current(explode('\\', get_called_class())).'\Bootstrap')){
			//Retrieve application config from Bootstrap
			if(is_callable(array($sBootstrapClass,'getApplicationConfig')))$this->setApplicationConfig(call_user_func(array($sBootstrapClass,'getApplicationConfig')));
			else throw new \BadMethodCallException('Method "getApplicationConfig" is not callable in "'.$sBootstrapClass.'" class');
		}
		else throw new \LogicException('Bootstrap class "'.$sBootstrapClass.'" does not exist');
		parent::setUp();
	}

	/**
	 * @see \Zend\Test\PHPUnit\Controller\AbstractControllerTestCase::tearDown()
	 */
	public function tearDown(){
		$this->cleanDatabase();
		unset($this->entityManager,$this->ormExcecutor,$this->schemaTool,$this->ormPurger);
		parent::tearDown();
	}
}
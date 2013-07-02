<?php
namespace BoilerAppTest\PHPUnit\TestCase;
abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase{
	use \BoilerAppTest\BootstrapFunctionsTrait;

	/**
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	public function tearDown(){
		unset($this->serviceManager);
	}

	public function testGetServiceManager(){
		$this->assertInstanceOf('Zend\ServiceManager\ServiceManager',$this->getServiceManager());
	}
}
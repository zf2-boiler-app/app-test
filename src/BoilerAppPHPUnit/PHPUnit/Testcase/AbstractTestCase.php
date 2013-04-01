<?php
namespace BoilerAppPHPUnit\PHPUnit\TestCase;
abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase{

	public function setUp(){
		//Retrieve service manager from bootstrap

		$this->serviceManager = $this->application->getServiceManager();
	}

	public function tearDown(){
		unset($this->serviceManager);
	}

	public function testGetServiceManager(){
		$this->assertInstanceOf('Zend\ServiceManager\ServiceManager',$this->getServiceManager());
	}

	/**
	 * @throws \LogicException
	 * @return \Zend\ServiceManager\ServiceManager
	 */
	protected function getServiceManager(){
		if($this->serviceManager instanceof \Zend\ServiceManager\ServiceManager)return $this->serviceManager;
		throw new \LogicException('Service manager is undefined');
	}
}
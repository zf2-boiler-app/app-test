<?php
namespace BoilerAppPHPUnit\PHPUnit\TestCase;
abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase{
	/**
	 * @var \Zend\ServiceManager\ServiceManager
	 */
	protected $serviceManager;
	
	protected function setUp(){		
		//Retrieve service manager from bootstrap
		if(class_exists($sBootstrapClass = current(explode('\\', get_called_class())).'\Bootstrap')){
			if(is_callable(array($sBootstrapClass,'getServiceManager')))$this->serviceManager = call_user_func(array($sBootstrapClass,'getServiceManager'));
			else throw new \BadMethodCallException('Method "getServiceManager" is not callable in "'.$sBootstrapClass.'" class');
		}
		else throw new \LogicException('Bootstrap class "'.$sBootstrapClass.'" does not exist');
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
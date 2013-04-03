<?php
namespace BoilerAppPHPUnit\PHPUnit\TestCase;
abstract class AbstractHttpControllerTestCase extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase{
	/**
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp(){
		//Retrieve service manager from bootstrap
		if(class_exists($sBootstrapClass = current(explode('\\', get_called_class())).'\Bootstrap')){
			if(is_callable(array($sBootstrapClass,'getServiceManager')))$this->setApplicationConfig(call_user_func(array($sBootstrapClass,'getServiceManager')));
			else throw new \BadMethodCallException('Method "getServiceManager" is not callable in "'.$sBootstrapClass.'" class');
		}
		else throw new \LogicException('Bootstrap class "'.$sBootstrapClass.'" does not exist');
		parent::setUp();
	}
}
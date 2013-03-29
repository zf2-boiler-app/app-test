<?php
namespace BoilerAppPHPUnit\PHPUnit\TestCase;
abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase{
	/**
	 * @var string
	 */
	protected $bootstrapPath;

	/**
	 * @var \Zend\Mvc\Application
	 */
	protected $application;

	/**
	 * @var \Zend\ServiceManager\ServiceManager
	 */
	protected $serviceManager;

	public function setUp(){
		if(!is_string($this->bootstrapPath))throw new \LogicException('Bootstrap path is undefined');
		$this->application = require $this->bootstrap;
		if(!($this->application instanceof \Zend\Mvc\Application))throw new \LogicException('Application could not be loaded from bootsrap');
		$this->serviceManager = $application->getServiceManager();
	}

	/**
	 * start afresh
	 *
	 * @return void
	 */
	public function tearDown(){
		unset($this->application,$this->serviceManager);
	}

	public function testServiceManagerInstance(){
		$this->assertInstanceOf('Zend\ServiceManager\ServiceManager',$this->serviceManager);
	}
}
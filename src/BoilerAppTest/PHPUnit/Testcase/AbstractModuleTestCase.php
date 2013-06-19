<?php
namespace BoilerAppTest\PHPUnit\TestCase;
abstract class AbstractModuleTestCase extends \PHPUnit_Framework_TestCase{
	protected $module;

	/**
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp(){
		parent::setUp();
		if(class_exists($sModuleClass = preg_replace('/Test$/','', current(explode('\\', get_called_class()))).'\Module'))$this->module = new $sModuleClass();
		else throw new \LogicException('Module class "'.$sModuleClass.'" does not exits');
	}

	public function testGetConfig(){
		$this->assertTrue(is_array($this->module->getConfig()));
	}
}
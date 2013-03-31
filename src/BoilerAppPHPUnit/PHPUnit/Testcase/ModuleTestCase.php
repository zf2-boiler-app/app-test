<?php
namespace BoilerAppPHPUnit\PHPUnit\TestCase;
abstract class ModuleTestCase extends \BoilerAppPHPUnit\PHPUnit\TestCase\AbstractTestCase{
	/**
	 * @var \BoilerAppDisplay\Module
	 */
	protected $module;

	/**
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp(){
		$sModuleClass = preg_replace('/Test$/','', __NAMESPACE__).'\Module';
		$this->module = new $sModuleClass();
	}

	public function testGetConfig(){
		$this->assertTrue(is_array($this->module->getConfig()));
	}
}
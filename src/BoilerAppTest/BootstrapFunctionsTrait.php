<?php
namespace BoilerAppTest;
trait BootstrapFunctionsTrait{
	/**
	 * @var string
	 */
	private $bootstrapClass;

	/**
	 * @var \Zend\ServiceManager\ServiceManager
	 */
	protected $serviceManager;

	/**
	 * @throws \LogicException
	 * @return \Zend\ServiceManager\ServiceManager
	 */
	protected function getServiceManager(){
		return $this->serviceManager instanceof \Zend\ServiceManager\ServiceManager
			?$this->serviceManager
			:$this->serviceManager = $this->callBootsrapFunction('getServiceManager');
	}

	/**
	 * @return boolean
	 */
	protected function getDbCreated(){
		return $this->callBootsrapFunction('getDbCreated');
	}

	/**
	 * @param boolean $bDbCreated
	 * @return \BoilerAppTest\BootstrapFunctionsTrait
	 */
	protected function setDbCreated($bDbCreated){
		$this->callBootsrapFunction('setDbCreated',$bDbCreated);
		return $this;
	}

	/**
	 * @throws \LogicException
	 * @return string|\BoilerAppTest\BootstrapFunctionsTrait
	 */
	private function getBootstrapClass(){
		if(is_string($this->bootstrapClass))return $this->bootstrapClass;
		//Retrieve service manager from bootstrap
		if(class_exists($sBootstrapClass = current(explode('\\', get_called_class())).'\Bootstrap'))$this->bootstrapClass = $sBootstrapClass;
		else throw new \LogicException('Bootstrap class "'.$sBootstrapClass.'" does not exist');
		return $this;
	}

	/**
	 * @param string $sFunction
	 * @throws \InvalidArgumentException
	 * @throws \BadMethodCallException
	 * @return mixed
	 */
	private function callBootsrapFunction($sFunction){
		if(!is_string($sFunction))throw new \InvalidArgumentException('Function expects string, "'.gettype($sFunction).'" given');
		if(is_callable(array($sBootstrapClass = $this->getBootstrapClass(),'getServiceManager')))return $this->serviceManager = call_user_func_array(
			array($sBootstrapClass,$sFunction),
			array_slice(func_get_args(), 1)
		);
		else throw new \BadMethodCallException('Method "'.$sFunction.'" is not callable in "'.$sBootstrapClass.'" class');
	}
}
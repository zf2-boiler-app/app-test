<?php
namespace BoilerAppTest\Doctrine;
trait DoctrineUtilsTrait{
	/**
	 * @var boolean
	 */
	protected $dbCreated = false;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $entityManager;

	/**
	 * @var \Doctrine\ORM\Tools\SchemaTool
	 */
	protected $schemaTool;

	/**
	 * @var \Doctrine\Common\DataFixtures\Purger\ORMPurger
	 */
	protected $ormPurger;

	/**
	 * @var \Doctrine\Common\DataFixtures\Executor\ORMExecutor
	 */
	protected $ormExcecutor;

	/**
	 * @param array $aFixtures
	 * @throws \InvalidArgumentException
	 * @return \BoilerAppTest\Dotrine\DoctrineUtilsTrait
	 */
    protected function addFixtures(array $aFixtures){
    	//Purge old fixtures
    	if($this->dbCreated)$this->getORMPurger()->purge();
   		//Create database
    	elseif($aMetadatas = $this->getEntityManager()->getMetadataFactory()->getAllMetadata()){
    		$oSchemaTool = $this->getSchemaTool();
    		$oSchemaTool->dropDatabase();
    		$oSchemaTool->createSchema($aMetadatas);
    		$this->dbCreated = true;
    	}
    	else throw new \LogicException('Metadatas are undefined');

    	//Retrieve service manager from getServiceManager function
    	if(is_callable(array($this,'getServiceManager')))$oServiceManager = call_user_func(array($this,'getServiceManager'));
   		//Retrieve service manager from bootstrap
    	elseif(class_exists($sBootstrapClass = current(explode('\\', get_called_class())).'\Bootstrap')){
    		if(is_callable(array($sBootstrapClass,'getServiceManager')))$oServiceManager = call_user_func(array($sBootstrapClass,'getServiceManager'));
    		else throw new \BadMethodCallException('Method "getServiceManager" is not callable in "'.$sBootstrapClass.'" class');
    	}
    	else throw new \LogicException('Bootstrap class "'.$sBootstrapClass.'" does not exist');

    	$oLoader = new \Doctrine\Common\DataFixtures\Loader();
    	foreach($aFixtures as $oFixture){
	    	if(is_string($oFixture)){
				if(class_exists($oFixture))$oFixture = new $oFixture();
	    		elseif(file_exists($oFixture)){
	    			require_once $oFixture;

	    			foreach(get_declared_classes() as $sClassName){
	    				$oReflectionClass = new \ReflectionClass($sClassName);
	    				if(
	    					$oReflectionClass->getFileName() === $oFixture
	    					&& !$oReflectionClass->isAbstract()
	    					&& in_array('Doctrine\Common\DataFixtures\FixtureInterface',class_implements($sClassName))
	    				){
	    					$oFixture = new $sClassName();
	    					break;
	    				}
	    			}
	    		}
	    	}
	    	if(!is_object($oFixture) || !($oFixture instanceof \Doctrine\Common\DataFixtures\FixtureInterface))throw new \InvalidArgumentException(sprintf(
	    		'Fixture is not valid : "%s"',
	    		is_scalar($oFixture)?$oFixture:(is_object($oFixture)?get_class($oFixture):gettype($oFixture))
	    	));

	    	//Set service locator if needed
	    	if($oFixture instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface)$oFixture->setServiceLocator($oServiceManager);

	    	$oLoader->addFixture($oFixture);
    	}
    	$this->getORMExecutor()->execute($oLoader->getFixtures());
    	return $this;
    }

    /**
     * @return \BoilerAppTest\Dotrine\DoctrineUtilsTrait
     */
    protected function cleanDatabase(){
   		//Drop database if created
    	if($this->dbCreated)$this->getSchemaTool()->dropDatabase();
    	return $this;
    }

    /**
     * @throws \BadMethodCallException
     * @throws \LogicException
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager(){
    	if($this->entityManager instanceof \Doctrine\ORM\EntityManager)return $this->entityManager;
    	//Retrieve service manager from getServiceManager function
    	if(is_callable(array($this,'getServiceManager')))$oServiceManager = call_user_func(array($this,'getServiceManager'));
    	//Retrieve service manager from bootstrap
    	elseif(class_exists($sBootstrapClass = current(explode('\\', get_called_class())).'\Bootstrap')){
    		if(is_callable(array($sBootstrapClass,'getServiceManager')))$oServiceManager = call_user_func(array($sBootstrapClass,'getServiceManager'));
    		else throw new \BadMethodCallException('Method "getServiceManager" is not callable in "'.$sBootstrapClass.'" class');
    	}
    	else throw new \LogicException('Bootstrap class "'.$sBootstrapClass.'" does not exist');
    	return $this->entityManager = $oServiceManager->get('Doctrine\ORM\EntityManager');
    }

    /**
     * @return \Doctrine\ORM\Tools\SchemaTool
     */
    protected function getSchemaTool(){
    	if($this->schemaTool instanceof \Doctrine\ORM\Tools\SchemaTool)return $this->schemaTool;
    	return $this->schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->getEntityManager());
    }

    /**
     * @return \Doctrine\Common\DataFixtures\Purger\ORMPurger
     */
    protected function getORMPurger(){
    	if($this->ormPurger instanceof \Doctrine\Common\DataFixtures\Purger\ORMPurger)return $this->ormPurger;
    	//Initialize ORM Purger
    	return $this->ormPurger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->getEntityManager());
    }

    /**
     * @return \Doctrine\Common\DataFixtures\Executor\ORMExecutor
     */
    protected function getORMExecutor(){
    	if($this->ormExcecutor instanceof \Doctrine\Common\DataFixtures\Executor\ORMExecutor)return $this->ormExcecutor;
    	//Initialize ORM Executor
    	return $this->ormExcecutor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor(
    		$this->getEntityManager(),
    		$this->getORMPurger()
    	);
    }
}
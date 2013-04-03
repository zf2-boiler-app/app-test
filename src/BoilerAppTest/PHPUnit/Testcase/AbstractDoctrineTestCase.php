<?php
namespace BoilerAppTest\PHPUnit\TestCase;
abstract class AbstractDoctrineTestCase extends \BoilerAppTest\PHPUnit\TestCase\AbstractTestCase{
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
     * @see \BoilerAppTest\PHPUnit\TestCase\AbstractTestCase::setUp()
     */
    protected function setUp(){
        parent::setup();

        $oEntityManager = $this->getEntityManager();

        //Create database
        if($aMetadatas = $oEntityManager->getMetadataFactory()->getAllMetadata())$this->getSchemaTool()->createSchema($aMetadatas);
        else throw new \LogicException('Metadatas are undefined');
    }

    /**
     * @see \BoilerAppTest\PHPUnit\TestCase\AbstractTestCase::tearDown()
     */
    public function tearDown(){
    	//Purge old fixtures
    	$this->getORMPurger()->purge();

		//Drop database
    	$this->getSchemaTool()->dropDatabase();
        unset($this->entityManager,$this->ormExcecutor);
        parent::tearDown();
    }

    /**
     * @param array $aFixtures
     * @throws \InvalidArgumentException
     * @return \BoilerAppTest\PHPUnit\TestCase\DoctrineTestCase
     */
    protected function addFixtures(array $aFixtures){
    	//Purge old fixtures
    	$this->getORMPurger()->purge();

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
	    	if($oFixture instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface)$oFixture->setServiceLocator($this->getServiceManager());

	    	$oLoader->addFixture($oFixture);
    	}
    	$this->getORMExecutor()->execute($oLoader->getFixtures());
    	return $this;
    }

    /**
     * @throws \LogicException
     * @return \Zend\ServiceManager\ServiceManager
     */
    protected function getEntityManager(){
    	if($this->entityManager instanceof \Doctrine\ORM\EntityManager)return $this->entityManager;
    	return $this->entityManager = $this->getServiceManager()->get('Doctrine\ORM\EntityManager');
    }

    /**
     * @throws \LogicException
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

    public function testGetEntityManager(){
    	$this->assertInstanceOf('Doctrine\ORM\EntityManager',$this->getEntityManager());
    }

    public function testGetSchemaTool(){
    	$this->assertInstanceOf('Doctrine\ORM\Tools\SchemaTool',$this->getSchemaTool());
    }

    public function testGetORMPurger(){
    	$this->assertInstanceOf('Doctrine\Common\DataFixtures\Purger\ORMPurger',$this->getORMPurger());
    }

    public function testGetORMExecutor(){
    	$this->assertInstanceOf('Doctrine\Common\DataFixtures\Executor\ORMExecutor',$this->getORMExecutor());
    }
}
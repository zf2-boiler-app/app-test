<?php
namespace BoilerAppPHPUnit\PHPUnit\TestCase;
abstract class AbstractDoctrineTestCase extends \BoilerAppPHPUnit\PHPUnit\TestCase\AbstractTestCase{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Doctrine\Common\DataFixtures\Purger\ORMPurger
     */
    protected $ormPurger;

    /**
     * @var \Doctrine\Common\DataFixtures\Executor\ORMExecutor
     */
    protected $ormExcecutor;

    /**
     * @see \BoilerAppPHPUnit\PHPUnit\TestCase\AbstractTestCase::setUp()
     */
    protected function setUp(){
        parent::setup();

        $oEntityManager = $this->getEntityManager();

        //Create database
        if($aMetadatas = $oEntityManager->getMetadataFactory()->getAllMetadata()){
        	$oSchemaTool = new \Doctrine\ORM\Tools\SchemaTool($oEntityManager);
            $oSchemaTool->createSchema($aMetadatas);
        }
        else throw new \LogicException('Metadatas are undefined');
    }

    /**
     * @see \BoilerAppPHPUnit\PHPUnit\TestCase\AbstractTestCase::tearDown()
     */
    public function tearDown(){
        parent::tearDown();
        $oSchemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->getEntityManager());
        $oSchemaTool->dropDatabase();
        unset($this->entityManager,$this->ormExcecutor);
    }

    /**
     * @param array $aFixtures
     * @throws \InvalidArgumentException
     * @return \BoilerAppPHPUnit\PHPUnit\TestCase\DoctrineTestCase
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
	    		is_object($oFixture)?get_class($oFixture):gettype($oFixture)
	    	));
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
     * @return \Doctrine\Common\DataFixtures\Purger\ORMPurger
     */
    protected function getORMPurger(){
    	if($this->ormPurger instanceof \Doctrine\Common\DataFixtures\Purger\ORMPurger)return $this->ormPurger;
    	//Initialize ORM Purger
    	return $this->ormPurger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger();
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

    public function testGetORMPurger(){
    	$this->assertInstanceOf('Doctrine\Common\DataFixtures\Purger\ORMPurger',$this->getORMPurger());
    }

    public function testGetORMExecutor(){
    	$this->assertInstanceOf('Doctrine\Common\DataFixtures\Executor\ORMExecutor',$this->getORMExecutor());
    }
}
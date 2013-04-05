<?php
namespace BoilerAppTest\PHPUnit\TestCase;
abstract class AbstractDoctrineTestCase extends \BoilerAppTest\PHPUnit\TestCase\AbstractTestCase{
	use \BoilerAppTest\Dotrine\DoctrineUtilsTrait;

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
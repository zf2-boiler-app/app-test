<?php
namespace BoilerAppTest\PHPUnit\TestCase;
abstract class AbstractDoctrineTestCase extends \PHPUnit_Framework_TestCase{
	use \BoilerAppTest\Doctrine\DoctrineUtilsTrait;

    /**
     * @see \BoilerAppTest\PHPUnit\TestCase\AbstractTestCase::tearDown()
     */
    public function tearDown(){
    	$this->cleanDatabase();
		unset($this->serviceManager,$this->entityManager,$this->ormExcecutor,$this->schemaTool,$this->ormPurger);
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
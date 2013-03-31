<?php
namespace BoilerAppPHPUnit\PHPUnit\TestCase;
abstract class DoctrineTestCase extends AbstractTestCase{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $tmpDir;

    public function setUp(){
        parent::setup();
        $this->entityManager = $this->getServiceManager()->get($this->emAlias);
        if($this->tmpDir && !is_dir($this->tmpDir))mkdir($this->tmpDir);
        if($sMetadatas = $this->entityManager->getMetadataFactory()->getAllMetadata()){
            $oSchemaTool = new \Doctrine\ORM\SchemaTool($this->entityManager);
            $oSchemaTool->createSchema($sMetadatas);
        } 
        else throw new \LogicException('Metadatas are undefined');
    }

    public function tearDown(){
        parent::tearDown();
        unset($this->entityManager);
       	//Remove temp directory
        if($this->tmpDir)foreach(new \RecursiveIteratorIterator(
        	new \RecursiveDirectoryIterator($this->tmpDir, \RecursiveDirectoryIterator::SKIP_DOTS),
        	\RecursiveIteratorIterator::CHILD_FIRST
        ) as $oFileinfo){
        	if($oFileinfo->isDir())rmdir($oFileinfo->getRealPath());
        	else unlink($oFileinfo->getRealPath());
        }
    }

    public function testGetEntityManager(){
        $this->assertInstanceOf('Doctrine\ORM\EntityManager',$this->getEntityManager());
    }

    /**
     * @throws \LogicException
     * @return \Zend\ServiceManager\ServiceManager
     */
    protected function getEntityManager(){
    	if($this->entityManager instanceof \Doctrine\ORM\EntityManager)return $this->entityManager;
    	throw new \LogicException('Entity manager is undefined');
    }
}
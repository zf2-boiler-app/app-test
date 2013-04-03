<?php
namespace BoilerAppTest\Doctrine\Common\DataFixtures;
abstract class AbstractFixture implements \Doctrine\Common\DataFixtures\FixtureInterface, \Zend\ServiceManager\ServiceLocatorAwareInterface{
	use \Zend\ServiceManager\ServiceLocatorAwareTrait;
}
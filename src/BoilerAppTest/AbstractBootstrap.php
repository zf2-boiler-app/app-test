<?php
namespace BoilerAppTest;
abstract class AbstractBootstrap{
    /**
     * @var \Zend\Mvc\Application
     */
    private static $application;

    /**
     * @var array
     */
    private static $applicationConfig;

    /**
     * @var string
     */
    private static $testsDir;

    /**
     * Initialize bootstrap
     */
    public static function init(){
        static::initAutoloader();

        //Load the user-defined test configuration file, if it exists;
        if(is_readable($sConfigPath = self::getTestDir().'/TestConfig.php'))$aTestConfig = include $sConfigPath;
        elseif(is_readable($sConfigDistPath = self::getTestDir().'/TestConfig.php.dist'))$aTestConfig = include $sConfigDistPath;
        else throw new \LogicException('Config file ("'.$sConfigPath.'" or "'.$sConfigDistPath.'") does not exists');

        $aZf2ModulePaths = array();
        if(isset($aTestConfig['module_listener_options']['module_paths']))foreach($aTestConfig['module_listener_options']['module_paths'] as $sModulePath){
        	if(($sPath = static::findParentPath($sModulePath)))$aZf2ModulePaths[] = $sPath;
        }

        //Use ModuleManager to load this module and it's dependencies
        self::setApplicationConfig(\Zend\Stdlib\ArrayUtils::merge(array(
            'module_listener_options' => array(
                'module_paths' => array_merge(
                	$aZf2ModulePaths,
                	explode(PATH_SEPARATOR, (getenv('ZF2_MODULES_TEST_PATHS')?:(defined('ZF2_MODULES_TEST_PATHS')?ZF2_MODULES_TEST_PATHS:'')))
                )
            )
        ),$aTestConfig));
    }

    /**
     * Initialize Autoloader
     * @throws RuntimeException
     */
    private static function initAutoloader(){
    	if(is_readable($sAutoloadPath = static::findParentPath('vendor').DIRECTORY_SEPARATOR.'autoload.php'))include $sAutoloadPath;
    	if(!class_exists('Zend\Loader\AutoloaderFactory'))throw new \RuntimeException('Unable to load ZF2. Install required libraries through `composer`');

    	$oReflectionClass = new \ReflectionClass(get_called_class());
    	$sNamespace = $oReflectionClass->getNamespaceName();

    	$aNamespaces = array();
    	if(is_dir($sFixturesDir = self::getTestDir().DIRECTORY_SEPARATOR.'Fixture'))$aNamespaces[$sNamespace.'\Fixture'] = $sFixturesDir;
    	$aNamespaces[$sNamespace] = self::getTestDir().DIRECTORY_SEPARATOR.$sNamespace;

    	\Zend\Loader\AutoloaderFactory::factory(array(
    		'Zend\Loader\StandardAutoloader' => array(
    			'autoregister_zf' => true,
    			'namespaces' => $aNamespaces
    		)
    	));
    }

    /**
     * Retrieve parent for a given path
     * @param string $sPath
     * @return boolean|string
     */
    private static function findParentPath($sPath){
    	$sPreviousDir = $sCurrentDir = self::getTestDir();
    	while(!is_dir($sPreviousDir.DIRECTORY_SEPARATOR.$sPath)){
    		$sCurrentDir = dirname($sCurrentDir);
    		if($sPreviousDir === $sCurrentDir)return false;
    		$sPreviousDir = $sCurrentDir;
    	}
    	return realpath($sCurrentDir.DIRECTORY_SEPARATOR.$sPath);
    }

    /**
     * Get the application config
     * @return array
     */
    public static function getApplicationConfig(){
    	if(!is_array(self::$applicationConfig))throw new \LogicException('Application config is undefined');
    	return self::$applicationConfig;
    }

    /**
     * Set the application config
     * @param array $aApplicationConfig
     * @throws \LogicException
     */
    public static function setApplicationConfig(array $aApplicationConfig){
    	if(null !== self::$application && null !== self::$applicationConfig)throw new \LogicException('Application config can not be set, the application is already built');
    	if(isset($applicationConfig['module_listener_options']['config_cache_enabled']))$aApplicationConfig['module_listener_options']['config_cache_enabled'] = false;
    	self::$applicationConfig = $aApplicationConfig;
    }

    /**
     * Get the application object
     * @return \Zend\Mvc\Application
     */
    public static function getApplication(){
    	if(self::$application instanceof \Zend\Mvc\ApplicationInterface)return self::$application;
    	self::$application = \Zend\Mvc\Application::init(self::getApplicationConfig());
    	$oEventManager = self::$application->getEventManager();
    	$oEventManager->detach(self::$application->getServiceManager()->get('SendResponseListener'));
    	return self::$application;
    }

    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    public static function getServiceManager(){
    	return self::getApplication()->getServiceManager();
    }

    /**
     * Attempt to retrieve tests dir (same as Bootsrap.php)
     * @throws \LogicException
     * @return string
     */
    public static function getTestDir(){
    	if(is_string(self::$testsDir))return self::$testsDir;
    	$oReflectionClass = new \ReflectionClass(get_called_class());
    	if($sFileName = $oReflectionClass->getFileName())return self::$testsDir = realpath(dirname($sFileName));
    	throw new \LogicException('Unable to retrieve Bootstrap "'.$oReflectionClass->getName().'" filename.');
    }
}
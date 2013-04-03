<?php
namespace BoilerAppPHPUnit\PHPUnit;
abstract class AbstractBootstrap{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
	protected static $serviceManager;

	/**
	 * @var array
	 */
	protected static $config;

    /**
     * Initialize bootstrap
     */
    public static function init(){
        //Load the user-defined test configuration file, if it exists;
        if(is_readable($sConfigPath = __DIR__ . '/TestConfig.php'))$aTestConfig = include $sConfigPath;
        elseif(is_readable($sConfigDistPath = __DIR__ . '/TestConfig.php.dist'))$aTestConfig = include $sConfigDistPath;
        else throw new \LogicException('Config file ("'.$sConfigPath.'" or "'.$sConfigDistPath.'") does not exists');

        $aZf2ModulePaths = array();
        if(isset($aTestConfig['module_listener_options']['module_paths']))foreach($aTestConfig['module_listener_options']['module_paths'] as $sModulePath){
        	if(($sPath = static::findParentPath($sModulePath)))$aZf2ModulePaths[] = $sPath;
        }
        static::initAutoloader();

        //Use ModuleManager to load this module and it's dependencies
        static::$config = \Zend\Stdlib\ArrayUtils::merge(array(
            'module_listener_options' => array(
                'module_paths' => array_merge(
                	$aZf2ModulePaths,
                	explode(PATH_SEPARATOR, (getenv('ZF2_MODULES_TEST_PATHS')?:(defined('ZF2_MODULES_TEST_PATHS')?ZF2_MODULES_TEST_PATHS:'')))
                )
            )
        ),$aTestConfig);
        static::$serviceManager = new \Zend\ServiceManager\ServiceManager(new \Zend\Mvc\Service\ServiceManagerConfig());
        static::$serviceManager->setService('ApplicationConfig',static::$config)->get('ModuleManager')->loadModules();

        if(is_readable($sConfigurationPath = __DIR__.'/configuration.php')){
        	$aConfiguration = \Zend\Stdlib\ArrayUtils::merge(static::$serviceManager->get('Config'),include __DIR__.'/configuration.php');
        	$bAllowOverride = static::$serviceManager->getAllowOverride();
        	if(!$bAllowOverride)static::$serviceManager->setAllowOverride(true);
        	static::$serviceManager->setService('Config',$aConfiguration)->setAllowOverride($bAllowOverride);
        }
    }

    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    public static function getServiceManager(){
        return static::$serviceManager;
    }

    /**
     * @return array
     */
    public static function getConfig(){
        return static::$config;
    }

    /**
     * Initialize Autoloader
     * @throws RuntimeException
     */
    protected static function initAutoloader(){
        if(is_readable($sAutoloadPath = static::findParentPath('vendor').'/autoload.php'))include $sAutoloadPath;
        if(!class_exists('Zend\Loader\AutoloaderFactory'))throw new \RuntimeException('Unable to load ZF2. Install required libraries through `composer`');

        $aNamespaces = array(__NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__);
        if(is_dir($sFixturesDir = __DIR__ . '/Fixture'))$aNamespaces[__NAMESPACE__.'\Fixture'] = $sFixturesDir;
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
    protected static function findParentPath($sPath){
        $sCurrentDir = __DIR__;
        $sPreviousDir = '.';
        while(!is_dir($sPreviousDir . '/' . $sPath)){
            $sCurrentDir = dirname($sCurrentDir);
            if($sPreviousDir === $sCurrentDir)return false;
            $sPreviousDir = $sCurrentDir;
        }
        return $sCurrentDir . '/' . $sPath;
    }
}
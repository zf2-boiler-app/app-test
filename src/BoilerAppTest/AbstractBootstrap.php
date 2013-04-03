<?php
namespace BoilerAppTest;
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
        if(is_readable($sConfigPath = getcwd() . '/TestConfig.php'))$aTestConfig = include $sConfigPath;
        elseif(is_readable($sConfigDistPath = getcwd() . '/TestConfig.php.dist'))$aTestConfig = include $sConfigDistPath;
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

        if(is_readable($sConfigurationPath = getcwd().'/configuration.php')){
        	$aConfiguration = \Zend\Stdlib\ArrayUtils::merge(static::$serviceManager->get('Config'),include $sConfigurationPath);
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
        if(is_readable($sAutoloadPath = static::findParentPath('vendor').DIRECTORY_SEPARATOR.'autoload.php'))include $sAutoloadPath;
        if(!class_exists('Zend\Loader\AutoloaderFactory'))throw new \RuntimeException('Unable to load ZF2. Install required libraries through `composer`');

        $oReflectionClass = new \ReflectionClass(get_called_class());
        $sNamespace = $oReflectionClass->getNamespaceName();

        $aNamespaces = array();
        if(is_dir($sFixturesDir = getcwd().DIRECTORY_SEPARATOR.'Fixture'))$aNamespaces[$sNamespace.'\Fixture'] = $sFixturesDir;
        $aNamespaces[$sNamespace] = getcwd().DIRECTORY_SEPARATOR.$sNamespace;

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
        $sCurrentDir = getcwd();
        $sPreviousDir = '.';
        while(!is_dir($sPreviousDir . '/' . $sPath)){
            $sCurrentDir = dirname($sCurrentDir);
            if($sPreviousDir === $sCurrentDir)return false;
            $sPreviousDir = $sCurrentDir;
        }
        return $sCurrentDir . '/' . $sPath;
    }
}
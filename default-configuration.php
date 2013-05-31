<?php
return array(
	'translator' => array(
		'locale' => 'fr_FR'
	),
	'doctrine' => array(
		'connection' => array(
			'orm_default' => array(
				'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
				'params' => array(
					'host' => 'localhost',
					'user' => 'root',
					'password' => ''
				)
			)
		)
	),
	'asset_bundle' => array(
		'cachePath' => getcwd().'/tests/_files/cache',
		'cacheUrl' => '@zfBaseUrl/cache/',
		'assetsPath' => null
	),
	'service_manager' => array(
		'factories' => array(
			'Logger' => function(){
				$oLogger = new \Zend\Log\Logger();
				return $oLogger->addWriter(new \Zend\Log\Writer\Stream(STDERR));
			}
		)
	)
);
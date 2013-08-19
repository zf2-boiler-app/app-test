ZF2 BoilerApp "Test" module
=====================

[![Build Status](https://travis-ci.org/zf2-boiler-app/app-test.png?branch=master)](https://travis-ci.org/zf2-boiler-app/app-test)
[![Latest Stable Version](https://poser.pugx.org/zf2-boiler-app/app-test/v/stable.png)](https://packagist.org/packages/zf2-boiler-app/app-test)
[![Total Downloads](https://poser.pugx.org/zf2-boiler-app/app-test/downloads.png)](https://packagist.org/packages/zf2-boiler-app/app-test)

NOTE : This module is in heavy development, it's not usable yet.
If you want to contribute don't hesitate, I'll review any PR.

Introduction
------------

__ZF2 BoilerApp "Test" module__ is a Zend Framework 2 module that provides tools to test modules of ZF2 BoilerApp

Requirements
------------

* [Zend Framework 2](https://github.com/zendframework/zf2) (latest master)

Installation
------------

### Main Setup

#### By cloning project

1. Clone this project into your `./vendor/` directory.

#### With composer

1. Add this project in your composer.json:

    ```json
    "require-dev": {
        "zf2-boiler-app/app-test": "dev-master"
    }
    ```

2. Now tell composer to download __ZF2 BoilerApp Test module__ by running the command:

    ```bash
    $ php composer.phar update
    ```

#### Post installation

1. Enabling it in your `TestConfig.php.dist` file.

    ```php
    return array(
        'modules' => array(
            // ...
            'BoilerAppTest',
        ),
        // ...
    );
    ```

2. Create the `Bootstrap.php` file

    ```php
    namespace MyModuleTest;
    error_reporting(E_ALL | E_STRICT);
    chdir(dirname(__DIR__));
    if(is_readable($sBoilerAppTestBootstrapPath = __DIR__.'/../vendor/zf2-boiler-app/app-test/src/BoilerAppTest/AbstractBootstrap.php'))include $sBoilerAppTestBootstrapPath;
    if(!class_exists('BoilerAppTest\AbstractBootstrap'))throw new \RuntimeException('Unable to load BoilerAppTest Bootstrap. Install required libraries through `composer`');
    class Bootstrap extends \BoilerAppTest\AbstractBootstrap{}
    Bootstrap::init();    
    ```

## Features

####Bootstraping

- Application and test configuration autoloading
- Service manager accessor

####Doctrine

- Database auto creator
- Fixture autoloading

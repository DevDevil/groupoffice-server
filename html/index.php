<?php
/* @var $classLoader Composer\Autoload\ClassLoader */
chdir(dirname(__FILE__));
$classLoader = require("../vendor/autoload.php");

use GO\Core\App as App;

//Initialize the framework with confuration
App::init(require('../config.php'));

//Class loader used to find PHP classes
App::config()->classLoader = $classLoader;

//Run the controller
App::router()->run();

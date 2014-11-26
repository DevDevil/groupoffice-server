<?php

namespace Intermesh\Core;

use Intermesh\Core\App;

/**
 * The App class is a collection of static functions to access common services
 * like the configuration, reqeuest, debugger etc.
 */
class AppTest extends \PHPUnit_Framework_TestCase{
	function testInit(){

		App::init(require('config.php'));

		$this->assertEquals(App::config()->productName,"Group-Office 7.0");


	}
}

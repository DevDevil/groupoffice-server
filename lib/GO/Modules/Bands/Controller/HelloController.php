<?php
namespace GO\Modules\Bands\Controller;

use GO\Core\Controller\AbstractRESTController;

class HelloController extends AbstractRESTController {
	public function httpGet($name = "human"){
		return ['data' => 'Hello '.$name];
	}
}
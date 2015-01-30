<?php
namespace GO\Modules\Bands\Controller;

use GO\Core\Controller\AbstractController;

class HelloController extends AbstractController {
	public function httpGet($name = "human"){
		return ['data' => 'Hello '.$name];
	}
}
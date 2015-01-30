<?php
namespace GO\Modules\Bands\Controller;

use GO\Core\Controller\AbstractController;

class HelloController extends AbstractController {
	public function actionName($name = "human"){
		return ['data' => 'Hello '.$name];
	}
}
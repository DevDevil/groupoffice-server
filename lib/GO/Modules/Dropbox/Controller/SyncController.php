<?php
namespace GO\Modules\Dropbox\Controller;

use GO\Core\Controller\AbstractController;
use GO\Modules\Dropbox\Model\Account;

class SyncController extends AbstractController{
	public function actionSync(){
		header('Content-Type: text/plain');
		$account = Account::find()->single();
		
		$account->sync();
		
	}
}
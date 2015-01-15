<?php
namespace GO\Modules\Dropbox\Controller;

use GO\Core\Controller\AbstractCrudController;
use GO\Modules\Dropbox\Model\Account;

class SyncController extends AbstractCrudController{
	public function actionSync(){
		header('Content-Type: text/plain');
		$account = Account::find()->single();
		
		$account->sync();
		
	}
}
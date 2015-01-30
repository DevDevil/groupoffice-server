<?php

namespace GO\Modules\Dropbox\Controller;

use Dropbox as dbx;
use Dropbox\WebAuth;
use GO\Core\App;
use GO\Core\Controller\AbstractController;
use GO\Modules\Dropbox\Config;
use GO\Modules\Dropbox\CsrfTokenStore;

class AuthorizeController extends AbstractController {
	
	/**
	 * 
	 * @return WebAuth
	 */
	private function getWebAuth(){
		return  new dbx\WebAuth(
				Config::getAppInfo(),
				App::config()->productName,
				App::router()->buildUrl('IPE/dropbox/authorize/callback'),
				new CsrfTokenStore()
				);
	}
	
	
	protected function actionRequestToken(){
		
		$authorizeUrl = $this->getWebAuth()->start();
		
		App::request()->redirect($authorizeUrl);		
	}
	
	protected function actionCallback(){
		list($accessToken, $dropboxUserId) = $this->getWebAuth()->finish($_GET);
		print "Access Token: " . $accessToken . "\n";
		
		$accessTokenModel = Config::getAccount();		
		$accessTokenModel->accessToken = $accessToken;		
		$accessTokenModel->dropboxUserId = $dropboxUserId;
		$accessTokenModel->save();				
	}
	
	protected function actionGetAccountInfo(){
		
		$dbxClient = Config::getClient();
		
		$accountInfo = $dbxClient->getAccountInfo();
		print_r($accountInfo);	
	}



}

<?php
namespace GO\Modules\Dropbox;

use Dropbox\ValueStore;

class CsrfTokenStore implements ValueStore{
	public function clear() {
		$accessTokenModel = Config::getAccount();		
		$accessTokenModel->requestToken = null;
		
		return $accessTokenModel->save();
	}

	public function get() {
		$accessTokenModel = Config::getAccount();		
		return $accessTokenModel->requestToken;
		
	}

	public function set($value) {
		$accessTokenModel = Config::getAccount();		
		$accessTokenModel->requestToken = $value;
		
		return $accessTokenModel->save();
		
	}

}
<?php
namespace GO\Core\Auth\Oauth2;

use GO\Core\Auth\Model\User;
use GO\Core\Auth\Oauth2\Server;
use GO\Core\Auth\Provider\AuthenticationProviderInterface;


class Provider implements AuthenticationProviderInterface{
	
	public function getUser() {
		$server = Server::newInstance();	

		// validate the authorize request		
		$r = $server->verifyResourceRequest(new Request());
		
		if (!$r) {
			$server->getResponse()->send();
			exit();
		}
		
		$token = $server->getResourceController()->getToken();
		
		$user = User::findByPk($token['user_id']);
		
		return $user;
	}

}
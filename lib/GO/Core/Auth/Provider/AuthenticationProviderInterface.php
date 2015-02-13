<?php
namespace GO\Core\Auth\Provider;

use GO\Core\Auth\Model\User;

interface AuthenticationProviderInterface {
	/**
	 * Get's the authenticated user or false if none
	 * 
	 * @return User|false
	 */
	public function getUser();
}
<?php

namespace GO\Core\Auth\Browser\Controller;

use GO\Core\App;
use GO\Core\Auth\Browser\Model\Token;
use GO\Core\Auth\Model\User;
use GO\Core\Controller\AbstractController;

/**
 * The controller that handles authentication
 * 
 * {@see \GO\Core\Auth\Browser\Model\Token}
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class AuthController extends AbstractController {

	protected function authenticate() {
		return true;
	}

	/**
	 * Logs the current user out.
	 */
	protected function actionLogout() {
		
		$token = Token::findByCookie();		
		if($token) {
			$token->unsetCookies();
			$token->delete();
		}

		return $this->renderJson(['success' => true]);
	}

	/**
	 * Logs the current user in.
	 *
	 * <p>Sample JSON post:</p>
	 *
	 * <code>
	 * {
	 * 	"username": "user",
	 * 	"password": "secret"
	 * }
	 * </code>
	 *
	 * @returns JSON {"userId": "Current ID of user", "securityToken": "token required in each request"}
	 */
	public function actionLogin() {

		$user = User::login(App::request()->payload['username'], App::request()->payload['password'], true);
		
		if($user){
			$token = new Token();
			$token->user = $user;
			$token->save();
			$token->setCookies();
		}

		$response = [			
			'success' => $user !== false
		];
		
		if($response['success']) {
			$response['XSRFToken'] = $token->XSRFToken;
		}

//		if ($response['success']) {
//			//todo remember for different clients
//			if (!empty(App::request()->payload['remember'])) {
//				Token::generateSeries($user->id);
//			}
//		}

		return $this->renderJson($response);
	}

	public function actionIsLoggedIn() {
		$token = Token::findByCookie(false);
//		if($token) {
//			
//			//resend cookies as client might need the XSRFToken again
//			$token->setCoookies();
//		}
		$response = [
			'success' => $token !== false
		];

		return $this->renderJson($response);
	}

}

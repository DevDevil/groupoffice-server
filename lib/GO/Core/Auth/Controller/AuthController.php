<?php

namespace GO\Core\Auth\Controller;

use GO\Core\Auth\Model\Token;
use GO\Core\Auth\Model\User;
use GO\Core\App;

/**
 * The controller that handles authentication
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class AuthController extends \GO\Core\Controller\AbstractController {

	protected function authenticate() {
		return true;
	}

	/**
	 * Logs the current user out.
	 */
	protected function actionLogout() {

		App::session()->end();

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

		$response = [
			'success' => $user !== false
		];

		if ($response['success']) {
			//todo remember for different clients
			if (!empty(App::request()->payload['remember'])) {
				Token::generateSeries($user->id);
			}
		}

		return $this->renderJson($response);
	}

	public function actionIsLoggedIn() {
		$user = User::current();

		$response = [
			'success' => $user !== false
		];

		return $this->renderJson($response);
	}

}

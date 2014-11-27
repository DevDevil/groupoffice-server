<?php
namespace Intermesh\Modules\Auth\Controller;

use Intermesh\Modules\Auth\Model\Token;
use Intermesh\Modules\Auth\Model\User;
use Intermesh\Core\App;

/**
 * The controller that handles authentication
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class AuthController extends \Intermesh\Core\Controller\AbstractRESTController{
	
	protected function authenticate() {
		return true;
	}

	/**
	 * Logs the current user out.
	 */
	protected function httpDelete(){

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
	 *	"username": "user",
	 *	"password": "secret"
	 * }
	 * </code>
	 *
	 * @returns JSON {"userId": "Current ID of user", "securityToken": "token required in each request"}
	 */
	public function httpPost(){
		
		
		$user = User::login(App::request()->payload['username'], App::request()->payload['password'], true);
		
		$response = [
				'success'=>$user!==false
		];

		if($response['success']){	
			//todo remember for different clients
			if(App::request()->payload['remember']){				
				Token::generateSeries($user->id);				
			}			
		}
		
		return $this->renderJson($response);

		
	}
}
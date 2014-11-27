<?php
namespace Intermesh\Modules\Auth\Model;

use DateInterval;
use DateTime;
use Exception;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\Column;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Core\Util\String;

/**
 * Tokens for remembering a login
 * 
 * 
 * 1. When the user successfully logs in with Remember Me checked, a authorization header is issued in addition to the standard session management cookie.
 * 2. The login cookie contains the a series identifier, and a token. The series and token are unguessable random numbers from a suitably large space. All three are stored together in a database table.
 * 3. When a non-logged-in user visits the site and presents a authorization header, the series, and token are looked up in the database.
 * 4. If found, the user is considered authenticated. The used token is removed from the database. A new token is generated, stored in database with the username and the same series identifier, and a new authorization header containing the series and token is issued to the user.
 * 5. If the username and series are present but the token does not match, a theft is assumed. The user receives a strongly worded warning and all of the user's remembered sessions are deleted.
 * 6. If the username and series are not present, the authorization header is ignored.
 * 
 * @property int $id
 * @property int $userId
 * @property string $series Stays the same each time the remember token is used.
 * @property string $token Changes every time the token is used.
 * @property string $expiresAt
 * 
 * @property User $user
 * 
 * @link http://jaspan.com/improved_persistent_login_cookie_best_practice
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Token extends AbstractRecord {

	/**
	 * A date interval for the lifetime of a token
	 * 
	 * @link http://php.net/manual/en/dateinterval.construct.php
	 */
	const LIFETIME = 'P7D';

	protected static function defineRelations(RelationFactory $r) {
		return array(
			$r->belongsTo('user', User::className(), 'userId')
		);
	}

	/**
	 * Generate a new token series when a user authenticates successfully and
	 * enabled "remember me".
	 * 
	 * @return Token
	 */
	public static function generateSeries($userId) {
		$token = new Token();
		$token->userId = $userId;
		$token->save();

		$token->series = $token->id . String::random(20, 'a-z,A-Z,1-9,!-)');
		$token->generateToken();
		$token->save();

		return $token;
	}

	/**
	 * Generates a new token. Should be called for new tokens and after succesfull use of the token.
	 * It set's the new token as a HTTP header so clients can store it.
	 * 
	 * @return string
	 */
	public function generateToken() {
		$this->token = String::random(20, 'a-z,A-Z,1-9,!-)');

		$expireDate = new DateTime();
		$expireDate->add(new DateInterval(Token::LIFETIME));

		$this->expiresAt = $expireDate->format(Column::DATETIME_API_FORMAT);


		header('Authorization: Token ' . $this->series . ' ' . $this->token);

		//clean garbage in 10% of the logins
		if (rand(1, 10) === 1) {
			$this->_collectGarbage();
		}

		return $this->token;
	}

	private function _collectGarbage() {
		$tokens = Token::find(['<=', ['expiresAt' => gmdate('Y-m-d H:i:s', time())]]);

		foreach ($tokens as $token) {
			$token->delete();
		}
	}	
	
	/**
	 * Get's the token used for reminding authorization on the client
	 * 
	 * @return array|bool eg. array('series' => '23432432', 'token' => '2345dsanh32q')
	 */
	private static function _getAuthorizationToken(){
		
		if(PHP_SAPI === 'cli'){
			return false;
		}
		
		$headers = apache_request_headers();
		if(!isset($headers['Authorization'])){
			return false;
		}

		$matches = array();
		preg_match('/Token (.*) (.*)/', $headers['Authorization'], $matches);
		if(isset($matches[1]) && isset($matches[2])){
			return array('series'=>$matches[1], 'token'=>$matches[2]);
		}else
		{
			return false;
		}
	}

	/**
	 * Logs in with a token. If a user has previously logged in with 
	 * "remember me" enabled an authorizationToken is stored in localstorage of 
	 * the client. The client can send the header: 
	 * 
	 * Authorization: Token SERIES TOKENSTR
	 * 
	 * This is used to authenticate the user. And when it's successfull the 
	 * server will return a new token in the same type of header. The client
	 * must store this header for the next time an unauthenticated user accesses
	 * the server.
	 * 
	 * @return boolean|User
	 * @throws Exception
	 */
	public static function loginWithToken() {
		
		$tokenHeader = self::_getAuthorizationToken();
		if(!$tokenHeader){
			return false;
		}
		
		$token = Token::find(['series' => $tokenHeader['series']])->single();		

		if (!$token) {
			return false;
		}		

		if ($token->token === $tokenHeader['token']) {

			$token->user->setCurrent();
			
			$token->user->loginCount++;
			$token->user->save();
			
			$token->generateToken();
			$token->save();

			return $token->user;
		} else {
			//Theft assumed!
			foreach ($token->user->sessions as $session) {
				$session->delete();
			}

			foreach ($token->user->tokens as $token) {
				$token->delete();
			}

			throw new Exception("Token theft!");
		}
	}
}
<?php
namespace GO\Core\Auth\Browser\Model;

use DateInterval;
use DateTime;
use Exception;
use GO\Core\App;
use GO\Core\Auth\Model\User;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Column;

/**
 * The Token model
 *
 * @property string $accessToken
 * @property string $XSRFToken
 * @property string $userId
 * @property string $expiresAt
 *
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Token extends AbstractRecord {
	
	
	/**
	 * You can disable this in development environments where you want to be able
	 * to easily test requests.
	 * 
	 * @var boolean 
	 */
	public $checkXSRFToken = true;
	
	public static function primaryKeyColumn() {
		return 'accessToken';
	}
	
	/**
	 * A date interval for the lifetime of a token
	 * 
	 * @link http://php.net/manual/en/dateinterval.construct.php
	 */
	const LIFETIME = 'P1D';

	protected static function defineRelations() {		
		self::belongsTo('user', User::className(), 'userId');		
	}
	
	protected static function defineDefaultAttributes() {
		
		$expireDate = new DateTime();
		$expireDate->add(new DateInterval(Token::LIFETIME));

		
		return [
			'accessToken' => self::generateToken(),
			'XSRFToken' => self::generateToken(),
			'expiresAt' => $expireDate->format(Column::DATETIME_DATABASE_FORMAT)
		];
		
	}
	
	private static function generateToken(){
		if (function_exists('mcrypt_create_iv')) {
            $randomData = mcrypt_create_iv(20, MCRYPT_DEV_URANDOM);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randomData = openssl_random_pseudo_bytes(20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
		
		throw new Exception("We need mcrypt or openssl support in PHP!");
	}
	
	public function save() {
		
		$ret = parent::save();
		
		if($ret) {
			
			//clean garbage in 10% of the logins
			if (rand(1, 10) === 1) {
				$this->_collectGarbage();
			}
		}
		
		return $ret;
	}
	
	private function _collectGarbage() {
		$tokens = Token::find(['<=', ['expiresAt' => gmdate('Y-m-d H:i:s', time())]]);

		foreach ($tokens as $token) {
			$token->delete();
		}
	}
	
	
	public function setCoookies() {				
		//Should be httpOnly so XSS exploits can't access this token
		setcookie('accessToken', $this->accessToken, 0, null, null, false, true);
		
		//XSRF is NOT httpOnly because it has to be added by the browser as a header
		setcookie('XSRFToken', $this->XSRFToken, 0, null, null, false, false);
	}
	
	public function unsetCookies(){
		
		//Should be httpOnly so XSS exploits can't access this token
		setcookie('accessToken', NULL, 0, null, null, false, true);
		
		//XSRF is NOT httpOnly because it has to be added by the browser as a header
		setcookie('XSRFToken', NULL, 0, null, null, false, false);
	}
	
	
	private static function requestXSRFToken(){
		if(isset($_GET['XSRFToken'])) {
			return $_GET['XSRFToken'];
		}
		
		if(isset(App::request()->headers['X-XSRFToken'])) {
			return App::request()->headers['X-XSRFToken'];
		}
		
		return false;
	}
	
	
	/**
	 * Get the user by token cookie
	 * 
	 * @return boolean|self
	 */
	public static function findByCookie($checkXSRFToken = true){
		
		if(!isset($_COOKIE['accessToken'])) {
			return false;
		}
		
		
		$token = Token::findByPk($_COOKIE['accessToken']);
		
		if(!$token) {
			return false;
		}
		
				
		if($checkXSRFToken  && $token->checkXSRFToken) {
			
			if(self::requestXSRFToken() != $token->XSRFToken) {
				throw new Exception("XSRFToken doesn't match!");
			}
		}
		
		//remove cookie as header has been set.
		//Small security improvement as this token will not be accessible trough document.cookies anymore.
		//It's still somewhere in javascript but a little bit harder to get.
//		if(isset($_COOKIE['XSRFToken'])) {
//			setcookie('XSRFToken', null, 0, '/', $_SERVER['HTTP_HOST'], false, false);
//		}
		
		return $token;
	}


}
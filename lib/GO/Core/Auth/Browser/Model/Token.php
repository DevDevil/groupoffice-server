<?php
namespace GO\Core\Auth\Browser\Model;

use DateInterval;
use DateTime;
use Exception;
use GO\Core\Auth\Model\User;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Column;

/**
 * The Token model
 *
 * @property string $accessToken
 * @property string $XSRFToken
 * @property string $userId
 * @property string $expires
 *
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Token extends AbstractRecord {
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
			$this->sendCoookies();
		}
		
		return $ret;
	}
	
	public function delete() {
		
		$ret =  parent::delete();
		
		$this->accessToken = null;
		$this->XSRFToken = null;
		
		$this->sendCoookies();
		
		return $ret;
	}
	
	public function sendCoookies() {
		
		//$cookiePath = dirname($_SERVER['SCRIPT_NAME']);
		$cookieDomain = $_SERVER['HTTP_HOST'];
		
		//Should be httpOnly so XSS exploits can't access this token
		setcookie('accessToken', $this->accessToken, 0, '/', $cookieDomain, false, true);
		
		//XSRF is NOT httpOnly because it has to be added by the browser as a header
		setcookie('XSRFToken', $this->XSRFToken, 0, '/', $cookieDomain, false, false);
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
		
		
		if($checkXSRFToken && !isset(\GO\Core\App::request()->headers['X-XSRFToken'])){
			return false;
		}
		
		$token = Token::findByPk($_COOKIE['accessToken']);
		
		if(!$token) {
			return false;
		}
		
		if($checkXSRFToken && $token->XSRFToken != \GO\Core\App::request()->headers['X-XSRFToken']) {
			return false;
		}
		
		//remove cookie as header has been set.
		//Small security improvement as this token will not be accessible trough document.cookies anymore.
		//It's still somewhere in javascript but a little bit harder to get.
		if(isset($_COOKIE['XSRFToken'])) {
			setcookie('XSRFToken', null, 0, '/', $_SERVER['HTTP_HOST'], false, false);
		}
		
		return $token;
	}


}
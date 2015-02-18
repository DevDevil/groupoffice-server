<?php

namespace GO\Core\Auth\Model;

use DateTime;
use GO\Core\App;
use GO\Core\Db\AbstractRecord;

use GO\Core\Db\SoftDeleteTrait;
use GO\Core\Model\Session;
use GO\Core\Validate\ValidatePassword;
use GO\Modules\Contacts\Model\Contact;

/**
 * User model
 *
 * @property int $id Primary key of the model. 
 * @property int $enabled Disables the user from logging in
 * @property string $username 
 * @property string $password
 * @property string $digest Digest of the password used for digest auth. (Deprecated?)
 * @property string $email E-mail address of the user. The system uses this for notifications.
 * @property string $createdAt
 * @property string $modifiedAt
 * 
 * @property Contact $contact
 *
 * @property Role[] $roles The roles of the user is a member off.
 * @property Role $role The role of the user. Every user get's it's own role for sharing.
 * @property Session[] $sessions The sessions of the user.
 * @property Token[] $tokens The authentication tokens of the user.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class User extends AbstractRecord {
	
	use SoftDeleteTrait {
        delete as softDelete;
    }

	
	/**
	 * Non admin users must supply the currentPassword attribute when changing
	 * the password.
	 * 
	 * @var string 
	 */
	public $currentPassword;
	
	
	private static $currentUser;
	

	/**
	 *
	 * @inheritdoc
	 */
	protected static function defineValidationRules() {

		self::getColumn('username')->required = true;

		return [
				new ValidatePassword('password')
		];
	}

	/**
	 *
	 * @inheritdoc
	 */
	public static function defineRelations() {		
		
		self::hasMany('roles', Role::className(), "userId")
				->via(UserRole::className());
		
		self::hasMany('userRole', UserRole::className(), "userId");
		self::hasOne('role', Role::className(), 'userId');
		self::hasMany('sessions', Session::className(), "userId");
		self::hasMany('tokens', Token::className(), "userId");			
		self::hasOne('contact', Contact::className(), 'userId');		
	}
	
//	public function __construct() {
//		parent::__construct();
//		
//		if($this->getIsNew()){
//			$this->contact = new Contact();
//		}
//	}

	/**
	 * Get the current logged in user.
	 * 
	 * If no user is logged in authorization by a remember me authorization header is attempted.
	 *
	 * @return self|boolean
	 */
	public static function current() {
		if (!isset(self::$currentUser)) {	
			
			$basic = new \GO\Core\Auth\Provider\Basic();
			
			self::$currentUser = $basic->getUser();
			
			if(self::$currentUser) {
				return self::$currentUser;
			}
			
			//$oauth2Provider = new \GO\Core\Auth\Oauth2\Provider();
			//self::$currentUser = $oauth2Provider->getUser();
			
			$token = \GO\Core\Auth\Browser\Model\Token::findByCookie();
			
			if($token) {			
				self::$currentUser = $token->user;
			}
			
			//self::$currentUser = Token::loginWithToken();			
		}

		return self::$currentUser;
	}
	

	/**
	 * Logs a user in.
	 *
	 * @param string $username
	 * @param string $password
	 * @return User|bool
	 */
	public static function login($username, $password, $count = true) {
		$user = User::find(['username' => $username])->single();
		
		$success = true;

		if (!$user) {
			$success = false;
		} elseif (!$user->enabled) {
			App::debug("LOGIN: User " . $username . " is disabled");
			$success = false;
		} elseif (!$user->checkPassword($password)) {
			App::debug("LOGIN: Incorrect password for " . $username);
			$success = false;
		}

		$str = "LOGIN ";
		$str .= $success ? "SUCCESS" : "FAILED";
		$str .= " for user: \"" . $username . "\" from IP: ";
		
		if (isset($_SERVER['REMOTE_ADDR'])){
			$str .= $_SERVER['REMOTE_ADDR'];
		}else{
			$str .= 'unknown';
		}

		App::debug($str);

		if (!$success) {
			return false;
		} else {
			
			if($count) {
				$user->loginCount++;
				$user->lastLogin = new DateTime();
				$user->save();
			}

			$user->setCurrent();

			return $user;
		}
	}
	
	/**
	 * Set this user to the current logged in session user
	 * 
	 * @return bool|self
	 */
	public function setCurrent(){
//		App::session()->regenerateId();

		//Store the sessionId in the user table so we can see who's online.
//		$sessionModel = App::session()->getModel();
//		$sessionModel->userId=$this->id;
//		$sessionModel->save();
		
		self::$currentUser = $this;
	}
	
	
	protected function setAttribute($name, $value) {
		parent::setAttribute($name, $value);
		
		if($name == 'password'){
			$this->digest = md5($this->username . ":" . App::config()->productName . ":" . $this->password);
		}
	}
	
	public function validate() {
		if(parent::validate()){
			if(!empty($this->password) && $this->isModified('password')){
				
				$salt = base64_encode(mcrypt_create_iv(24, MCRYPT_DEV_URANDOM));
				$this->password = crypt($this->password, $salt);
			}
			return true;
		}  else {
			return false;
		}
	}

	/**
	 *
	 * @inheritdoc
	 */
	public function save() {

		$wasNew = $this->isNew();

		$success = parent::save();

		if ($success && $wasNew) {

			//Create a role for this user and add the user to this role.
			$role = new Role();
			$role->userId = $this->id;
			$role->name = $this->username;
			$role->save();

			$ur = new UserRole();
			$ur->userId = $this->id;
			$ur->roleId = $role->id;
			$ur->save();

			//add this user to the everyone role
			$ur = new UserRole();
			$ur->userId = $this->id;
			$ur->roleId = Role::findEveryoneRole()->id;
			$ur->save();
		}

		return $success;
	}

	/**
	 * @inheritdoc
	 */
	public function delete() {
		if ($this->id === 1) {
			$this->setValidationError('id', 'adminDeleteForbidden');
			return false;
		} else {
			return $this->softDelete();
		}
	}

	public function getAttributes(array $returnAttributes = []) {
		$attr = parent::getAttributes($returnAttributes);
		//protect password
		$attr['password'] = $attr['digest'] = "";

		return $attr;
	}

	/**
	 * Check if the password is correct for this user.
	 *
	 * @param string $password
	 * @return boolean
	 */
	public function checkPassword($password) {
		
		$currentPassword = $this->isModified('password') ? $this->getOldAttributeValue('password') : $this->password;
		
		return crypt($password, $currentPassword) === $currentPassword;
	}

	/**
	 * Check if this user is in the admins role
	 *
	 * @return bool
	 */
	public function isAdmin() {

		$ur = UserRole::findByPk(['userId' => $this->id, 'roleId' => Role::adminRoleId]);

		return $ur !== false;
	}
}

<?php

namespace Intermesh\Modules\Auth\Model;

use DateTime;
use Intermesh\Core\App;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Core\Db\SoftDeleteTrait;
use Intermesh\Core\Model\Session;
use Intermesh\Core\Validate\ValidatePassword;
use Intermesh\Modules\Contacts\Model\Contact;

/**
 * User model
 *
 * @property int $id
 * @property int $enabled
 * @property string $username
 * @property string $password
 * @property string $digest
 * @property string $email
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
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class User extends AbstractRecord {
	
	use SoftDeleteTrait {
        delete as softDelete;
    }

	/**
	 * Password confirmation when changing
	 *
	 * @var string
	 */
	public $passwordConfirm;
	
	
	/**
	 * Non admin users must supply the currentPassword attribute when changing
	 * the password.
	 * 
	 * @var string 
	 */
	public $currentPassword;

	/**
	 *
	 * @inheritdoc
	 */
	protected static function defineValidationRules() {

		self::getColumn('username')->required = true;

		return [
				new ValidatePassword('password', 'passwordConfirm')
		];
	}

	/**
	 *
	 * @inheritdoc
	 */
	public static function defineRelations(RelationFactory $r) {
		return array(
			$r->manyMany('roles', Role::className(), UserRole::className(), "userId"),
			$r->hasMany('userRole', UserRole::className(), "userId"),
			$r->hasOne('role', Role::className(), 'userId'),
			$r->hasMany('sessions', Session::className(), "userId"),
			$r->hasMany('tokens', Token::className(), "userId"),
			
			$r->hasOne('contact', Contact::className(), 'userId')->autoCreate()
		);
	}

	/**
	 * Get the current logged in user.
	 * 
	 * If no user is logged in authorization by a remember me authorization header is attempted.
	 *
	 * @return self|boolean
	 */
	public static function current() {
		if (empty(App::session()['userId'])) {		
			$user = Token::loginWithToken();
			if($user){
				return $user;
			}else
			{
				return false;
			}
		}

		return User::findByPk(App::session()['userId']);
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
		$sessionModel = App::session()->getModel();
		$sessionModel->userId=$this->id;
		$sessionModel->save();
		
		App::session()['userId'] = $this->id;
	}
	
	
	protected function setAttribute($name, $value) {
		parent::setAttribute($name, $value);
		
		if($name == 'password'){
			$this->digest = md5($this->username . ":" . App::config()->productName . ":" . $this->password);
		}
	}
	
	public function validate() {
		if(parent::validate()){
			if(!empty($this->password)){
				$this->password = crypt($this->password);
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
		
		
	
		if ($this->isModified('password')) {
			
			if(!User::current()->isAdmin() && !$this->checkPassword($this->currentPassword)){
				$this->setValidationError('currentPassword', 'wrongPassword');
				
				return false;
			}
			
			
		}

		$wasNew = $this->getIsNew();

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
<?php
namespace Intermesh\Core\Validate;

use Intermesh\Core\Model;

/**
 * Validates an email attribute of the ActiveRecord
 * 
 * eg. in ActiveRecord do:
 * 
 * <p>eg. in ActiveRecord do:</p>
 * 
 * <code>
 * protected static function defineValidationRules() {
 *	
 *		self::getColumn('username')->required=true;
 *		
 *		return array(
 *				new ValidateEmail("email"),
 *				new ValidateUnique('email'),
 *				new ValidateUnique('username'),
 *        new ValidatePassword('password', 'passwordConfirm') //Also encrypts it on success
 *		);
 *	}
 * </code>
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ValidateEmail extends AbstractValidationRule {
	
	private static $_regex = "/^[a-z0-9\._\-+\&]+@[a-z0-9\.\-_]+\.[a-z]{2,6}$/i";
	
	public function validate(Model $model) {
		if(self::check($model->{$this->getId()})){
			return true;
		}else
		{
			$this->errorCode='emailInvalid';
		}
	}
	
	public static function check($email){
		return preg_match(self::$_regex, $email);
	}
}
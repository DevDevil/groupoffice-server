<?php

namespace GO\Core\Validate;

use GO\Core\Db\Query;
use GO\Core\AbstractModel;

/**
 * Checks if the attribute is unique. Can also validate it in combination with other columns
 * 
 * Do not set this yourself. Just define a unique key on the database and it will
 * be generated automatically. Can also validate it in combination with other columns.
 * 
 * If for some reason you can't do this you can set it yourself:
 * 
 * <p>eg. in ActiveRecord do:</p>
 * 
 * <code>
 * protected static function defineValidationRules() {
 * 	
 * 		self::getColumn('username')->required=true;
 * 		
 * 		return array(
 * 				new ValidateEmail("email"),
 * 				new ValidateUnique('email'),
 * 				new ValidateUnique('username'),
 *        new ValidatePassword('password', 'passwordConfirm') //Also encrypts it on success
 * 		);
 * 	}
 * </code>
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ValidateUnique extends AbstractValidationRule {

	private $_relatedColumns = [];

	/**
	 * Validate a unique value of this column in combination with other columns.
	 * 
	 * @param array $relatedColumns
	 */
	public function setRelatedColumns(array $relatedColumns) {
		$this->_relatedColumns = $relatedColumns;
	}

	public function validate(AbstractModel $model) {
		$relatedColumns = $this->_relatedColumns;

		if(!in_array($this->getId(), $relatedColumns)){
			$relatedColumns[] = $this->getId();
		}


//		$modified = false;
//		foreach ($relatedColumns as $relatedAttribute) {
//			if ($model->isModified($relatedAttribute)) {
//				$modified = true;
//			}
//		}
//
//		if ($modified) {
			$query = Query::newInstance();

			foreach ($relatedColumns as $f) {
//				if (isset($model->{$f})) {
					$query->andWhere([$f => $model->{$f}]);
//				}
			}

			if (!$model->isNew()) {
				$query->andWhere(['!=', [$model->primaryKeyColumn() => $model->{$model->primaryKeyColumn()}]]);
			}

			if(method_exists($model, 'findWithDeleted')){				
				//when using GO\Core\Db\SoftDeleteTrait we must check deleted items too.				
				$existing = $model->findWithDeleted($query)->single();
			}else
			{
				$existing = $model->find($query)->single();
			}

			if ($existing) {

				$this->errorCode = 'unique';
				$this->errorInfo = ['relatedColumns' => $this->_relatedColumns, 'existingAttributes' => $existing->getAttributes()];

				return false;
			}
//		}

		return true;
	}

}

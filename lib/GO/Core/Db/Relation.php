<?php

namespace GO\Core\Db;

use Exception;

/**
 * A relation defines and queries related models
 *
 * There are 4 types:
 *
 * 1. Belongs to. eg. A contact belongs to an address book
 * 2. Has one eg. A user has one settings model.
 * 3. Has Many eg. A user has many address books
 * 4. Many many eg. Users and roles. User has many roles and role has many users.
 *
 * eg. $model->relation automatically fetches the related model.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Relation {

	/**
	 * This relation type means that the relation is single and this model's primary
	 * key can be found in the remote model.
	 *
	 * User->Addressbook for example where user_id is in the addressbook table.
	 */
	const TYPE_BELONGS_TO = 1; // 1:1

	/**
	 * This relation type is used when this model has many related models.
	 *
	 * Addressbook->contacts() for example.
	 */
	const TYPE_HAS_MANY = 2; // 1:n

	/**
	 * This relation type means that the relation is single and this model's primary
	 * key can be found in the remote model.
	 *
	 * User->Addressbook for example where user_id is in the addressbook table.
	 */
	const TYPE_HAS_ONE = 3; // 1:1

	/**
	 * Cascade delete relations. 
	 * 
	 * Only works on has_one and has_many relations. {@see setDeleteAction()}
	 */
	const DELETE_CASCADE = 2;

	/**
	 * Restrict delete relations. 
	 * 
	 * Only works on has_one and has_many relations. {@see setDeleteAction()}
	 */
	const DELETE_RESTRICT = 1;

	/**
	 * Don't do anything on delete (Default action).
	 * 
	 * {@see setDeleteAction()}
	 */
	const DELETE_NO_ACTION = 0;

	/**
	 * Name of the relation
	 * 
	 * @var string 
	 */
	protected $name;

	/**
	 * One of TYPE_* constants of the Relation class
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Class name of the related model
	 *
	 * @var string
	 */
	protected $modelName;

	/**
	 * Class name of the related model
	 *
	 * @var string
	 */
	protected $relatedModelName;

	/**
	 * Key column of the relation
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * Foreign key of the relation
	 *
	 * @var string
	 */
	protected $foreignKey;

	/**
	 * The delete action:
	 *
	 * Relation::DELETE_RESTRICT
	 * Relation::DELETE_CASCADE
	 * 
	 * @var int 
	 */
	public $deleteAction = self::DELETE_NO_ACTION;

	/**
	 * Query object for this relation
	 * 
	 * @var Query 
	 */
	public $query;

	/**
	 * Set to the failing model when saving a relation failed
	 * 
	 * @var AbstractRecord 
	 */
	protected $failedRelatedModel;

	/**
	 * Auto create a new relation model when creating a new model. 
	 * 
	 * This only works for TYPE_HAS_MANY and TYPE_HAS_ONE. This can be useful
	 * for loading a default email address input for a contact for example.
	 * 
	 * @var bool 
	 */
//	public $autoCreate = false;

	/**
	 *
	 * @param string $type One of TYPE_* constants of the Relation class
	 * @param string $modelName Class name of the model that has this relation
	 * @param string $relatedModel Class name of the related model
	 * @param string $key Key column of the relation
	 * @param string $foreignKey Foreign key of the relation
	 */
	public function __construct($name, $type, $modelName, $relatedModelName, $key, $foreignKey = null) {

		$this->name = $name;
		$this->type = $type;
		$this->modelName = $modelName;
		$this->relatedModelName = $relatedModelName;
		$this->key = $key;
		$this->foreignKey = $foreignKey;
	}

	/**
	 * Set extra query options
	 * 
	 * @param Query $query
	 * @return \self
	 */
	public function setQuery(Query $query) {
		$this->query = $query;

		return $this;
	}

	/**
	 * Get the name of the relation
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set the delete action
	 *
	 * You can use:
	 * 
	 * Relation::DELETE_RESTRICT
	 * Relation::DELETE_CASCADE
	 * 
	 * Note that it's better to let the database handle cascade deletes as it is
	 * much faster. However in some cases it can be necessary to make the framework
	 * handle cascading if you want to program some extra logic on delete.
	 *
	 * @param int $action
	 * @return \self
	 */
	public function setDeleteAction($action) {
		$this->deleteAction = $action;

		return $this;
	}

	/**
	 * Class name of the related model
	 *
	 * @return string
	 */
	public function getRelatedModelName() {
		return $this->relatedModelName;
	}

	/**
	 * Get the foreign key
	 *
	 * @return string
	 */
	public function getForeignKey() {
		return $this->foreignKey;
	}

	/**
	 * Get the key
	 *
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Check the type of this relation
	 *
	 * @param int $type One of the TYPE_ constants of this class.
	 * @return bool
	 */
	public function isA($type) {
		return $type === $this->type;
	}

	/**
	 * Set's the link model on a many many relation.
	 *
	 * Eg. a user has a many many relation with roles. The link model
	 *
	 * is UserRole in this case. It connects the User and Role models.
	 * 
	 * <code>
	 * self::hasMany('roles', Role::className(), "userId", "id")
	 *		->via(UserRole::className(), 'roleId', "id");
	 * </code>
	 * 
	 * The values are all supplied for clarity in the example above. Some of 
	 * them can be auto detected:
	 * 
	 * <code>
	 * self::hasMany('roles', Role::className(), "userId")
	 *			->via(UserRole::className());
	 * </code> 
	 * 
	 * hasMany userId = foreignKey in via link model.
	 * hasMany id = key in User model that relates to UserRole.userId in link model
	 * via roleId = key in via link model UserRole.roleId
	 * via id = key in Role model that relates to roleId
	 *
	 * @param string $linkModelClassName The name of the link model eg. authRoleUser
	 * @param string $key The column in the link model that holds the foreignKey. eg. "roleId. Defaults to the other primary key that is not this->foreignKey
	 * @param string $foreignKey The column in the target model that holds the value of the key. Defaults to the primary key.
	 * @return self
	 */
	public function via($linkModelClassName, $key = null, $foreignKey = null) {

		if ($this->type != self::TYPE_HAS_MANY) {
			throw new \Exception("via can only be used on hasMany relations");
		}

		$this->viaModelClassName = $linkModelClassName;
		
		if(!isset($key)){
			$primaryKeys = $linkModelClassName::primaryKeyColumn();
			if(!is_array($primaryKeys)){
				throw new \Exception ("Fatal error: Primary key of linkModel '".$linkModelClassName."' should be an array if used in a many many relation.");
			}

			$key = $primaryKeys[0] == $this->foreignKey ? $primaryKeys[1] : $primaryKeys[0];
		}
		
		
		$this->viaKey = $key;
		
		if(!isset($foreignKey)){
			$mn = $this->modelName;
			$foreignKey = $mn::primaryKeyColumn();
		}
		
		$this->viaForeignKey = $foreignKey;
	
		return $this;
	}
	
	
	/**
	 * Get the class name of the link model if set.
	 * @return string
	 */
	public function getVia(){
		return $this->viaModelClassName;
	}

	/**
	 * The name of the link model eg. authRoleUser
	 *
	 * @var string
	 */
	private $viaModelClassName;
	
	/**
	 * eg. UserRole.roleId in user -> roles
	 * 
	 * @var string 
	 */
	private $viaKey;
	
	/**
	 * eg. Role.id in user -> roles
	 * 
	 * @var string
	 */
	private $viaForeignKey;

	private function _setHasOne(AbstractRecord $model, &$value) {
		
		//eg User has one Contact
		
		//Contact
		$hasOne = $this->_createOrFindHasMany($model, $value);

		//Contact->userId = User->id		
		$hasOne->{$this->foreignKey} = $model->{$this->key};		
		
		if (!$hasOne->save()) {
			$this->failedRelatedModel = $hasOne;
			return false;
		} else {
			return true;
		}
	}

	/**
	 * will be set immediately in the model. save not needed here.
	 * 
	 * @param \GO\Core\Db\AbstractRecord $model
	 * @param type $value
	 * @return boolean
	 * @throws Exception
	 */
	private function _setBelongsTo(AbstractRecord $model, &$value) {
		if (is_array($value)) {
			if (!isset($value[$this->foreignKey])) {

				//Creates belongs to on the fly.

				$rmn = $this->relatedModelName;
				$belongsTo = new $rmn;
				$belongsTo->setAttributes($value);

				if (!$belongsTo->save()) {

					$this->failedRelatedModel = $belongsTo;

					throw new Exception("Could not set belongs to relation because it didn't validate: " . var_export($belongsTo->getValidationErrors(), true));
				} else {

					$model->{$this->key} = $belongsTo->{$this->foreignKey};
				}
			} else {
				$model->{$this->key} = $value[$this->foreignKey];
			}
		} elseif (is_a($value, AbstractRecord::className())) {
			$model->{$this->key} = $value->{$this->foreignKey};
		} else {
			throw new Exception("Invalid value in Relation::set(): " . var_export($value, true));
		}

		return true;
	}

	private function _setHasMany($model, &$value) {
		foreach ($value as &$hasMany) {
			$hasMany = $this->_createOrFindHasMany($model, $hasMany);

			if (!$hasMany->save()) {

				//return failed model
				$value = [$hasMany];

				$this->failedRelatedModel = $hasMany;
				return false;
			}
		}
		return true;
	}

	private function _setManyMany(AbstractRecord $model, &$value) {

		$linkModelName = $this->viaModelClassName;

		$relatedModelName = $this->relatedModelName;

		foreach ($value as $record) {

			if (is_array($record)) {
				//Array of attributes of the related model eg. Role

				$delete = !empty($record['markDeleted']);

				if (!isset($record[$this->viaForeignKey])) {

					if ($delete) {
						//The client created a new one but also deleted it. Skip it.
						continue;
					}
					
					//create new (eg. Role) model on the fly					
					$relatedModel = new $relatedModelName;
					$relatedModel->setAttributes($record);
					if (!$relatedModel->save()) {
						return false;
					}

					$foreignKey = $relatedModel->{$this->viaForeignKey};
				} else {
					$foreignKey = $record[$this->viaForeignKey];
				}
			} elseif (is_a($record, AbstractRecord::className())) {
				
				//eg Role.id
				$foreignKey = $record->{$this->viaForeignKey};
				$delete = false;
			}

			//create link model
			$primaryKey = array(
				$this->viaKey => $foreignKey, //eg roleId
				$this->foreignKey => $model->{$this->key}); //eg userId

			
			
			$link = $linkModelName::findByPk($primaryKey);			
		
			if (!$link && !$delete) {
				$manyMany = $linkModelName::newInstance()
						->setAttributes($primaryKey);

				if (!$manyMany->save()) {
					
					var_dump($manyMany->getValidationErrors());
					
					$this->failedRelatedModel = $manyMany;
					return false;
				}
			} elseif ($link && $delete) {
				if (!$link->delete()) {
					return false;
				}
			}
		}

		return true;
	}
	
	/**
	 * Set's this relation of $model to $value. Don't use this method directly.
	 * ActiveRecord uses it for you when setting relations directly.
	 * 
	 * @param AbstractRecord $model
	 * @param mixed $value
	 * @return boolean
	 */
	public function set(AbstractRecord $model, &$value) {

		switch ($this->type) {

			case self::TYPE_HAS_ONE:
				return $this->_setHasOne($model, $value);

			case self::TYPE_BELONGS_TO:
				return $this->_setBelongsTo($model, $value);


			case self::TYPE_HAS_MANY:
				if (isset($this->viaModelClassName)) {
					return $this->_setManyMany($model, $value);
				} else {
					return $this->_setHasMany($model, $value);
				}
		}
	}

	private function _buildPk($primaryKeyColumn, $attributes) {
		if (is_array($primaryKeyColumn)) {
			$pk = [];

			foreach ($primaryKeyColumn as $col) {

				if (empty($attributes[$col])) {
					return false;
				}
				$pk[$col] = $attributes[$col];
			}

			return $pk;
		} else {
			return empty($attributes[$primaryKeyColumn]) ? false : $attributes[$primaryKeyColumn];
		}
	}

	private function _createOrFindHasMany(AbstractRecord $model, &$hasMany) {

		$rmn = $this->relatedModelName;
		$primaryKeyColumn = $rmn::primaryKeyColumn();

		if ($hasMany instanceof AbstractRecord) {
			$hasMany->{$this->foreignKey} = $model->{$this->key};
		} else {

			if (!is_array($hasMany)) {
				throw new \Exception("Should be an array: " . var_export($hasMany, true));
			}

			//It's an array of attributes of the has many related model
			$modelArray = $hasMany;

			//Set the foreign key
			$modelArray[$this->foreignKey] = $model->{$this->key};

			$pk = $this->_buildPk($primaryKeyColumn, $modelArray);
			$hasMany = $pk ? $rmn::findByPk($pk) : false;
			if (!$hasMany) {
				$hasMany = new $rmn;
			}

			$hasMany->setAttributes($modelArray);
		}

		return $hasMany;
	}

	/**
	 * Returns validation errors when setting a relation failed.
	 * 
	 * @return array
	 */
	public function getValidationErrors() {
		return isset($this->failedRelatedModel) ? $this->failedRelatedModel->getValidationErrors() : array();
	}

	/**
	 * Queries the database for the relation
	 *
	 * @param AbstractRecord $model The model that this relation belongs to.
	 * @param Criteria|Query $extraQuery Passed when calling a relation as a function with Query as single parameter.
	 * @return AbstractRecord[]
	 */
	public function find(AbstractRecord $model, Criteria $extraQuery = null) {

		if (empty($model->{$this->key})) {

			switch ($this->type) {
				case self::TYPE_HAS_ONE:
				case self::TYPE_BELONGS_TO:
					return null;

				default:
					return [];
			}
		}

		switch ($this->type) {

			case self::TYPE_HAS_ONE:
			case self::TYPE_BELONGS_TO:
				return $this->_findHasOne($model, $extraQuery);


			case self::TYPE_HAS_MANY:
				if (isset($this->viaModelClassName)) {
					return $this->_findManyMany($model, $extraQuery);
				} else {
					return $this->_findHasMany($model, $extraQuery);
				}
		}
	}

	private function _findHasOne(AbstractRecord $model, Criteria $extraQuery = null) {

		$value = $model->{$this->key};


		$query = isset($this->query) ? clone $this->query : Query::newInstance();
		$query->andWhere([$this->foreignKey => $value]);

		if (isset($extraQuery)) {
			$query->mergeWith($extraQuery);
		}

		$relatedModelName = $this->relatedModelName;

		$finder = $relatedModelName::find($query);
		$relation = $finder->single();
		return $relation ? $relation : null;
	}

	private function _findHasMany(AbstractRecord $model, Criteria $extraQuery = null) {

		$value = $model->{$this->key};


		$query = isset($this->query) ? clone $this->query : Query::newInstance();
		$query->andWhere([$this->foreignKey => $value]);

		if (isset($extraQuery)) {
			$query->mergeWith($extraQuery);
		}

		$relatedModelName = $this->relatedModelName;

		$finder = $relatedModelName::find($query);

		return $finder;
	}

	private function _findManyMany(AbstractRecord $model, Criteria $extraQuery = null) {

		$linkTableAlias = 'manyManyLink';
		
		//eg. Role
		$relatedModelName = $this->relatedModelName;

		$query = isset($this->query) ? clone $this->query : Query::newInstance();

		//join Role.id = UserRole.roleId				//role.id								//roleId
		
		$query->joinModel($this->viaModelClassName, $this->viaForeignKey, $linkTableAlias, $this->viaKey);

		//where				UserRole.userId = User.id
		$query->andWhere([$linkTableAlias . '.' . $this->foreignKey => $model->{$this->key}]);

		if (isset($extraQuery)) {
			$query->mergeWith($extraQuery);
		}
		
		//Role::find()
		return $relatedModelName::find($query);
	}

}

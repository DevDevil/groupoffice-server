<?php

namespace Intermesh\Core\Db;

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
	 * This relation type is used when this model has many related models.
	 * The relation makes use of a linked table that has a combined key of the related model and this model.
	 *
	 * Example use in the model class relationship array: 'users' => array('type'=>self::MANY_MANY, 'model'=>'GO\Base\Model\User', 'linkModel'=>'GO\Base\Model\UserGroups', 'field'=>'group_id', 'remoteField'=>'user_id'),
	 */
	const TYPE_MANY_MANY = 4; // n:n

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
	public $autoCreate = false;

	/**
	 *
	 * @param string $type One of TYPE_* constants of the Relation class
	 * @param string $modelName Class name of the model that has this relation
	 * @param string $relatedModel Class name of the related model
	 * @param string $key Key column of the relation
	 * @param string $foreignKey Foreign key of the relation
	 */
	public function __construct($name, $type, $modelName, $relatedModelName, $key, $foreignKey = 'id') {

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
	 * Auto create a new relation model when creating a new model. 
	 * 
	 * This only works for TYPE_HAS_MANY and TYPE_HAS_ONE. This can be useful
	 * for loading a default email address input for a contact for example.
	 * 
	 * @param Query $query
	 * @return \self
	 */
	public function autoCreate(){
		
		if($this->type != self::TYPE_HAS_MANY && $this->type != self::TYPE_HAS_ONE)
		{
			throw new Exception("Auto create only wirks with TYPE_HAS_MANY and TYPE_HAS_ONE");
		}
		
		$this->autoCreate = true;
		
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
				$hasOne = $this->_createOrFindHasMany($model, $value);

				$hasOne->{$this->foreignKey} = $model->{$this->key};
				if (!$hasOne->save()) {
					$this->failedRelatedModel = $hasOne;
					return false;
				} else {
					return true;
				}

			case self::TYPE_BELONGS_TO:
				//will be set immediately in the model. save not needed here.
				
				if(is_array($value)){
					if(!isset($value[$this->foreignKey])){
						$rmn = $this->relatedModelName;
						$belongsTo = new $rmn;
						$belongsTo->setAttributes($value);
					
						if(!$belongsTo->save()){							
							
							$this->failedRelatedModel = $belongsTo;
							
							throw new Exception("Could not set belongs to relation because it didn't validate: ".var_export($belongsTo->getValidationErrors(), true));
						}else
						{
														
							$model->{$this->key} = $belongsTo->{$this->foreignKey};
						}					
					}else
					{
						$model->{$this->key} = $value[$this->foreignKey];
					}
				}elseif(is_a($value, AbstractRecord::className()))
				{
					$model->{$this->key} = $value->{$this->foreignKey};
				}else
				{
					throw new Exception("Invalid value in Relation::set(): ".var_export($value, true));
				}
				
				
				return true;

			case self::TYPE_HAS_MANY:
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
	}

	private function _createOrFindHasMany(AbstractRecord $model, &$hasMany) {

		$rmn = $this->relatedModelName;
		$primaryKey = $rmn::primaryKeyColumn();

		if ($hasMany instanceof AbstractRecord) {
			$hasMany->{$this->foreignKey} = $model->{$this->key};
		} else {
			//It's an array of attributes of the has many related model
			$modelArray = $hasMany;

			$hasMany = !empty($modelArray['attributes'][$primaryKey]) ? $rmn::findByPk($modelArray['attributes'][$primaryKey]) : false;
			if(!$hasMany){
				$hasMany = new $rmn;
			}

			$hasMany->setAttributes($modelArray['attributes']);

			//Set the foreign key
			$hasMany->{$this->foreignKey} = $model->{$this->key};
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
		
	
		if(!isset($extraQuery) && $model->getIsNew() && $this->autoCreate){
			if($this->type === self::TYPE_HAS_ONE){
				return new $this->relatedModelName;
			}else
			{
				return [new $this->relatedModelName];
			}
		}

		$query = isset($this->query) ? clone $this->query : Query::newInstance();
		
		$value = $model->{$this->key};
		
//		if($this->name == 'company'){
////			var_dump($value);
//		}
		
		if(empty($value)){
			
			switch($this->type){
				case self::TYPE_HAS_ONE:
					case self::TYPE_BELONGS_TO:
					return null;
						
				default:
					return [];
			}
		}

		$query->andWhere([$this->foreignKey => $value]);

		if (isset($extraQuery)) {
			$query->mergeWith($extraQuery);
		}

		$relatedModelName = $this->relatedModelName;

		$finder = $relatedModelName::find($query);

		if ($this->type === self::TYPE_BELONGS_TO || $this->type === self::TYPE_HAS_ONE) {
			
			$relation = $finder->single();
			
			return $relation ? $relation : null;
		} else {
			return $finder;
		}
	}
}

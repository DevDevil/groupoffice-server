<?php

namespace Intermesh\Core\Db;

use Exception;
use Intermesh\Core\App;
use Intermesh\Core\Db\Exception\DeleteRestrict;
use Intermesh\Core\Model;
use Intermesh\Core\Util\String;
use Intermesh\Core\Validate\AbstractValidationRule;
use Intermesh\Core\Validate\ValidateUnique;
use Intermesh\Modules\Auth\Model\User;

/**
 * AbstractRecord class.
 *
 * Special columns
 * ---------------
 * 
 * 1. createdAt and modifiedAt
 * 
 * Database columns "createdAt" and "modifiedAt" are automatically filled. They
 * should be of type "DATETIME" in MySQL. *
 * All times should be stored in GMT. They are returned and set in the ISO 8601
 * Standard. Eg. 2014-07-22T16:10:15Z
 * 
 * 2. ownerUserId
 * 
 * Automatically filled with userId
 * 
 * 3. deleted
 * 
 * Reserved for soft deletion. The record must use {@see Intermesh\Core\Db\SoftDeleteTrait}.
 *
 * Basic usage
 * -----------
 *
 * <p>Create a new model:</p>
 * <code>
 * $user = new User();
 * $user->username="merijn";
 * $user->email="merijn@intermesh.nl";
 * $user->modifiedAt='2014-07-22T16:10:15Z'; //makes no sense but just for showing how to format the time.
 * $user->save();
 * </code>
 *
 * <p>Updating a model:</p>
 * <code>
 * $user = User::find(
 *  Query::newInstance()->where(['username' => 'merijn'])
 * )->single();
 *
 * if($user){
 *    $user->email="merijn@intermesh.nl";
 *    $user->save();
 * }
 * </code>
 *
 * <p>Find all users ({@see find()}):</p>
 * <code>
 * $users = User::find();
 * foreach($users as $user){
 *     echo $user->username.'<br />';
 * }
 * </code>
 * 
 * 
 * Relations
 * ---------
 * 
 * The AbstractRecord supports relational data. See the defineRelations() function on how to define them. There are 4 types:
 *
 * 1. Belongs to. eg. A contact belongs to an address book
 * 2. Has one eg. A user has one settings model.
 * 3. Has Many eg. A user has many address books
 * 4. Many many eg. Users and roles. User has many roles and role has many users.
 * 
 * 
 * To get the "roles" relation of a user simply do:
 * 
 * <code>
 * foreach($user->roles as $role){
 *    echo $role->name;
 * }
 * </code>
 * 
 * You can also set relations:
 * 
 * With models:
 * <code>
 * $user->roles = [$roleModel1, $roleModel2\; 
 * $user->save();
 * </code>
 * 
 * Or with arrays of attributes. (This is the API way when posting JSON):
 * <code>
 * $user->roles = [[['attributes' => ['roleId' => 1]), ['roleId' => 2]]; 
 * $user->save();
 * </code>
 * 
 * 
 * Traits used in Record models
 * ----------------------------
 * 
 * 1. {@see Intermesh\Modules\Auth\Model\RecordPermissionTrait}
 * 
 * With the Auth module you can implement permissions. You need to create a 
 * "roles" relation. This is a "hasMany" relation to a table that keeps
 * the primary key of the model and a "roleId" that links to the authRole model. 
 * Additionally you need to create some access booleans like readAccess, 
 * editAccess etc.
 * 
 * 2. {@see Intermesh\Core\Db\SoftDeleteTrait}
 * 
 * Enable soft deletion. When the user deletes a record it will only be marked
 * as deleted in a deleted boolean column.
 * 
 * 3. (@see Intermesh\Modules\CustomFields\Model\CustomFieldsTrait}
 * 
 * For creating a model that can hold custom fields.
 * 
 *
 * Useful tools
 * ------------
 * To generate a list of database properties go to:
 *
 * http://localhost/pocket-office/index.php?r=intermesh/devTools/model/databaseToProperties&modelName=\Intermesh\Modules\Auth\Model\User
 *
 * See the query object for available options for the find functions and the User object for an example implementation.
 *
 * @method \static single() single() When calling "ActiveRecord::find()" you can retrieve a single model by calling single.
 * @method \static all() all() {@see Finder::all()} When calling "ActiveRecord::find()" you can retrieve a all models by calling all. Avoid using this as it might use a lot of memory.
 * @method string buildSql() buildSql()
 *
 * This function is not really part of active record but of the Finder object that is returned by find.
 *
 * @see find()
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class AbstractRecord extends Model {
	
	/**
	 * When this is set to true the model will be deleted on save.
	 * Useful for saving relations.
	 * 
	 * @var bool 
	 */
	public $markDeleted=false;
	

	/**
	 * findByPk caches results here so queries are not made more than once.
	 *
	 * @var array
	 */
	private static $_findByPkCache = [];

	/**
	 * Validation rules
	 * @var array
	 */
	protected static $_validators = [];

	/**
	 * Indiciates that the ActiveRecord is being contructed by PDO.
	 * Used in setAttribute so it skips fancy features that we know will only
	 * cause overhead.
	 *
	 * @var boolean
	 */
	protected $loadingFromDatabase = true;

	/**
	 * True if this record doesn't exit in the database yet
	 * @var boolean
	 */
	private $_isNew = true;

	/**
	 * Holds all attributes for the database
	 *
	 * @var array
	 */
	private $_attributes =  [];

	/**
	 * Key value array of all modified attributes and their old values
	 *
	 * @var array
	 */
	private $_modifiedAttributes = [];

	/**
	 * All relations are only fetched once per request and stored in this static
	 * array
	 *
	 * @var array
	 */
	private static $_relations = [];

	/**
	 * When joinRelation is used in the findParams then all related attributes are fetched
	 * and stored in this array. When a relation is called it will construct the model
	 * from this array rather than querying the database.
	 *
	 * @var array
	 */
	private $_joinRelationAttr = [];

	/**
	 * Used to store relations that need to be saved after saving the model.
	 *
	 * @var array
	 */
	private $_saveRelations = [];
	
	
	/**
	 * When relations are set directly on the model we cache them in this array
	 * so we don't need to query them in the __get function.
	 * 
	 * @var array 
	 */
	private $_setRelations = [];

	/**
	 *
	 * @param boolean $isNew
	 */
	public function __construct($isNew = true) {

		parent::__construct();

		$this->_isNew = $isNew;

		if (!$this->_isNew) {
			$this->_cacheRelatedAttributes();

			$this->castDatabaseAttributes();
		} else {
			$this->_loadDefaultAttributes();
		}

		$this->loadingFromDatabase = false;
	}

	/**
	 * Mysql always returns strings. We want strict types in our model to clearly
	 * detect modifications
	 *
	 * @param array $columns
	 * @return void
	 */
	private function castDatabaseAttributes() {

		foreach ($this->getColumns() as $colName => $column) {
			if (isset($this->_attributes[$colName]) && $column->dbType) {
				switch ($column->dbType) {
					case 'int':
					case 'tinyint':
					case 'bigint':
						if ($column->length === 1) {
							//Boolean fields in mysql are listed at tinyint(1);
							$this->_attributes[$colName] = (bool) $this->_attributes[$colName];
						} else {
							// Use floatval because of ints greater then 32 bit? Problem with floatval that ints will set as modified attribute when saving.
							$this->_attributes[$colName] = intval($this->_attributes[$colName]);
						}
						break;

					case 'float':
					case 'double':
					case 'decimal':
						$this->_attributes[$colName] = floatval($this->_attributes[$colName]);
						break;
				}
			}
		}
	}

	/**
	 * Don't use this function directly. ActiveRecord will cache attributes when
	 * using joinRelation.
	 *
	 * @access private
	 * @param string $relationName
	 * @param array $attributes
	 */
	public function setJoinRelationCache($relationName, $attributes) {
		$this->_joinRelationAttr[$relationName] = $attributes;
	}

	/**
	 * When a model is joined on a find action and we need it for permissions, We
	 * select all the model attributes so we don't have to query it seperately later.
	 * eg. $contact->addressbook will work from the cache when it was already joined.
	 */
	private function _cacheRelatedAttributes() {
		foreach ($this->_attributes as $name => $value) {
			$arr = explode('@', $name);
			if (count($arr) > 1) {

				$cur = &$this->_joinRelationAttr;

				foreach ($arr as $part) {
					$cur = & $cur[$part];
					//$this->_relatedCache[$arr[0]][$arr[1]]=$value;
				}
				$cur = $value;

				unset($this->_attributes[$name]);
			}
		}
	}

	private function _loadDefaultAttributes() {

		$defaults = static::defineDefaultAttributes();

		foreach ($this->getColumns() as $colName => $column) {
			if (!isset($defaults[$colName])) {
				$defaults[$colName] = $column->default;
			}
		}

		if (array_key_exists('ownerUserId', $defaults) && empty($defaults['ownerUserId'])) {
			$defaults['ownerUserId'] = User::current() ? User::current()->id : 1;
		}

		$this->_attributes = array_merge($this->_attributes, $defaults);
	}

	/**
	 * Define default attributes for this model with a key value array
	 *
	 * <p>Example:</p>
	 * <code>
	 * protected static function defineDefaultAttributes() {
	 * 	return ['name'=>'Value'];
	 * }
	 * </code>
	 *
	 * @return array
	 */
	protected static function defineDefaultAttributes() {
		return [];
	}

	/**
	 * Return the table name to store these records in.
	 *
	 * Defaults to "appNameModelName".
	 *
	 * @return string
	 */
	public static function tableName() {
		$class = get_called_class();

		$parts = explode("\\", $class);

		$table = lcfirst($parts[2]) . $parts[4];

		return $table;
	}

	/**
	 * Get the database columns
	 *
	 * <p>Example:</p>
	 * <code>
	 * $columns = User::getColumns();
	 * </code>
	 *
	 * @return Column[] Array with column name as key
	 */
	public static function getColumns() {
		return Columns::getColumns(get_called_class());
	}

	/**
	 * Get the database column definition
	 *
	 * <p>Example:</p>
	 * <code>
	 * $column = User::getColumn('username);
	 * echo $column->length;
	 * </code>
	 *
	 * @param string $name
	 * @return Column
	 */
	public static function getColumn($name) {
		$c = self::getColumns();

		return isset($c[$name]) ? $c[$name] : false;
	}

	/**
	 * The column name of the primary key
	 *
	 * @return string
	 */
	public static function primaryKeyColumn() {
		return 'id';
	}

	/**
	 * Returns true if this is a new record and does not exist in the database yet.
	 *
	 * @return boolean
	 */
	public function getIsNew() {

		return $this->_isNew;
	}
	
	protected function getAttribute($name){
		$col = $this->getColumn($name);

		return $col ? $col->formatOutput($this->_attributes[$name]) : $this->_attributes[$name];
	}

	/**
	 * The special magic getter
	 *
	 * This function finds database values and relations
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		
//		App::debug($name, '__get');
		
		if (key_exists($name, $this->_attributes)) {
			return $this->getAttribute($name);
		} elseif ($this->getColumn($name)) {
			//it's a db column but it's not set in the attributes array.
			return null;
		} elseif (($relation = $this->getRelation($name))) {
			return $this->_getRelated($name);
		} else {

			return parent::__get($name);
		}
	}

	/**
	 * The special magic getter
	 *
	 * This function finds relations and you can supply a FindParams object to the find function.
	 *
	 * @param string $name the method name
	 * @param array $parameters method parameters
	 * @return mixed the method return value
	 */
	public function __call($name, $parameters) {
		
		if (($relation = $this->getRelation($name))){
			
			$query = isset($parameters[0]) ? $parameters[0] : null;
			
			if(is_array($query)){
				$query = Query::newInstance()->where($query);
			}
			
			return $this->_getRelated($name, $query);
		}else{
			throw new Exception("function {$this->className()}:$name does not exist");
		}
	}
	
	public function setCachedAttributes($attr){
		foreach ($attr as $colName => $value) {
			if ($this->getColumn($colName)) {
				$this->_attributes[$colName] = $value;
			} else {
				//Nested relation query. eg. contact.addressbook.owner.*
				//Pass on cached relation attributes to the related model.
				$this->setJoinRelationCache($colName, $value);
			}
		}
		$this->castDatabaseAttributes();
		
		$this->_modifiedAttributes = [];
	}

	/**
	 * Get's a relation from cache or from the database
	 *
	 * @param string $name Name of the relation
	 * @param Query $query
	 * @return \Intermesh\Core\Db\modelName|null Returns null if not found
	 */
	private function _getRelated($name, Query $query = null) {

		$relation = $this->getRelation($name);

		if (!$relation) {
			throw new Exception($name . ' is not a relation of ' . $this->classname());
		}
		
		if(!isset($query)){
			
			if(isset($this->_setRelations[$name])){
				
				//cached relations when set directly in the __set function
				return $this->_setRelations[$name];
			}else if (isset($this->_joinRelationAttr[$name])) {
				//joinRelationAttr are set when Query::joinRelation() is used with find().			
				$attr = $this->_joinRelationAttr[$name];

				$modelName = $relation->getRelatedModelName();
				
				$primaryKeys = $modelName::primaryKeyColumn();
				
				if(!is_array($primaryKeys)){
					$primaryKeys = [$primaryKeys];
				}
				
				foreach($primaryKeys as $primaryKey){
					if(!isset($attr[$primaryKey])){
						return null;
					}
				}

				$model = new $modelName(false);
				$model->setCachedAttributes($attr);
				

				//unset($this->_joinRelationAttr[$cacheKey]);
				
				$this->_setRelations[$name] = $model;

				return $model;
			}
		}		
		
		$model = $relation->find($this, $query);		
		
		if($model && !isset($query)){
			$this->_setRelations[$name] = $model;
		}
		
		return $model;
	}

	/**
	 *
	 * @inheritdoc
	 */
	public function __isset($name) {
		return isset($this->_attributes[$name]) ||
						($this->getRelation($name) && $this->_getRelated($name)) ||
						parent::__isset($name);
	}

	/**
	 * Magic setter. Set's database columns or setter functions.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {

		if ($this->loadingFromDatabase) {
			//Simply set the db attribute
			$this->_attributes[$name] = $value;
		} else {
			if (($col = $this->getColumn($name))) {
				
				$value = $col->formatInput($value);
				
				$this->setAttribute($name, $value);
				
			} elseif(array_key_exists($name, $this->_attributes)){
				$this->_attributes[$name] = $value;
			} elseif (($relation = $this->getRelation($name))) {

				if ($relation->isA(Relation::TYPE_BELONGS_TO)) {
					//belongs to must be set immediately.
//					App::debug("SET BELONGS TO");
//					App::debug($value);
					
					$relation->set($this, $value);				
					
				}else
				{				
					//processed in the save() function
					$this->_saveRelations[$name] = ['relation' => $relation, 'value' => &$value, 'name' => $name];
				}
				
				//cache it here so __get can return it.
				$this->_setRelations[$name] = &$value;
				
			} else {
				parent::__set($name, $value);
			}
		}
	}
	
	protected function setAttribute($name, $value){
		if ((!isset($this->_attributes[$name]) && $value !== null) || ($value === null || ($this->_attributes[$name] !== $value && !$this->isModified($name)))) {
			$this->_modifiedAttributes[$name] = isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
		}
		$this->_attributes[$name] = $value;
	}

	/**
	 * Check is this model or model attribute name has modifications not saved to
	 * the database yet.
	 *
	 * @param string/array $attributeName
	 * @return boolean
	 */
	public function isModified($attributeName = false) {
		if (!$attributeName) {
			return count($this->_modifiedAttributes) > 0;
		} else {
			if (is_array($attributeName)) {
				foreach ($attributeName as $a) {
					if (array_key_exists($a, $this->_modifiedAttributes)) {
						return true;
					}
				}
				return false;
			} else {
				return array_key_exists($attributeName, $this->_modifiedAttributes);
			}
		}
	}

	/**
	 * Reset attribute to it's original value and clear the modified attribute.
	 *
	 * @param string $name
	 */
	public function resetAttribute($name) {
		$this->$name = $this->getOldAttributeValue($name);
		unset($this->_modifiedAttributes[$name]);
	}

	/**
	 * Reset attributes to it's original value and clear the modified attributes.
	 */
	public function resetAttributes() {
		foreach ($this->_modifiedAttributes as $name => $oldValue) {
			$this->$name = $oldValue;
		}
		$this->_modifiedAttributes = [];
	}

	/**
	 * Get the old value for a modified attribute.
	 *
	 * <p>Example:</p>
	 * <code>
	 *
	 * $model = User::findByPk(1);
	 * $model->username='newValue':
	 *
	 * $oldValue = $model->getOldAttributeValue('username');
	 *
	 * </code>
	 * @param string $attributeName
	 * @return mixed
	 */
	public function getOldAttributeValue($attributeName) {
		return isset($this->_modifiedAttributes[$attributeName]) ? $this->_modifiedAttributes[$attributeName] : false;
	}

	/**
	 * Get modified attributes
	 *
	 * Get a key value array of modified attribute names with their old values
	 * that are not saved to the database yet.
	 *
	 * eg. ['attributeName'=>'Old value']
	 *
	 * <p>Example:</p>
	 * <code>
	 *
	 * $model = User::findByPk(1);
	 * $model->username='newValue':
	 *
	 * $oldValues = $model->getModifiedAttributes();
	 *
	 * </code>
	 *
	 * @return array
	 */
	public function getModifiedAttributes() {
		return $this->_modifiedAttributes;
	}

	/**
	 * Define relations for this model.
	 *
	 * <p>Example:</p>
	 * <code>
	 * public static function defineRelations(RelationFactory $r){
	 * 	  return [
	 *		$r->hasMany('addressbooks', Addressbook::className(), 'ownerUserId')->setDeleteAction(Relation::DELETE_CASCADE),
	 *		$r->manyMany('roles', Role::className(), UserRole::className(), "userId"),
	 * 		$r->hasOne('role', UserRole::className(), 'userId')
	 * 	];
	 *
	 * }
	 * </code>
	 * @param RelationFactory $r The factory class that can be used to define relations
	 * @return Relation[]
	 */
	protected static function defineRelations(RelationFactory $r) {
		return [];
	}

	/**
	 * Get's the relation definition
	 *
	 * @param string $name
	 * @return Relation
	 */
	public static function getRelation($name) {
		$relations = static::getRelations();

		return isset($relations[$name]) ? $relations[$name] : false;
	}

	/**
	 * Get all relations for this model
	 *
	 * @return Relation[]
	 */
	public static function getRelations() {
		$calledClass = get_called_class();
		if (!isset(self::$_relations[$calledClass])) {
			$relations = static::defineRelations(new RelationFactory(get_called_class()));
			
			self::$_relations[$calledClass] = [];
			
			foreach($relations as $relation){
				self::$_relations[$calledClass][$relation->getName()] = $relation; 
			}
		}

		return self::$_relations[$calledClass];
	}
	
	/**
	 * Add a relation available in current script run only.
	 * Useful for joining relations.
	 * 
	 * @param Relation $r
	 */
	public static function addRuntimeRelation(Relation $r){
		
		//make sure self::$_relations is populated
		static::getRelations();
		
		self::$_relations[get_called_class()][$r->getName()] = $r;		
	}

	/**
	 * Define an array of validation rules
	 *
	 * These rules should be checked when saving this model.
	 *
	 * <code>
	 * protected static function defineValidationRules() {
	 *
	 * 	self::getColumn('username')->required=true;
	 *
	 * 	return [
	 * 			new ValidateEmail("email")
	 * 	];
	 * }</code>
	 *
	 * @return AbstractValidationRule[]
	 */
	protected static function defineValidationRules() {
		return [];
	}

	/**
	 * Get's the validation rules
	 *
	 * @return AbstractValidationRule
	 */
	protected static function getValidationRules() {
		$calledClass = get_called_class();
		if (!isset(self::$_validators[$calledClass])) {
			self::$_validators[$calledClass] = static::defineValidationRules();
		}

		return self::$_validators[$calledClass];
	}

//	private function _debugModifiedAttributes() {
//		foreach ($this->getModifiedAttributes() as $attrName => $oldVal) {
//			App::debug("Modified " . $attrName . " from " . var_export($oldVal, true) . " to " . var_export($this->_attributes[$attrName], true));
//		}
//	}
//	


	/**
	 * Save changes to database
	 *
	 * <p>Example:</p>
	 * <code>
	 * $model = User::findByPk(1);
	 * $model->setAttibutes(['username'=>'admin']);
	 * if(!$model->save())	{
	 *  //oops, validation must have failed
	 *   var_dump($model->getValidationErrors();
	 * }
	 * </code>
	 *
	 * @return static|bool
	 */
	public function save() {
		
		if($this->markDeleted){
			return $this->delete();
		}

		if (!$this->validate()) {
			return false;
		}

//		$this->_debugModifiedAttributes();

//		$wasNew = $this->isNew;

		$useTransaction = !empty($this->_saveRelations) && !App::dbConnection()->getPDO()->inTransaction();


		if ($useTransaction) {
			App::dbConnection()->getPDO()->beginTransaction();
		}

		if ($this->_isNew) {
			$this->_dbInsert();
		} else {
			$this->dbUpdate();
		}

		foreach ($this->_saveRelations as $r) {		
				
			if(!$r["relation"]->set($this, $r["value"])){
				
				App::debug("Saving relation". $r['name'].' failed: '.var_export($r['relation']->getValidationErrors(), true));

				$this->setValidationError($r['name'], 'relation', $r['relation']->getValidationErrors());				
				App::dbConnection()->getPDO()->rollBack();				
				
				return false;
			}
		}

		//Clear modified attributes
		$this->_modifiedAttributes = [];
		
		$this->_saveRelations = [];
		
		//Unset the relations passed to __set so they are queried from the db after save.
		$this->_setRelations = [];

		if ($useTransaction) {
			App::dbConnection()->getPDO()->commit();
		}
		
		return $this;
	}

	/**
	 * Inserts the model into the database
	 *
	 * @return boolean
	 */
	private function _dbInsert() {

		if ($this->getColumn('createdAt') && empty($this->createdAt)) {
			$this->createdAt = gmdate(Column::DATETIME_API_FORMAT);
		}

		if ($this->getColumn('modifiedAt') && !$this->isModified('modifiedAt')) {
			$this->modifiedAt = gmdate(Column::DATETIME_API_FORMAT);
		}

		$colNames = [];
		$bindParams = [];
		
		//Build an array of fields that are set in the object. Unset columns will
		//not be in the SQL query so default values from the database are respected.
		$i = 0;
		foreach ($this->columns as $colName => $col) {
			if (isset($this->_attributes[$colName])) {
				$i++;
				$colNames[':attr'.$i] = $colName;				
				$bindParams[':attr'.$i] = $this->_attributes[$colName];
			}
		}

		$sql = "INSERT INTO `{$this->tableName()}` (`" . implode('`,`', array_values($colNames)) . "`) VALUES " .
						"(" . implode(', ', array_keys($colNames)) . ")";

		try {
			$stmt = App::dbConnection()->getPDO()->prepare($sql);

			foreach ($colNames as $bindTag => $colName) {

				$column = $this->getColumn($colName);

				$stmt->bindParam($bindTag, $this->_attributes[$colName], $column->pdoType, empty($column->length) ? null : $column->length);
			}


			App::debugger()->debugSql($sql, $bindParams);

			$ret = $stmt->execute();
		} catch (Exception $e) {

			$msg = $e->getMessage();

			if (App::debugger()->enabled) {
				$msg .= "\n\nFull SQL Query: " . $sql . "\n\nParams:\n" . var_export($this->_attributes, true);

				$msg .= "\n\n" . $e->getTraceAsString();

				App::debug($msg);
			}
			throw new Exception($msg);
		}

		if (!is_array($this->primaryKeyColumn()) && empty($this->{$this->primaryKeyColumn()})) {
			$this->{$this->primaryKeyColumn()} = intval(App::dbConnection()->getPDO()->lastInsertId());

			$this->_isNew = false;
		}

		return $ret;
	}

	/**
	 * Updates the database with modified attributes.
	 * 
	 * You generally don't use this function yourself. The only case it might be
	 * usefull if you want to generate some attribute based on the auto incremented
	 * primary key value. For an order number for example.
	 * 
	 * @return boolean
	 * @throws Exception
	 */
	protected function dbUpdate() {
		
		
		if ($this->getColumn('modifiedAt') && !$this->isModified('modifiedAt')) {
			$this->modifiedAt = gmdate(Column::DATETIME_API_FORMAT);
		}

		if (!$this->isModified()) {
			return true;
		}
	
	
		
		$i =0;
		
		$tags = [];

		$updates = [];
		foreach ($this->_modifiedAttributes as $colName => $oldValue) {
			$i++;
			$tag = ':attr'.$i;
			$updates[] = "`$colName` = " . $tag;
			
			$tags[$colName] = $tag;
		}

		$sql = "UPDATE `{$this->tableName()}` SET " . implode(',', $updates) . " WHERE ";

		$bindParams = [];

		
		if (is_array($this->primaryKeyColumn())) {
			$first = true;
			foreach ($this->primaryKeyColumn() as $colName) {
				
				$i++;
				
				$tag = ':attr'.$i;
				
				if (!$first){
					$sql .= ' AND ';
				}else{
					$first = false;
				}

				$sql .= "`" . $colName . "`= " . $tag;
				
				$tags[$colName] = $tag;
			}
			
		}else {
			$i++;
			$tag = ':attr'.$i;
			$sql .= "`" . $this->primaryKeyColumn() . "`=" . $tag ;
			$tags[$this->primaryKeyColumn()] = $tag;
		}

		try {
			$stmt = App::dbConnection()->getPDO()->prepare($sql);

			$pks = is_array($this->primaryKeyColumn()) ? $this->primaryKeyColumn() : [$this->primaryKeyColumn()];

			foreach ($this->columns as $colName => $column) {

				if ($this->isModified($colName) || in_array($colName, $pks)) {
					$bindParams[$tags[$colName]] = $this->_attributes[$colName];
					$stmt->bindParam($tags[$colName], $this->_attributes[$colName], $column->pdoType, empty($column->length) ? null : $column->length);
				}
			}

			App::debugger()->debugSql($sql, $bindParams);

			$ret = $stmt->execute();
		} catch (Exception $e) {
			$msg = $e->getMessage();

			if (App::debugger()->enabled) {
				$msg .= "\n\nFull SQL Query: " . $sql . "\n\nParams:\n" . var_export($bindParams, true);

				$msg .= "\n\n" . $e->getTraceAsString();

				App::debug($msg);
			}
			throw new Exception($msg);
		}
		return $ret;
	}

	/**
	 * Find an ActiveRecord by primary key
	 *
	 * <p>Example:</p>
	 * <code>
	 * $user = User::findByPk(1);
	 * </code>
	 *
	 * @param mixed $pk
	 * @return static
	 */
	public static function findByPk($pk) {

		if (!is_array($pk)) {
			$pk = [static::primaryKeyColumn() => $pk];
		}

		$cacheKey = get_called_class() . ':' . implode(',', $pk);

		if (empty(self::$_findByPkCache[$cacheKey])) {
			self::$_findByPkCache[$cacheKey] = self::find($pk)->single();
		}

		return self::$_findByPkCache[$cacheKey];
	}

	/**
	 * Find models
	 * 
	 * Basic usage
	 * -----------
	 * 
	 * 
	 * 
	 * <code>
	 * 
	 * //Single user by attributes.
	 * $user = User::find(['username' => 'admin'])->single(); 
	 * 
	 * //Multiple users with search query.
	 * $users = User::find(
	 *         Query::newInstance()
	 *           ->orderBy([$orderColumn => $orderDirection])
	 *           ->limit($limit)
	 *           ->offset($offset)
	 *           ->searchQuery($searchQuery, ['t.username','t.email'])
	 *         );
	 * 
	 * foreach ($users as $user) {
	 *   echo $user->username."<br />";
	 * }
	 * </code>
	 * 
	 * Join relations
	 * --------------
	 * 
	 * With Query::joinRelation() it's possible to join a relation so that later calls to that relation don't need to be fetched from the database separately.
	 * 
	 * <code>
	 * $contacts = Contact::find(
	 *         Query::newInstance()
	 *           ->joinRelation('addressbook')
	 *         );
	 * 
	 * foreach ($contacts as $contact) {
	 *   echo $contact->addressbook->name."<br />"; //no query needed for the addressbook relation.
	 * }
	 * </code>
	 * 
	 * Complex join
	 * ------------
	 * <code>
	 * 
	 * $roles = Role::find(Query::newInstance()
	 *         ->orderBy([$orderColumn => $orderDirection])
     *         ->limit($limit)
     *         ->offset($offset)
     *         ->search($searchQuery, ['t.name'])
     *         ->joinAdvanced(
     *              UserRole::className(),
     *              Criteria::newInstance()
     *                  ->where('t.id = userRole.roleId')
     *                  ->andWhere(["userRole.userId", $userId])
     *              ,
     *              'userRole',
     *              'LEFT')
     *          ->where(['userRole.roleId'=>null])
     *          );
	 * </code>
	 * 
	 * More features
	 * -------------
	 * 
	 * <code>
	 * $finder = Contact::find(
						Query::newInstance()
								->select('t.*, count(emailAddresses.id)')
								->joinRelation('emailAddresses', false)								
								->groupBy(['t.id'])
								->having("count(emailAddresses.id) > 0")
						->where(['!=',['lastName'=>null]])
						->andWhere(Criteria::newInstance()
						->where(['IN','firstName', ['Merijn', 'Wesley']])
						->orWhere(['emailAddresses.email'=>'test@intermesh.nl'])
						)
		);
	 * </code>
	 * 
	 * <p>Produces:</p>
	 * 
	 * <code>
	 * SELECT t.*, count(emailAddresses.id) FROM `contactsContact` t
	 * INNER JOIN `contactsContactEmailAddress` emailAddresses ON (`t`.`id` = `emailAddresses`.`contactId`)
	 * WHERE
	 * (
	 * 	`t`.`lastName` IS NOT NULL
	 * )
	 * AND
	 * (
	 * 	(
	 * 		`t`.`firstName` IN ("Merijn", "Wesley")
	 * 	)
	 * 	OR
	 * 	(
	 * 		`emailAddresses`.`email` = "test@intermesh.nl"
	 * 	)
	 * )
	 * AND
	 * (
	 * 	`t`.`deleted` != "1"
	 * )
	 * GROUP BY `t`.`id`
	 * HAVING
	 * (
	 *		count(emailAddresses.id) > 0
	 * )
	 * </code>
	 *
	 * @param Query|array $query Query object. When you pass an array a new Query object will be autocreated and the array will be passwed to {@see Query::where()}.
	 * @return \static
	 */
	public static function find($query = null) {
		
		if(is_array($query)){
			$query = Query::newInstance()->where($query);
		}
		
		return new Finder(get_called_class(), $query);
	}

	/**
	 * Validates all attributes of this model
	 *
	 * You do not need to call this function. It's automatically called in the
	 * save function. Validators can be defined in defineValidationRules().
	 * This function can also be overridden to perform additional checks.
	 *
	 * @see defineValidationRules()
	 * @return boolean
	 */
	public function validate() {

		if ($this->_isNew) {
			//validate all columns
			$fieldsToCheck = array_keys($this->getColumns());
		} else {
			//validate modified columns
			$fieldsToCheck = array_keys($this->getModifiedAttributes());
		}
		
		$uniqueKeysToCheck = [];

		foreach ($fieldsToCheck as $colName) {

			$column = $this->getColumn($colName);

			if ($column->required && empty($this->_attributes[$colName])) {
				$this->setValidationError($colName, "required");
			} elseif (!empty($column->length) && !empty($this->_attributes[$colName]) && String::length($this->_attributes[$colName]) > $column->length) {
				$this->setValidationError($colName, 'maxLength', ['length' => $column->length, 'value'=>$this->_attributes[$colName]]);
			}
			
			if($column->unique && isset($this->_attributes[$colName])){
				//set imploded key so no duplicates will be checked
				$uniqueKeysToCheck[implode(':', $column->unique)] = $column->unique;
			}
		}
		
		$validators = [];
		
		foreach(self::getValidationRules() as $validator){
			if(in_array($validator->getId(), $fieldsToCheck)){
				$validators[]=$validator;
			}			 
		}		
		
		foreach($uniqueKeysToCheck as $uniqueKeyToCheck){
			$validator = new ValidateUnique($uniqueKeyToCheck[0]);
			$validator->setRelatedColumns($uniqueKeyToCheck);
			$validators[] = $validator;
		}
		
		foreach ($validators as $validator) {
			if (!$validator->validate($this)) {

				$this->setValidationError(
								$validator->getId(), $validator->getErrorCode(), $validator->getErrorInfo());
			}
		}


		return !$this->hasValidationErrors();
	}


	/**
	 * Check if a given ActiveRecord is the same as this ActiveRecord.
	 *
	 * <p>Example:</p>
	 * <code>
	 * $model = User::findByPk(2);
	 *
	 * $model2 = User::find(['username'=>'test']);
	 *
	 * if($model->equals($model2)){
	 * 	echo 'Users are equal!';
	 * }
	 * </code>
	 *
	 * @param AbstractRecord $compare
	 * @return boolean
	 */
	public function equals(AbstractRecord $compare) {
		return $compare->className() == $this->className() && $compare->{$compare->primaryKeyColumn()} == $this->{$this->primaryKeyColumn()};
	}
	
	
	protected function deleteCheckRestrictions(){
		$r = $this->getRelations();

		foreach ($r as $name => $relation) {
			if($relation->deleteAction === Relation::DELETE_RESTRICT) {
				if ($relation->isA(Relation::TYPE_HAS_ONE) || $relation->isA(Relation::TYPE_BELONGS_TO)){
					$result = $this->$name;
				}else{
					$result = $this->$name->single();
				}

				if ($result) {
					throw new DeleteRestrict($this, $relation);
				}
			}
		}
	}
	
	protected function deleteCascade(){
		$r = $this->getRelations();

		foreach ($r as $name => $relation) {
			if($relation->deleteAction === Relation::DELETE_CASCADE) {
				$result = $this->$name;

				if ($result instanceof Finder) {
					//has_many relations result in a statement.
					foreach ($result as $child) {
						if (!$child->equals($this)) {
							$child->delete();
						}
					}
				} elseif ($result) {
					//single relations return a model.
					$result->delete();
				}
			}
		}
	}

	/**
	 * Delete's the model from the database
	 *
	 * <p>Example:</p>
	 * <code>
	 * $model = User::findByPk(2);
	 * $model->delete();
	 * </code>
	 *
	 * @return boolean
	 */
	public function delete() {


		if ($this->_isNew) {
			return true;
		}
		
		$this->deleteCheckRestrictions();
		
		$this->deleteCascade();		

		$sql = "DELETE FROM `" . $this->tableName() . "` WHERE ";

		if (is_array($this->primaryKeyColumn())) {

			$first = true;
			foreach ($this->primaryKeyColumn() as $field) {
				if (!$first){
					$sql .= ' AND ';
				}else{
					$first = false;
				}

				$column = $this->getColumn($field);

				$sql .= "`" . $field . '`=' . App::dbConnection()->getPDO()->quote($this->{$field}, $column->pdoType);
			}
		}else {
			$column = $this->getColumn($this->primaryKeyColumn());

			$sql .= "`" . $this->primaryKeyColumn() . '`=' . App::dbConnection()->getPDO()->quote($this->{$this->primaryKeyColumn()}, $column->pdoType);
		}

		App::debug($sql, 'sql');

		$success = App::dbConnection()->getPDO()->query($sql);

		if (!$success){
			throw new Exception("Could not delete from database");
		}

//		$this->fireEvent('delete', [$this]);

		return true;
	}

	/**
	 * Set database attributes with key value array.
	 *
	 * <p>Example:</p>
	 * <code>
	 * $model = User::findByPk(1);
	 * $model->setAttibutes(['username' => 'admin']);
	 * $model->save();
	 * </code>
	 *
	 * @param array $attributes
	 * @return \static
	 */
	public function setAttributes(array $attributes) {

		foreach ($attributes as $attributeName => $value) {
			$this->{$attributeName} = $value;
		}

		return $this;
	}
	
	/**
	 * Resolves an attribute path and adds the found attributes to the return array. 
	 * eg. contact.owner.username or contact.owner.*
	 * 
	 * @param string $path
	 * @param array $return
	 * 
	 * @throws Exception
	 */
	private function _resolveAttribute($path, &$return){
		
		$parts = explode('.', $path);
	
		$model = $this;
		
		$level = 0;
		$partCount = count($parts);
		foreach($parts as $part) {	
//			echo $part."\n-";
			if(!isset($return['attributes'])){
				$return['attributes'] = [];
			}
			
			$level++;
			
			$isLastPart = $level === $partCount;
			
			if(!$isLastPart){
				//Must be a relation
				$model = $model->{$part};
				
				if(!$model){
					
					//relation doesn't exist so we can't go further.
					$return['attributes'][$part] = false;
					break;
				}
				
//				if(!is_a($model,"\Intermesh\Core\Db\AbstractRecord")){
//					throw new Exception("Invalid attributes specification '$path' in model '".$this->className().'" '.var_export($model, true));
//				}
					
				if(!isset($return['attributes'][$part])){
					$return['attributes'][$part] = [];
				}
			}else
			{
				
				
				$this->_getLastAttributePart($model, $part, $return);
			}			
			
			if(!$isLastPart){				
				$return = &$return['attributes'][$part];				
			}			
		}
	}
	
	/**
	 * Extracts attributes from the last attribute part. eg address[*, formatted]
	 * This will adjust the $part variable to "address" and returns ['*', 'formatted'];
	 * 
	 * @param string $part
	 * @return boolean
	 */
	private function _extractAttributesFromLastPart(&$part){
		if(preg_match("/([\w]+)\[([^\]]+)\]/", $part, $matches)){
			$part = $matches[1];
			return array_map('trim', explode(',', $matches[2]));			
		}else
		{
			return null;
		}		
	}
	
	/**
	 * Get's the last part attributes from the model and adds them to the return array
	 * 
	 * @param AbstractRecord $model
	 * @param string $part
	 * @param array $return
	 */
	private function _getLastAttributePart(AbstractRecord $model, $part, &$return){		
		
		if($part === '*'){
			$return['attributes'] = array_merge($return['attributes'], $model->getAttributes());
		}else
		{
			$attributes = $this->_extractAttributesFromLastPart($part);
			
			$attr = $model->{$part};
			
//			if($this->getRelation($part)){			
			//do this for finder object, array of models, or a record.
			if($attr instanceof AbstractRecord || $attr instanceof Finder || (is_array($attr) && isset($attr[0]) && $attr[0] instanceof AbstractRecord)){
				
				//attributes and relation part				
				$return['attributes'][$part] = $this->_relationToAttributeArray($attr, $attributes);			
			}else
			{
				//just plain single attribute
				$return['attributes'][$part] = $attr;
			}
		}
	}
	
	/**
	 * Changes an hasmany relation finder array into an array of attributes or 
	 * returns the array of attributes from a single relation.
	 * 
	 * @param type $relation
	 * @param type $attributes
	 * @return boolean
	 */
	private function _relationToAttributeArray($relation, $attributes){
		
		if(!$relation){
			return null;
		}elseif($relation instanceof AbstractRecord){
			return isset($attributes) ? $relation->toArray($attributes) : $relation->toArray();		
		}elseif(is_array($relation) || $relation instanceof Finder)
		{			
			$return = [];
			
			foreach ($relation as $index => $relatedModel) {
				
//				if(!method_exists($relatedModel, 'toArray')){
//					var_dump($this);
//					
//					throw new \Exception('here');
//				}
				
				if(is_array($relatedModel)){
					
					//Can be an array when a relation was set with the JSON API and the save failed.
					//In that case we just pass it back.
					
					$return[$index] = ['attributes' => $relatedModel];
				}else{
				
					$return[$index] = isset($attributes) ? $relatedModel->toArray($attributes) : $relatedModel->toArray();		
				}
			}
			
			return $return;			
		}
	}	
	
	/**
	 * By default all fields except the ones that start with an underscore are returned.
	 * @return array
	 */
	protected function getDefaultReturnAttributes(){
		$names = array_keys($this->_attributes);
		
		$arr = [];
		foreach($names as $name){
			if(substr($name, 0, 1) != '_'){
				$arr[]=$name;
			}
		}
		
		return $arr;
	}
	

	/**
	 * Get model attributes.
	 *
	 * It's also possible to get related attributes eg.	['name','user.username'];
	 * This will fetch the name attribute of the model but also the username of the
	 * user relation. Wildcards are also supported for all direct database attributes:
	 * 
	 * ['name','owner.*','hasmanyRelation[*, magicAttribute]']
	 *
	 * <code>
	 * $model = User::findByPk(1);
	 *
	 * $allOfTheModel = $model->getAttributes();
	 *
	 * $someButWithRelational = $model->getAttributes(['name', 'user.username']);
	 * 
	 * $allAndWithUser = $model->getAttributes(['*', 'user.*']);
	 * 
	 * </code>
	 *
	 * @return array
	 */
	public function getAttributes(array $returnAttributes = []) {		
		
		if (empty($returnAttributes)) {
			$returnAttributes = $this->getDefaultReturnAttributes();
		}

		$return = [];
	
		foreach ($returnAttributes as $colName) {			
			$this->_resolveAttribute($colName, $return);						
		}
		
		return $return['attributes'];
	}
	
	
	/**
	 * 
	 * Parse returnAttributes parameter passed to API
	 * 
	 * In the API the return attributes may be passed as a string.
	 * This will be parsed into an array. eg turn this:
	 * 
	 * 'name,owner.*,hasmanyRelation[*, magicAttribute]' 
	 * 
	 * into:
	 * 
	 * ['name','owner.*','hasmanyRelation[*, magicAttribute]']
	 * 
	 * @param mixed $returnAttributes
	 * @return array
	 * @throws Exception
	 */
	public static function parseReturnAttributes($returnAttributes){
		
		if(empty($returnAttributes)){
			return [];
		}else if(is_string($returnAttributes)){
		
			if(!preg_match_all('/[^,\[]+(\[[^\]]+\])?+/', $returnAttributes, $matches)){
				throw new Exception("Invalid format for returnAttributes: ".$returnAttributes);
			}
	
			return array_map('trim', $matches[0]);
		}else if (is_array($returnAttributes)){
			return $returnAttributes;
		}  else {
			throw new Exception("Invalid format for returnAttributes");
		}
	}
	
	
	/**
	 * When the API returns this model to the client in JSON format it uses 
	 * this function to convert it into an array. 
	 * 
	 * @param array $returnAttributes The attributes that will be returned. Note that the primary key will always be returned.
	 * @return array
	 */
	public function toArray(array $returnAttributes = []){
		
		$array =  [
			'attributes' => $this->getAttributes($returnAttributes)
		];
		
		//check if primary key is present. And if not then add it.
		$pks = $this->primaryKeyColumn();
		
		if(!is_array($pks)){
			$pks = [$pks];
		}
		
		foreach($pks as $pk){
			if(!in_array($pk, $array['attributes'])){
				$array['attributes'][$pk] = $this->$pk;
			}
		}		
		
		if(isset($this->ownerUserId)){
			$array['isOwner'] = User::current()->id === $this->ownerUserId;
		}
		
		$array['success'] = !$this->hasValidationErrors();

		if(!$array['success']){
			$array['validationErrors'] = $this->getValidationErrors();
		}
		
		return $array;
	}
	
	/**
	 * Lock the table of this model and also for the 't' alias.
	 * 
	 * @return boolean
	 */
			
	public static function lock($write = true){
		$mode = $write ? 'WRITE' : 'READ';
		return App::dbConnection()->getPDO()->query("LOCK TABLES `".static::tableName()."`  $mode, `".static::tableName()."` AS t $mode");
	}
	
	
	public function getETag(){
		return $this->modifiedAt;
	}
}
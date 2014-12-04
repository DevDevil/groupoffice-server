<?php
namespace Intermesh\Core\Db;

/**
 * Factory class that creates relations for models
 */
class RelationFactory{

	private $_modelClassName;

	public function __construct($modelClassName) {
		$this->_modelClassName=$modelClassName;
	}

	/**
	 * Create a belongs to relation. For example an "Addressbook" belongs to a user.
	 *
	 * @param string $relatedModelName The class name of the related model. eg. User::className()
	 * @param string $key eg. 'ownerUserId'
	 * @return Relation
	 */
	public function belongsTo($name, $relatedModelName, $key){
		$r = new Relation($name, Relation::TYPE_BELONGS_TO, $this->_modelClassName, $relatedModelName, $key, $relatedModelName::primaryKeyColumn());

		return $r;
	}

	/**
	 * Create a hasMany relation. For example a user has many address books.
	 *
	 * @param string $relatedModelName The class name of the related model. eg. Addressbook::className()
	 * @param string $foreignKey eg. ownerUserId This key points to the main model name
	 * @param string $key You can leave this empty in most cases. Except when the primary key is an array. Then you must select one of the primary key columns.
	 * @return Relation
	 */
	public function hasMany($name, $relatedModelName, $foreignKey, $key=null){

		if(!isset($key)){
			$key = call_user_func(array($this->_modelClassName, 'primaryKeyColumn'));
		}

		$r = new Relation($name, Relation::TYPE_HAS_MANY, $this->_modelClassName, $relatedModelName, $key, $foreignKey);

		return $r;
	}

	/**
	 * Creates a has one relation. For example a user has one addressbook.
	 * This one is similar to hasMany but only one item exists.
	 *
	 * @param string $relatedModelName The class name of the related model.
	 * @param string $foreignKey The attribute name in the related model that points to the main model.
	 * @param string $key You can leave this empty in most cases. Except when the primary key is an array. Then you must select one of the primary key columns.
	 * @return Relation
	 */
	public function hasOne($name, $relatedModelName, $foreignKey, $key=null){

		if(!isset($key)){
			$key = call_user_func(array($this->_modelClassName, 'primaryKeyColumn'));
		}

		$r = new Relation($name, Relation::TYPE_HAS_ONE, $this->_modelClassName, $relatedModelName, $key, $foreignKey);

		return $r;
	}

	/**
	 * Create a many many relation
	 *
	 * @param string $relatedModelName The class name of the related model.
	 * @param string $linkModelName The model class name that links this model to the relation model.
	 * @param string $mainTableColumn The column of link model that points to the main model.
	 * @return ManyManyRelation
	 */
	public function manyMany($name, $relatedModelName, $linkModelName, $mainTableColumn, $foreignTableColumn = null){
		
		

		//eg. roleId
		
		if(!isset($foreignTableColumn)){
			
			$primaryKeys = $linkModelName::primaryKeyColumn();
			if(!is_array($primaryKeys)){
				throw new \Exception ("Fatal error: Primary key of linkModel '".$linkModelName."' should be an array if used in a many many relation.");
			}

			$foreignTableColumn = $primaryKeys[0]==$mainTableColumn ? $primaryKeys[1] : $primaryKeys[0];
		}

		$r = new ManyManyRelation($name, Relation::TYPE_MANY_MANY, $this->_modelClassName, $relatedModelName, $mainTableColumn, $foreignTableColumn);
		$r->setLinkModel($linkModelName);

		return $r;
	}
}
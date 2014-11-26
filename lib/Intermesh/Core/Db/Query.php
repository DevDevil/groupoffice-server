<?php
namespace Intermesh\Core\Db;

/**
 * Used to build parameters for ActiveRecord::find() database queries
 *
 * @see AbstractRecord::find()
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class Query extends Criteria {
	
	
	public $distinct = false;
	
	public $select = 't.*';
	
	public $orderBy;	
	
	public $groupBy;
	
	public $having = [];	
	
	public $limit;
	
	public $offset = 0;	
	
	public $joinModels = [];
	
	public $joinRelations = [];
	
	public $joinAdvanced = [];

	/**
	 * Set the Distinct select option
	 *
	 * @param boolean $useDistinct
	 * @return \self
	 */
	public function distinct($useDistinct = true) {
		$this->distinct = $useDistinct;
		return $this;
	}

	/**
	 * Merge this with another Query object.
	 *
	 * @param Query $query
	 * @return self
	 */
	public function mergeWith(Query $query) {

		foreach (get_object_vars($query) as $key => $value) {
			if(is_array($this->$key)){
				$this->$key = array_merge($this->$key, $value);
			}else
			{
				$this->$key = $value;
			}
        }

		return $this;
	}

	/**
	 * Set the selected fields for the select query.
	 *
	 * Remember the model table is aliased with 't'. Using this may result in incomplete models.
	 *
	 * @param string $select
	 * @return \self
	 */
	public function select($select = '*') {
		$this->select = $select;
		return $this;
	}


	/**
	 * Execute a simple search query
	 *
	 * @param string $query
	 * @param array $fields eg. array("t.username","t.email")
	 * @param boolean $exactPhrase If false, then the query will be wrapped with wildcards and spaces will be replaced. eg. John Smits will become %John%Smith%.
	 * @return \self
	 */
	public function search($query, $fields, $exactPhrase = false) {

		if (!empty($query)) {
			if (!$exactPhrase) {
				$query = "%" . preg_replace("/[\s]+/", "%", $query) . "%";
			}
			
			$hashValues = [];
			
			foreach($fields as $field){
				$hashValues[$field] = $query;
			}
			
			$this->andWhere(['OR','LIKE',$hashValues]);
		}

		return $this;
	}

	/**
	 * Set sort order
	 *
	 * @param string/array $by or array('field1'=>'ASC','field2'=>'DESC') for multiple values	 
	 * @return \self
	 */
	public function orderBy($by) {
		$this->orderBy = $by;	

		return $this;
	}

	/**
	 * Adds a group by clause.
	 *
	 * @param array $columns eg. array('t.id');
	 * @return \self
	 */
	public function groupBy(array $columns) {
		$this->groupBy = $columns;
		return $this;
	}

	/**
	 * Adds a having clause. Warning. RAW SQL is passed to the query. Be careful
	 * with user input.
	 *
	 * @param mixed $condition
	 * @return \self
	 */
	public function having($condition, $operator = 'AND') {
		$this->having[] = [$operator, $condition];
		return $this;
	}
	
	public function andHaving($condition){
		return $this->having($condition);
	}
	
	public function orHaving($condition){
		return $this->having($condition, 'OR');
	}
	
	
	public $joinRaw = [];
	
	public function joinRaw($str){
		$this->joinRaw[]=$str;
		
		return $this;
	}

	/**
	 * Join a model table on the main table. Note that the main table can also be
	 * a previously joined table.
	 *
	 * <p>Example:</p>
	 * <code>
	 * $query = Query::newInstance()
	  ->joinModel(Addressbook::className(), 'userId', 'ab');
	 *
	 * User::find($query);
	 * </code>
	 *
	 * @param string $modelClassName The model to join
	 * @param string $mainTableColumn The column of the main table to use
	 * @param string $joinTableAlias The alias of the table to join
	 * @param string $joinTableColumn The column of the joined table to use
	 * @param string $mainTableAlias The alias of the main table.
	 * @param string $type The join type. INNER, LEFT or RIGHT
	 * @return \self
	 */
	public function joinModel($modelClassName, $mainTableColumn, $joinTableAlias, $joinTableColumn = 'id', $mainTableAlias = 't', $type = 'INNER') {

		$this->joinModels[] = array(
				'modelClassName' => $modelClassName,
				'joinTableColumn' => $joinTableColumn,
				'joinTableAlias' => $joinTableAlias,
				'mainTableAlias' => $mainTableAlias,
				'mainTableColumn' => $mainTableColumn,
				'type' => $type
		);

		return $this;
	}

	/**
	 * Join a relation in the find query. Relation models are fetched together and
	 * can be accessed without the need for an extra select query.
	 *
	 * eg. joinRelation('owner') on an addressbooks query allows you to do
	 *
	 * $addressbook->owner->username
	 *
	 * without an extra select query.
	 *
	 * @param string $name
	 * @param bool $selectAttributes Select the relation attributes and fetch them into the related model.
	 * @param string $type
	 * @param Criteria $criteria Add extra join criteria
	 * @return \self
	 */
	public function joinRelation($name, $selectAttributes = true, $type = 'INNER', Criteria $criteria = null) {		

		$this->joinRelations[$name] = array(
				'name' => $name,
				'type' => $type,
				'criteria' => $criteria,
				'selectAttributes' => $selectAttributes
		);

		return $this;
	}

	/**
	 * Make an advanced join where you can specify the join criteria yourself.
	 *
	 * <p>Example:</p>
	 * <code>
	 * $query = Query::newInstance()
	  ->orderBy([$orderColumn => $orderDirection])
	  ->limit($limit)
	  ->offset($offset)
	  ->searchQuery($searchQuery, array('t.name'));

	  if(isset($userId)){

	  //select the checked column for this user.
	  $query->select('t.*, !ISNULL(userRole.userId) AS checked')
	  ->groupBy(['t.id']);

	  $criteria = Criteria::newInstance()
	  ->addRawCondition('t.id','userRole.roleId')
	  ->addCondition("userId", $userId, '=', 'userRole');

	  $query->joinAdvanced(UserRole::className(), $criteria, 'userRole', 'LEFT');
	  }

	  $roles = Role::find($query);
	 * </code>
	 *
	 * @param string $modelClassName The model to join
	 * @param \Intermesh\Core\Db\Criteria $criteria The criteria used in the ON clause.
	 * @param string $joinTableAlias Leave empty for none.
	 * @param string $type The join type. INNER, LEFT or RIGHT
	 * @return Query
	 */
	public function joinAdvanced($modelClassName, Criteria $criteria, $joinTableAlias, $type = 'INNER') {

		$this->joinAdvanced[] = array(
				'modelClassName' => $modelClassName,
				'criteria' => $criteria,
				'joinTableAlias' => $joinTableAlias,
				'type' => $type
		);

		return $this;
	}

	/**
	 * Skip this number of records
	 *
	 * @param int $offset
	 * @return \self
	 */
	public function offset($offset = 0) {
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Limit the number of models returned
	 *
	 * @param int $limit
	 * @return \self
	 */
	public function limit($limit = 0) {
		$this->limit = $limit;
		return $this;
	}
}
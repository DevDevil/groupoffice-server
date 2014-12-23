<?php

namespace Intermesh\Core\Db;

use Exception;
use Intermesh\Core\AbstractObject;
use Intermesh\Core\App;
use IteratorAggregate;
use PDOStatement;

/**
 * All find (select) queries are done by this class. It finds ActiveRecord models.
 *
 * This is generally not used directly.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Finder extends AbstractObject implements IteratorAggregate {

	/**
	 * Name of the AbstractRecord derived class this finder is for.
	 *
	 * @var string
	 */
	public $recordClassName;

	/**
	 *
	 * @var Query 
	 */
	private $_query;
	private $_pdoStatement;
	
	
	//populated in buildSql
	
	/**
	 * Key value array with [tableAlias => modelClassName]
	 * @var array 
	 */
	public $aliasMap;	
	
	/**
	 * Key value array of parameters to bind to the SQL Statement
	 * @var array 
	 */
	public $bindParameters;
	
	
	private static $_paramCount = 0;
	private static $_paramPrefix = ':ifw';
	
	/**
	 * Table alias used for the primary table
	 * @var string 
	 */
	public $primaryTableAlias = 't';

	/**
	 *
	 * @param string $recordClassName The class name of the activerecord to find.
	 */
	public function __construct($recordClassName, Query $query = null) {

		parent::__construct();

		$this->recordClassName = $recordClassName;

		if (!isset($query)) {
			$query = Query::newInstance();
		}

		$this->_query = $query;
	}

	/**
	 * Get the query parameters
	 *
	 * @return Query
	 */
	public function getQuery() {
		return $this->_query;
	}

	public function buildSql() {
		
		$this->bindParameters = [];
		$this->aliasMap=[$this->primaryTableAlias => $this->recordClassName];
		
		$select = "SELECT ";
		if ($this->_query->distinct) {
			$select .= "DISTINCT ";
		}
		$select .= $this->_query->select . ' ';

		

		$joins = $this->_joinAdvanced();
		$joins .= $this->_joinModels();
		
		$joins .= $this->_joinRaw();
		
		$where = $this->_buildWhere();
		$joins .= $this->_joinRelations($select);

		$select .= 'FROM `' . call_user_func([$this->recordClassName, 'tableName']) . '` ' . $this->primaryTableAlias."\n";

		
		$group = $this->_buildGroupBy();
		$having = $this->_buildHaving();
		$orderBy = $this->_buildOrderBy();

		$limit = "";
		if (!empty($this->_query->limit)) {
			$limit .= "\nLIMIT " . intval($this->_query->offset) . ',' . intval($this->_query->limit);
		}

		return $select . $joins . $where . $group . $having . $orderBy . $limit;
	}
	
	private function _findPdoType($tableAlias, $column){
		if(!isset($tableAlias) || !isset($this->aliasMap[$tableAlias])){
			return \PDO::PARAM_STR;
		}else
		{
//			echo $tableAlias.' -> '.$this->aliasMap[$tableAlias].'::getColumn('.$column.')';
			$columnObject = call_user_func([$this->aliasMap[$tableAlias],'getColumn'], $column);
			if(!$columnObject){
				throw new \Exception("Column ".$column." not found");
			}
			return $columnObject->pdoType;
		}
	}

	/**
	 * 
	 * @param string $sql
	 * @return \PDOStatement
	 * @throws Exception
	 */
	private function _executeSql($sql) {
		try {
                    
			$binds = [];
                        
			$stmt = App::dbConnection()->getPDO()->prepare($sql);
			
			foreach ($this->bindParameters as $p) {
				$binds[$p['paramTag']]=$p['value'];
				$stmt->bindValue($p['paramTag'], $p['value'], $this->_findPdoType($p['tableAlias'], $p['column']));
			}
			
			foreach($this->_query->bindParameters as $p){				
				$binds[$p['paramTag']]=$p['value'];
				$stmt->bindValue($p['paramTag'], $p['value'], $p['pdoType']);
			}
			
			App::debugger()->debugSql($sql, $binds);

			$stmt->execute();
			
		} catch (\PDOException $e) {
			$msg = $e->getMessage();

			if (App::debugger()->enabled) {
				$msg .= "\n\nFull SQL Query: " . $sql;
				$msg .= "\nBind params: " . var_export($binds, true);
				$msg .= "\n\n" . $e->getTraceAsString();

				App::debug($msg);
			}

			throw $e;
		}
		
		if(!isset($this->_query->fetchMode)){
			$stmt->setFetchMode(PDO::FETCH_CLASS, $this->recordClassName, array(false));
		}else
		{
			call_user_func_array([$stmt, 'setFetchMode'], $this->_query->fetchMode);
		}
		
		return $stmt;
	}

	/**
	 * Don't call this directly. Just do a foreach($finder as $model){}
	 *
	 * @param Query $query
	 * @return PDOStatement
	 * @throws Exception
	 */
	public function getIterator() {

		if (!isset($this->_pdoStatement)) {
			$sql = $this->buildSql();
			$this->_pdoStatement = $this->_executeSql($sql);
		}

		return $this->_pdoStatement;
	}
	
	
	/**
	 * Counts the records in the result.
	 * 
	 * @return int
	 */
	public function getRowCount(){
		return $this->getIterator()->rowCount();
	}

	private function _buildGroupBy() {
		$groupBy = "";

		if ($this->_query->groupBy) {
			$groupBy .= "\nGROUP BY ";

			foreach ($this->_query->groupBy as $column) {
				$groupBy .= $this->quoteTableAndColumnName($column) . ', ';
			}

			$groupBy = trim($groupBy, ' ,');
		}

		return $groupBy."\n";
	}

	private function _buildWhere(Criteria $query = null, $prefix="") {
		
		if(!isset($query)){
			$query = $this->_query;
			$appendWhere = true;
		}else
		{
			//import new params
			$this->_query->bindParameters = array_merge($this->_query->bindParameters, $query->bindParameters);
			$appendWhere = false;
		}
		
		$conditions = $query->where;

		$condition = array_shift($conditions);

		if (!$condition) {
			return '';
		}

		$where = $this->_buildCondition($condition[1], $prefix) . "\n";

		foreach ($conditions as $condition) {
			$where .= $prefix.$condition[0]."\n". $this->_buildCondition($condition[1], $prefix) . "\n";
		}

		return $appendWhere ? "WHERE\n" . $where : $where;
	}

	private function _buildCondition($condition, $prefix = "") {
		$c = $prefix."(\n";

		if (is_string($condition)) {
			$c .= $prefix."\t".$condition."\n";
		} elseif (is_array($condition)) {
			$c .= $this->_arrayConditionToString($condition, $prefix."\t");
		} elseif (is_a($condition, "Intermesh\\Core\\Db\\Criteria")) {
			$c .= $this->_buildWhere($condition, $prefix."\t");
		}else
		{
			throw new \Exception("Invalid condition passed\n\n". var_export($condition, true));
		}

		$c .= $prefix.')';

		return $c;
	}

	private function _isType($value) {
		return in_array($value, ['AND', 'OR', 'NOT IN', 'IN']);
	}

	private function _arrayConditionToString($condition, $prefix) {
		
		if(!isset($condition[0])){
			//Hash values given directly.eg. [id=>1].			
			$condition = [$condition];
		}

		$values = array_pop($condition);
		$comparator = '=';
		$type = 'AND';

		while ($next = array_pop($condition)) {
			if ($this->_isType($next)) {
				$type = $next;
			} else {
				$comparator = $next;

				$this->_validateComparator($comparator);
			}
		}

		switch ($type) {

			case 'AND':
			case 'OR':
				return $this->_buildAndOrCondition($type, $comparator, $values, $prefix);

			case 'IN':
			case 'NOT IN':
				return $this->_buildInCondition($type, $comparator, $values, $prefix);
		}
	}

	private function _validateComparator($comparator) {
		if (!preg_match("/[=!><a-z]/i", $comparator)) {
			throw new \Exception("Invalid comparator: " . $comparator);
		}
	}

	private function _splitTableAndColumn($column, $usePrimaryTableAsDefault = true) {
		$parts = explode('.', $column);

		if (count($parts) > 1) {
			return [trim($parts[0], ' `'), trim($parts[1], ' `')];
		} else {
			
			$alias = $usePrimaryTableAsDefault ? $this->primaryTableAlias : null;
			
			return [$alias, trim($column, ' `')];
		}
	}

	protected function quoteTableName($tableName) {

		//disallow \ ` and \00  : http://stackoverflow.com/questions/1542627/escaping-field-names-in-pdo-statements
		if (preg_match("/[`\\\\\\000\(\),]/", $tableName)) {
			throw new Exception("Invalid characters found in column name: " . $tableName);
		}

		return '`' . $tableName . '`';
	}

	/**
     * Quotes a column name for use in a query.
     * If the column name contains prefix, the prefix will also be properly quoted.
     * If the column name is already quoted or contains '(', '[[' or '{{',
     * then this method will do nothing.
     * @param string $columnName column name
     * @return string the properly quoted column name

     */
	protected function quoteColumnName($columnName) {
		return $this->quoteTableName($columnName);
	}

	protected function quoteTableAndColumnName($columnName, $usePrimaryTableAsDefault = true) {
		
		$parts = $this->_splitTableAndColumn($columnName, $usePrimaryTableAsDefault);

		if(isset($parts[0])){
			return $this->quoteTableName($parts[0]) . '.' . $this->quoteColumnName($parts[1]);
		}else
		{
			return $this->quoteColumnName($parts[1]);
		}
	}
	
	/**
	 * Code for automatic join of relations based on the where table aliases.
	 * @param type $relationName
	 */
	private function _joinWhereRelation($relationName){
		
		if(!isset($this->aliasMap[$relationName]) && !isset($this->_query->joinRelations[$relationName])){
			$arName = $this->recordClassName;
			if($arName::getRelation($relationName)){
				$this->_query->joinRelation($relationName, false);					
			}
		}
	}

	/**
	 * Builds "`t`.`id` = :ifw1 AND `t`.`name` = :ifw2"
	 * 
	 * @param type $type
	 * @param type $comparator
	 * @param type $hashValues
	 * @return string
	 */
	private function _buildAndOrCondition($type, $comparator, $hashValues, $prefix) {
		

		$str = '';
		
		foreach ($hashValues as $column => $value) {

			if ($str != '') {				
				$str .= $type . ' ';
			}

			
//			if($this->_isAColumn($column)){
				$columnParts = $this->_splitTableAndColumn($column);
				$col = $this->quoteTableName($columnParts[0]) . '.' . $this->quoteColumnName($columnParts[1]);
				
				$this->_joinWhereRelation($columnParts[0]);
				
//			}else
//			{
//				$columnParts = array(null, null);
//				$col=$column;
//			}

			if(!isset($value)) {
				if($comparator == '='){
					
					$str .= $prefix . $col . " IS NULL\n";
					
				}elseif($comparator == '!=')
				{
					$str .= $prefix . $col . " IS NOT NULL\n";
				}else
				{
					throw new \Exception('Null value not possible with comparator '.$comparator);
				}
			}else
			{
				$paramTag = $this->_getParamTag();
				
				$this->addBindParameter($paramTag, $value, $columnParts[0], $columnParts[1]);
				
				$str .= $prefix . $col . ' ' . $comparator . ' ' . $paramTag."\n";
				
			}
			
		}

		return $str;
	}

	private function _buildInCondition($type, $column, $hashValues, $prefix) {

		$columnParts = $this->_splitTableAndColumn($column);

		$this->_joinWhereRelation($columnParts[0]);
		
		$str = $this->quoteTableName($columnParts[0]) . '.' . $this->quoteColumnName($columnParts[1]) . ' ' . $type . ' (';

		foreach ($hashValues as $column => $value) {
			$paramTag = $this->_getParamTag();
			$this->addBindParameter($paramTag, $value, $columnParts[0], $columnParts[1]);

			$str .= $paramTag . ', ';
		}

		$str = $prefix.rtrim($str, ', ') . ")\n";

		return $str;
	}

	private function _buildOrderBy() {

		if (!isset($this->_query->orderBy)) {
			return '';
		}

		$orderBy = "\nORDER BY ";

		foreach ($this->_query->orderBy as $column => $direction) {

			$direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

			$orderBy .= $this->quoteTableAndColumnName($column, false) . ' ' . $direction . ', ';
		}

		return trim($orderBy, ' ,')."\n";
	}

	private function _buildHaving() {
		
		$conditions = $this->_query->having;

		$condition = array_shift($conditions);
		
		if (!$condition) {
			return '';
		}

		$having = $this->_buildCondition($condition[1]) . "\n";

		foreach ($conditions as $condition) {
			$having .= $condition[0] . ' (' . $this->_buildCondition($condition[1]) . ")\n";
		}

		return "HAVING\n" . $having;
	}

	public function addBindParameter($paramTag, $value, $tableAlias=null, $column=null) {
		
//		if($paramTag == ':ifw4')
//			throw new \Exception();
		$this->bindParameters[] = array('paramTag' => $paramTag, 'value' => $value, 'tableAlias' => $tableAlias, 'column' => $column);
	}

	/**
	 * Private function to get the current parameter prefix.
	 * 
	 * @return String The next available parameter prefix.
	 */
	private function _getParamTag() {
		self::$_paramCount++;
		return self::$_paramPrefix . self::$_paramCount;
	}

	private function _joinModels() {

		$joins = "";
		foreach ($this->_query->joinModels as $config) {

			$joinTableName = call_user_func(array($config['modelClassName'], 'tableName'));

			$joins .= $config['type'] . ' JOIN `' . $joinTableName . '` ';

			if (!empty($config['joinTableAlias'])) {
				$joins .= '`' . $config['joinTableAlias'] . '` ';
			} else {
				$config['joinTableAlias'] = $joinTableName;
			}

			$joins .= 'ON (`' . $config['mainTableAlias'] . '`.`' . $config['mainTableColumn'] . '` = ';

			$joins .= '`' . $config['joinTableAlias'] . '`.`' . $config['joinTableColumn'] . '`)'."\n";

			$this->aliasMap[$config['joinTableAlias']] = $config['modelClassName'];
		}

		return $joins;
	}

	private function _joinAdvanced() {

		$joins = "";
		foreach ($this->_query->joinAdvanced as $config) {

			$this->aliasMap[$config['joinTableAlias']] = $config['modelClassName'];

			$joinTableName = call_user_func(array($config['modelClassName'], 'tableName'));

			$joins .=  $config['type'] . ' JOIN `' . $joinTableName . '` ';

			if (!empty($config['joinTableAlias'])) {
				$joins .= '`' . $config['joinTableAlias'] . '` ';
			}

			$joins .= 'ON (' . $this->_buildWhere($config['criteria'], "\t") . ")\n";
		}

		return $joins;
	}
	
	private function _joinRaw(){
		
		$join = '';
		
		foreach($this->_query->joinRaw as $str){
			$join .= $str."\n";
		}
		
		return $join;
	}

	private function _joinRelations(&$select) {

		$alreadyJoined = array();

		$join = '';

		foreach ($this->_query->joinRelations as $joinRelation) {

			$names = explode('.', $joinRelation['name']);
			$relationAlias = 't';
			$attributePrefix = '';

			$relationModel = $this->recordClassName;

			foreach ($names as $name) {

				/* @var $r Relation  */

				$r = $relationModel::getRelation($name);

				if (!$r) {
					throw new Exception("Can't join non existing relation '" . $name . '"');
				}

				$attributePrefix.= $name . '@';

				$joinParams = $this->_joinRelation($r, $relationAlias, $joinRelation['type'], $attributePrefix, $joinRelation['criteria']);

				//add the bind params to the main criteria object.
				if (isset($joinRelation['criteria'])) {
					$this->_query->bindParameters = array_merge($this->_query->bindParameters, $joinRelation['criteria']->bindParameters);
				}

				if (!in_array($name, $alreadyJoined)) {
					$join .= $joinParams['joinSql'];
				}
				$alreadyJoined[] = $name;

				if ($joinRelation['selectAttributes']) {
					$select .= ",\n" . $joinParams['selectCols'];
				}

				

				$relationModel = $r->getRelatedModelName();
				$relationAlias = $name;
			}
		}

		return $join;
	}
	
	/**
	 * Creates joinSql and a select string of attributes. Used by ActiveRecord
	 * when joinRelation() is used in FindParams
	 *
	 * @param string $relationTableAlias
	 * @param string $primaryTableAlias
	 * @param string $joinType
	 * @param string $attributePrefix
	 * @return array
	 */
	private function _joinRelation(Relation $relation, $primaryTableAlias, $joinType, $attributePrefix, Criteria $criteria = null) {
		
		if($relation->isA(Relation::TYPE_MANY_MANY)){
			throw new Exception('many many not supported by joinRelation');
//			$relatedModelName = $relation->linkModelClassName;
		}else
		{
			$relatedModelName = $relation->getRelatedModelName();
		}

		$joinSql = $joinType . ' JOIN `' . $relatedModelName::tableName() . '` ' . $relation->getName() . ' ON ' .
						'(`' . $primaryTableAlias . '`.`' . $relation->getKey() . '` = `' . $relation->getName() . '`.`' . $relation->getForeignKey(). '`';
		
		if(isset($relation->query)){
			//TODO Perhaps a better way for string replace?
			
			$where = str_replace('`t`', '`'.$relation->getName().'`', $this->_buildWhere($relation->query, "\t"));
			
			if(!empty($where)){
				$joinSql .= ' AND ('.$where.')';
			}
		}
		
		if(isset($criteria)){
			$joinSql .= ' AND ('.$this->_buildWhere($criteria, "\t").')';
		}		
		
		$joinSql .= ")\n";

		$joinCols = array_keys($relatedModelName::getColumns());

		foreach ($joinCols as $col) {

			if (!isset($selectCols)) {
				$selectCols = '';
			} else {
				$selectCols .=",\n";
			}
			$selectCols .= "`" . $relation->getName()  . '`.`' . $col . '` AS `' . $attributePrefix . $col . '`';
		}
		
		$this->aliasMap[$relation->getName()] = $relatedModelName;

		return array('joinSql' => $joinSql, 'selectCols' => $selectCols);
	}

	

	/**
	 * Return one model. It also set's the limit on
	 *
	 * @return AbstractRecord
	 */
	public function single() {
		$this->_query->limit(1);

		return $this->getIterator()->fetch();
	}

	/**
	 * Fetch all records from the database server. Not lazy but immediately.
	 * May use a lot of memory.
	 *
	 * @return AbstractRecord[]
	 */
	public function all() {
		return $this->getIterator()->fetchAll();
	}

}

<?php

namespace Intermesh\Core\Db;

use Intermesh\Core\AbstractObject;
use Exception;

/**
 * Create "where" criteria for the SQL query ActiveRecord::find() function
 * 
 * <p>Example with finding users with a checkbox if the give role is enabled:</p>
 * <code>
 * 
 * $query = Query::newInstance()
 * 					->joinAdvanced(
  UserRole::className(),
  Criteria::newInstance()
  ->addRawCondition('t.id','userRole.userId')
  ->addCondition("roleId", $roleId, '=', 'userRole')
  ,
  'userRole',
  'LEFT')
  ->select('t.*, !ISNULL(userRole.roleId) AS checked')
  ->groupBy(['t.id']);
 * 
 * $users = User::find($query);
 * 
 * </code>
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Criteria extends AbstractObject {

	/**
	 * The where conditions
	 * 
	 * Use {@see where()} to add new.
	 * 
	 * @var array 
	 */
	public $where = [];

	/**
	 * Key value array of bind parameters.
	 * 
	 * @var array 
	 */
	public $bindParameters = [];
	
	
	/**
	 * Where function that is safe for direct client input.
	 * 
	 * String input is not allowed:
	 * 
	 * eg. "username = :username"
	 * 
	 * See {@see where()} for more information
	 * 
	 * @param array $condition
	 * @return self
	 * @throws Exception
	 */
	public function whereSafe(array $conditions){
//		if(is_string($condition)){
//			throw new Exception("Strings not allowed in whereSafe");
//		}
		foreach($conditions as $condition){
			$this->where($condition);
		}
		return $this;
	}

	/**
	 * 
	 * Set where parameters. 
	 * 
	 * If relations are included they are joined automatically.
	 * 
	 * <p>Examples:</p>
	 * 
	 * <code>
	 * Query::newInstance()
	 * ->where([['id'=>'1','name'=>'merijn'])  // (id=1 AND name=merijn)
	 * 
	 * ->where(['AND', '=', ['id'=>'1','name'=>'merijn'])  // (id=1 AND name=merijn)
	 * ->andWhere(['OR', '=',['id'=>'1','name'=>'merijn'])  // AND (id=1 OR name=merijn)
	 * 
	 * (id=1 AND name=merijn) AND ((id=1) AND (name LIKE 'merijn'))
	 * ->andWhere('username = :username)->addBindParameter(':username', $username)
	 * ->orWhere(['OR', 'LIKE', ['id'=>'2', 'name' => 'piet', 'like') // OR (id like 2 OR name like 'piet')
	 * ->andWhere(['IN', 'id', [1, 2, 3]])
	 * ->andWhere(Criteria $c)
	 * 	 * 
	 * (id=1 and name=merijn and ((id=4 or id=3) OR (name=merijn and id=2))
	 * </code>
	 * 
	 * @param string|array|Criteria $condition
	 * @param string $operator =, !=, LIKE, NOT LIKE
	 * 
	 * @return static
	 */
	public function where($condition, $operator = "AND") {
		$this->where[] = [$operator, $condition];

		return $this;
	}

	/**
	 * Concatonate where condition with AND
	 * 
	 * {@see where()}
	 * 
	 * @param string|array|Criteria $condition
	 * @return static
	 */
	public function andWhere($condition) {
		return $this->where($condition, 'AND');
	}

	/**
	 * Concatonate where condition with OR
	 * 
	 * {@see where()}
	 * 
	 * @param string|array|Criteria $condition
	 * @return static
	 */
	public function orWhere($condition) {
		return $this->where($condition, 'OR');
	}

	/**
	 * Add a parameter to bind to the SQL query
	 * 
	 * <code>
	 * $query->where($subQuery)
	  ->addBindParameter(':userId', $userId, \PDO::PARAM_INT);
	 * </code>
	 * 
	 * @param string $tag eg. ":userId"
	 * @param mixed $value
	 * @param int $pdoType {@see \PDO} Autodetected based on the type of $value if omitted.
	 */
	public function addBindParameter($tag, $value, $pdoType = null) {

		if (!isset($pdoType)) {
			if (is_bool($value)) {
				$pdoType = PDO::PARAM_BOOL;
			} elseif (is_int($value)) {
				$pdoType = PDO::PARAM_INT;
			} elseif (is_null($value)) {
				$pdoType = PDO::PARAM_NULL;
			} else {
				$pdoType = PDO::PARAM_STR;
			}
		}

		$this->bindParameters[] = ['paramTag' => $tag, 'value' => $value, 'pdoType' => $pdoType];
		
		return $this;
	}

}

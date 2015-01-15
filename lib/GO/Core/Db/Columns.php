<?php

namespace GO\Core\Db;

use GO\Core\App;

/**
 * Class that fetches database column information for the ActiveRecord.
 * It detects the length, type, default and required attribute etc.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Columns {

	public static $forceLoad = false;
	private static $_columns = [];

	private static function getCacheKey($recordClassName) {

		return 'modelColumns_' . $recordClassName;
	}

	/**
	 * Clear the column cache for a particular model.
	 *
	 * @param string $recordClassName
	 */
	public static function clearCache($recordClassName) {
		App::cache()->delete(self::getCacheKey($recordClassName));
	}

	/**
	 * Get all columns of a model
	 *
	 * @param string $recordClassName
	 * @return Column[] Array with column name as key
	 */
	public static function getColumns($recordClassName) {
		$tableName = $recordClassName::tableName();
		$cacheKey = self::getCacheKey($recordClassName);

		if (self::$forceLoad) {
			unset(self::$_columns[$tableName]);
			App::cache()->delete($cacheKey);
		}

		if (isset(self::$_columns[$tableName]) && !self::$forceLoad) {
			return self::$_columns[$tableName];
		} elseif (($columns = App::cache()->get($cacheKey))) {
//			App::debug("Got columns from cache for $tableName");
			self::$_columns[$tableName] = $columns;
			return self::$_columns[$tableName];
		} else {
//			App::debug("Loading columns for $tableName");
			self::$_columns[$tableName] = [];
			$sql = "SHOW COLUMNS FROM `" . $tableName . "`;";
			$stmt = App::dbConnection()->getPDO()->query($sql);
			while ($field = $stmt->fetch()) {
				preg_match('/(.*)\(([1-9].*)\)/', $field['Type'], $matches);

				if ($matches) {
					$length = intval($matches[2]);
					$type = $matches[1];
				} else {
					$type = $field['Type'];
					$length = 0;
				}

			
				$default = $field['Default'];

				$ai = strpos($field['Extra'], 'auto_increment') !== false;

				$pdoType = PDO::PARAM_STR;
				switch ($type) {
					case 'int':
					case 'tinyint':
					case 'bigint':
						if ($length == 1 && $type == 'tinyint') {
							$pdoType = PDO::PARAM_BOOL;
							$default = !isset($field['Default']) ? null : (bool) $default;
						} else {
							$pdoType = PDO::PARAM_INT;
							$default = $ai || !isset($field['Default']) ? null : intval($default);
						}

						break;

					case 'float':
					case 'double':
					case 'decimal':
						$pdoType = PDO::PARAM_STR;
						$length = 0;
						$default = $default == null ? null : floatval($default);
						break;
				}


				$required = is_null($default) && $field['Null'] == 'NO' && strpos($field['Extra'], 'auto_increment') === false;

				if ($field['Field'] == 'createdAt' || $field['Field'] == 'modifiedAt') {

					//don't validate because they will be set by the ActiveRecord
					$required = false;
				}

				$c = new Column();
				$c->name = $field['Field'];
				$c->pdoType = $pdoType;
				$c->required = $required;
				$c->length = $length;
				$c->default = $default;
				$c->dbType = $type;
				$c->nullAllowed = $field['Null'] == 'YES';
				
				


				self::$_columns[$tableName][$field['Field']] = $c;
				
			}
			
			self::_processIndexes($tableName);

			App::cache()->set($cacheKey, self::$_columns[$tableName]);

			return self::$_columns[$tableName];
		}
		
		
	}
	
	private static function _processIndexes($tableName){
		$query = "SHOW INDEXES FROM `".$tableName."`";
		
		$unique = [];
		
		//group keys;
		
		// ['keyName' => ['col1', 'col2']];
		
		$stmt = App::dbConnection()->getPDO()->query($query);
		while ($index = $stmt->fetch()) {
			
			if($index['Key_name'] === 'PRIMARY'){
				
				//don't validate uniqueness on primary key
				continue;
			}
			
			if($index['Non_unique'] === "0"){
				if(!isset($unique[$index['Key_name']])){
					$unique[$index['Key_name']] = [];
				}
				
				$unique[$index['Key_name']][] = $index['Column_name'];
			}
		}
		
//		var_dump($unique);
		
		foreach($unique as $cols){
			
			foreach ($cols as $colName){
				self::$_columns[$tableName][$colName]->unique = $cols;
			}
		}
		
	}

}

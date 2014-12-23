<?php

namespace Intermesh\Core\Db;

use Exception;
use Intermesh\Core\App;
use Intermesh\Core\Fs\File;

class Utils {
	
	public static function runSQLFile(File $file) {
		$queries = self::getSqlQueries($file);

		foreach($queries as $query){
			App::dbConnection()->query($query);		
		}
	}

	/**
	 * Get's all queries from an SQL dump file in an array
	 *
	 * @param File $file The absolute path to the SQL file
	 * @access public
	 * @return array An array of SQL strings
	 */
	public static function getSqlQueries(File $file) {
		$sql = '';
		$queries = array();
		
		$handle = $file->open('r');
		if ($handle) {
			while (!feof($handle)) {
				$buffer = trim(fgets($handle, 4096));
				if ($buffer != '' && substr($buffer, 0, 1) != '#' && substr($buffer, 0, 1) != '-') {
					$sql .= $buffer . "\n";
				}
			}
			fclose($handle);
		} else {
			throw new Exception("Could not read SQL dump file $file!");
		}
		$length = strlen($sql);
		$in_string = false;
		$start = 0;
		$escaped = false;
		for ($i = 0; $i < $length; $i++) {
			$char = $sql[$i];
			if ($char == '\'' && !$escaped) {
				$in_string = !$in_string;
			}
			if ($char == ';' && !$in_string) {
				$offset = $i - $start;
				$queries[] = substr($sql, $start, $offset);

				$start = $i + 1;
			}
			if ($char == '\\') {
				$escaped = true;
			} else {
				$escaped = false;
			}
		}
		return $queries;
	}
	
	/**
	 * Check if a database exists
	 * 
	 * @param string $tableName
	 * @return boolean 
	 */
	public static function databaseExists($databaseName){
		$stmt = App::dbConnection()->query('SHOW DATABASES');
		while($r=$stmt->fetch()){
			if($r[0]==$databaseName){
				return true;
			}		
		}
		
		return false;
	}
	
	/**
	 * Check if a table exists in the Group-Office database.
	 * 
	 * @param string $tableName
	 * @return boolean 
	 */
	public static function tableExists($tableName){
		$stmt = App::dbConnection()->query('SHOW TABLES');
		while($r=$stmt->fetch()){
			if($r[0]==$tableName){
				return true;
			}		
		}
		
		return false;
	}
	
	/**
	 * Check if a field exists 
	 * 
	 * @param string $tableName
	 * @param string $fieldName
	 * @return boolean
	 */
	public static function fieldExists($tableName, $fieldName){
		$sql = "SHOW FIELDS FROM `".$tableName."`";
		$stmt = App::dbConnection()->query($sql);
		while($record = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($record['Field']==$fieldName){
				return true;
			}
		}
		return false;
	}

}

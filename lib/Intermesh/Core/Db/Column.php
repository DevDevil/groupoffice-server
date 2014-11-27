<?php

namespace Intermesh\Core\Db;

use DateTime;
use DateTimeZone;

/**
 * Represents an ActiveRecord database column attribute.
 * 
 * <p>Example:</p>
 * <code>
 * $model = User::findByPk(1);
 * echo $model->getColumn('username')->length;
 * </code>
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Column {
	
	
	/**
	 * false if non unique or an array of columns that should be unique in combination with this column.
	 * 
	 * @var bool|array 
	 */
	public $unique = false;
	
	
	/**
	 * Name of the column
	 * 
	 * @var string 
	 */
	public $name;

	/**
	 * Length of the column
	 * 
	 * @var int
	 */
	public $length;

	/**
	 * True if null is allowed
	 * 
	 * @var boolean 
	 */
	public $nullAllowed;

	/**
	 * Field type in the database
	 * 
	 * @var string 
	 */
	public $dbType;

	/**
	 * PDO Type
	 * 
	 * @var int 
	 */
	public $pdoType;

	/**
	 * True if field is required
	 * 
	 * @var boolean 
	 */
	public $required;

	/**
	 * Default value of the column
	 * 
	 * @var mixed 
	 */
	public $default;

	/**
	 * The MySQL database datetime format.
	 */
	const DATETIME_DATABASE_FORMAT = "Y-m-d H:i:s";

	/**
	 * The date outputted to the clients. It's according to ISO 8601;
	 */
	const DATETIME_API_FORMAT = "Y-m-d\TH:i:s\Z";

	/**
	 * The MySQL database date format.
	 */
	const DATE_FORMAT = "Y-m-d";

	/**
	 * Input formatting for the database.
	 * Currently only used for date fields because we want ISO 8601 for I/O.
	 * 
	 * @param mixed $value
	 * @return mixed
	 */
	public function formatInput($value) {
		if (!empty($value)) {
			switch ($this->dbType) {

				case 'datetime':
					if($value instanceof DateTime){
						$dt = $value;
					}else
					{
						$dt = new DateTime($value);
						$dt->setTimezone(new DateTimeZone("Etc/GMT"));
					}
					$value = $dt->format(self::DATETIME_DATABASE_FORMAT);
					break;

				case 'date':
					//make sure date is formatted correctly
					if($value instanceof DateTime){
						$dt = $value;
					}else
					{
						$dt = new DateTime($value);						
					}
					$value = $dt->format(self::DATE_FORMAT);
					break;
			}
		}
		return $value;
	}

	/**
	 * Output formatting for the database.
	 * Currently only used for date fields because we want ISO 8601 for I/O.
	 * 
	 * @param mixed $value
	 * @return mixed
	 */
	public function formatOutput($value) {
		if (!empty($value)) {
			switch ($this->dbType) {
				case 'datetime':
					$dt = new DateTime($value, new DateTimeZone("Etc/GMT"));
					$value = $dt->format(self::DATETIME_API_FORMAT);
					break;
			}
		}
		return $value;
	}
}
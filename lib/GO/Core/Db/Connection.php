<?php
namespace GO\Core\Db;

use GO\Core\AbstractObject;
use GO\Core\App;
use PDOStatement;

/**
 * The database connection object. It uses PDO to connect to the database.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Connection extends AbstractObject{
	
	/**
	 *
	 * @var PDO 
	 */
	private $_pdo;
	
	/**
	 * Database name
	 * @var string 
	 */
	public $database;
	
	/**
	 * MySQL user
	 * 
	 * @var string 
	 */
	public $user;
	
	/**
	 * MySQL user password
	 * @var string 
	 */
	public $pass;
	
	/**
	 * Port
	 * 
	 * @var int 
	 */
	public $port;
	
	/**
	 * MySQL Hostname
	 * 
	 * @var string 
	 */
	public $host;
	
	
	/**
	 * Connection options
	 * {@link http://php.net/manual/en/pdo.construct.php}
	 * 
	 * @var array 
	 */
	public $options=[];
	
	/**
	 * Gets the global database connection object.
	 * 
	 * {@link http://php.net/manual/en/pdo.construct.php}
	 *
	 * @return PDO Database connection object
	 */
	public function getPDO(){
		if(!isset($this->_pdo)){
			$this->setPDO();
		}
		return $this->_pdo;
	}
	
	/**
	 * Close the database connection. Beware that all active PDO statements must be set to null too
	 * in the current scope.
	 * 
	 * Wierd things happen when using fsockopen. This test case leaves the conneciton open. When removing the fputs call it seems to work.
	 * 
	 * 			
	    App::session()->login('admin','admin');
			
			$settings = \GO\Sync\Model\Settings::model()->findForUser(App::user());
			$account = \GO\Email\Model\Account::model()->findByPk($settings->account_id);
			
			
			$handle = stream_socket_client("tcp://localhost:143");
			$login = 'A1 LOGIN "admin@intermesh.dev" "admin"'."\r\n";
			fputs($handle, $login);
			fclose($handle);
			$handle=null;			
			
			echo "Test\n";
			
			App::unsetDbConnection();
			sleep(10);
	 */
	public function disconnect(){
		$this->_pdo=null;
	}

	/**
	 * Set's a new PDO object base on the current connection settings
	 */
	public function setPDO(){				
		$this->_pdo = null;				
		$dsn = "mysql:host=".$this->host.";dbname=".$this->database.";port=".$this->port;
		$this->_pdo = new PDO($dsn, $this->user, $this->pass, $this->options);
	}	
	
	/**
	 * Execute an SQL string
	 * 
	 * Should be properly escaped!
	 * {@link http://php.net/manual/en/pdo.query.php}
	 * 
	 * @param string $sql
	 * @return PDOStatement
	 */
	public function query($sql){
		App::debugger()->debugSql($sql);
		return $this->getPdo()->query($sql);
	}
	
	/**
	 * UNLOCK TABLES explicitly releases any table locks held by the current session
	 */
	public function unlockTables(){
		return $this->_pdo->query("UNLOCK TABLES");
	}
}

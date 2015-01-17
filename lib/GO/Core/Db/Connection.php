<?php
namespace GO\Core\Db;

use GO\Core\AbstractObject;

/**
 * The database connection object. It uses PDO to connect to the database.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Connection extends AbstractObject{
	
	private $_pdo;
	
	
	public $database;
	
	public $user;
	
	public $pass;
	
	public $port;
	
	public $host;
	
	public $options=array();
	
	/**
	 * Gets the global database connection object.
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

	public function setPDO(){				
		$this->_pdo = null;				
		$dsn = "mysql:host=".$this->host.";dbname=".$this->database.";port=".$this->port;
		$this->_pdo = new PDO($dsn, $this->user, $this->pass, $this->options);
	}	
	
	
	public function query($statement){
		\GO\Core\App::debug($statement, 'sql');
		return $this->pdo->query($statement);
	}
	
	/**
	 * UNLOCK TABLES explicitly releases any table locks held by the current session
	 */
	public function unlockTables(){
		return $this->_pdo->query("UNLOCK TABLES");
	}
}

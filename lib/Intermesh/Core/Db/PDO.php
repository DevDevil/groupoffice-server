<?php
namespace Intermesh\Core\Db;
use Intermesh\Core\App;

/**
 * PDO Connection
 * 
 * PDO extension that set's some defaults for the Intermesh framework.
 * It set's UTF8 as charset, MySQL strict mode in debug mode and persistant 
 * connections.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class PDO extends \PDO{
	public function __construct($dsn, $username, $passwd, $options=null) {
		parent::__construct($dsn, $username, $passwd, $options);
		
		$this->setAttribute(\PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(\PDO::ATTR_PERSISTENT, true);

		//todo needed for foundRows
//		$this->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true); 

		$this->query("SET NAMES utf8");

		if(App::debugger()->enabled){
			//\GO::debug("Setting MySQL sql_mode to TRADITIONAL");
			$this->query("SET sql_mode='TRADITIONAL'");
		}
	}
}
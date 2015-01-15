<?php
namespace GO\Core;

/**
 * Server information class.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Server{
	public function isWindows(){
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}	
}
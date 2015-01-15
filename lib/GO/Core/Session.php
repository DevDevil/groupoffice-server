<?php

namespace GO\Core;

use GO\Core\Model\Session as SessionModel;
use GO\Core\App;
use GO\Core\Db\Query;
use GO\Core\Fs\Folder;
use SessionHandlerInterface;
use ArrayAccess;

/**
 * Session handling class
 *
 * <p>Can be accessed as an array:</p>
 *
 * <code>
 * App::session()['userId']=1;
 * </code>
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Session extends Observable implements ArrayAccess, SessionHandlerInterface {

	/**
	 * The name of the session
	 *
	 * @var string
	 */
	public $sessionName = "ifw";

	/**
	 * Disable session saving
	 *
	 * @var bool
	 */
	public static $enabled = true;

	public function __construct() {
		$this->start();
	}

	/**
	 * Starts the session
	 */
	public function start() {
		//start session
		//In some cases it doesn't make sense to use the session because the client is
		//not capable. (WebDAV for example).
		if (self::$enabled && !$this->isStarted()) {
			if (!isset($_SESSION)) {

				//without cookie_httponly the cookie can be accessed by malicious scripts
				//injected to the site and its value can be stolen. Any information stored in
				//session tokens may be stolen and used later for identity theft or
				//user impersonation.
				ini_set("session.cookie_httponly", 1);

				//Avoid session id in url's to prevent session hijacking.
				ini_set('session.use_only_cookies', 1);

				//Make sure garbase collection is enabled. Debian and Ubuntu use
				//a cronjob for the default file based sessions, but that won't 
				//work for our db sesssions.
				ini_set("session.gc_probability","1");
//				ini_set("session.gc_divisor","100");
				

				session_name($this->sessionName);

				session_set_save_handler($this);

				session_start();
			}
		}
	}

	/**
	 * Check if the session has been started
	 *
	 * @return bool
	 */
	public function isStarted() {
		return session_status() == PHP_SESSION_ACTIVE;
	}

	/**
	 * Return session ID
	 *
	 * @return string
	 */
	public function id() {
		return session_id();
	}

	/**
	 * Get the temporary folder dedicated to this session
	 *
	 * @return Folder
	 */
	public function getTempFolder($autoCreate=true) {

		$id = $this->id() ? $this->id() : 'nosession';
		$folder = App::config()->getTempFolder($autoCreate)->createFolder($id);

		if($autoCreate){
			$folder->create();
		}
		return $folder;
	}


	/**
	 * End the session
	 *
	 * @return bool
	 */
	public function end(){
		//
		//		if (ini_get("session.use_cookies") && !headers_sent()) {
		//			//rRemove session cookie. PHP does not remove this automatically.
		//			$params = session_get_cookie_params();
		//			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		//		}

		return session_destroy();
	}



	/**
	 * Close writing to session so other concurrent requests won't be blocked.
	 * When a PHP session is open the webserver won't process a new request until
	 * the session is closed again.
	 */
	public function closeWriting() {
		session_write_close();
	}

	/**
	 * Update the current session id with a newly generated one
	 *
	 * @link http://php.net/manual/en/function.session-regenerate-id.php
	 * @param bool $delete_old_session [optional]
	 * Whether to delete the old associated session file or not.
	 *
	 * @return bool TRUE on success or FALSE on failure.
	 */
	public function regenerateId() {

		//A PHP variable named “session.use_only_cookies” controls the behaviour
		//of session_start(). When this variable is enabled (true) then session_start() on-
		//ly uses the cookies of a request for retrieving the session ID. If this variable is disa-
		//bled, then GET or POST requests can contain the session ID and can be used for
		//session fixation. This PHP variable was added in PHP 4.3.0 but is enabled by default
		//only since PHP 5.3.0. Environments with previous PHP versions, as well as non-
		//default PHP configurations are vulnerable to the session fixation attack described in
		//this finding if further measures are not taken.
		//In addition to only accepting session IDs in the form of cookies, the application
		//should force the re-generation of session IDs upon successful user authentication.
		//This way, an attacker would not be able to create a session ID that will be reused by
		//the application to identify a valid authenticated session. This is possible in PHP by
		//using the session_regenerate_id() function.

		return self::$enabled ? session_regenerate_id() : false;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset) {
		$this->start();

		return isset($_SESSION[$offset]);
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 *
	 * @param integer $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset) {
		return isset($_SESSION[$offset]) ? $_SESSION[$offset] : null;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 *
	 * @param integer $offset the offset to set element
	 * @param mixed $item the element value
	 */
	public function offsetSet($offset, $item) {
		$_SESSION[$offset] = $item;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to unset element
	 */
	public function offsetUnset($offset) {
		unset($_SESSION[$offset]);
	}

	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Destroy a session
	 * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
	 * @param string $session_id <p>
	 * The session ID being destroyed.
	 * </p>
	 * @return bool The return value (usually <b>TRUE</b> on success, <b>FALSE</b> on failure). Note this value is returned internally to PHP for processing.
	 */
	public function destroy($session_id) {

		$model = SessionModel::findByPk($session_id);
		if($model){
			$model->delete();
		}

		return true;
	}

	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Close the session
	 * @link http://php.net/manual/en/sessionhandlerinterface.close.php
	 * @return bool The return value (usually <b>TRUE</b> on success, <b>FALSE</b> on failure). Note this value is returned internally to PHP for processing.
	 */
	public function close() {
		return true;
	}

	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Cleanup old sessions
	 * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
	 * @param string $maxlifetime <p>
	 * Sessions that have not updated for the last <i>maxlifetime</i> seconds will be removed.
	 * </p>
	 * @return bool The return value (usually <b>TRUE</b> on success, <b>FALSE</b> on failure). Note this value is returned internally to PHP for processing.
	 */
	public function gc($maxlifetime) {

		$query = Query::newInstance()->where(['<', ['modifiedAt'=> gmdate('Y-m-d H:i:s', time()-$maxlifetime)]]);

		$sessions = SessionModel::find($query);

		foreach($sessions as $session){
			$session->delete();
		}

		return true;
	}

	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Initialize session
	 * @link http://php.net/manual/en/sessionhandlerinterface.open.php
	 * @param string $save_path <p>
	 * The path where to store/retrieve the session.
	 * </p>
	 * @param string $name <p>
	 * The session name.
	 * </p>
	 * @return bool The return value (usually <b>TRUE</b> on success, <b>FALSE</b> on failure). Note this value is returned internally to PHP for processing.
	 */
	public function open($save_path, $name) {
		return true;
	}

	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Read session data
	 * @link http://php.net/manual/en/sessionhandlerinterface.read.php
	 * @param string $session_id <p>
	 * The session id.
	 * </p>
	 * @return string an encoded string of the read data. If nothing was read, it must return an empty string. Note this value is returned internally to PHP for processing.
	 */
	public function read($session_id) {
		$model = $this->getModel($session_id);

		return isset($model->data) ? $model->data : "";
	}

	/**
	 * Get the model where the session is saved
	 *
	 * @param string $sessionId
	 * @return \GO\Core\Model\Session
	 */
	public function getModel($sessionId=null){
		if(!isset($sessionId))
		{
			$sessionId = $this->id();
		}
		$model = SessionModel::findByPk($sessionId);

		if(!$model){
			$model = new SessionModel();
			$model->id=$sessionId;
			if(!$model->save()){
				throw new \Exception(var_export($model->getValidationErrors(), true));
			}
		}
		return $model;
	}

	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Write session data
	 * @link http://php.net/manual/en/sessionhandlerinterface.write.php
	 * @param string $session_id <p>
	 * The session id.
	 * </p>
	 * @param string $session_data <p>
	 * The encoded session data. This data is the result of the PHP internally encoding the $_SESSION superglobal to a serialized
	 * string and passing it as this parameter. Please note sessions use an alternative serialization method.
	 * </p>
	 * @return bool The return value (usually <b>TRUE</b> on success, <b>FALSE</b> on failure). Note this value is returned internally to PHP for processing.
	 */
	public function write($session_id, $session_data) {
		$model = SessionModel::findByPk($session_id);
		if(!$model){
			$model = new SessionModel();
			$model->id=$session_id;
		}

		$model->data = $session_data;

		return $model->save()!==false;
	}

}

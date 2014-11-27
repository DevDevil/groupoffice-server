<?php
namespace Intermesh\Core\Http;

/**
 * The HTTP request class.
 *
 * <p>Example:</p>
 * <code>
 * $var = App::request()->post['someVar'];
 * </code>
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Request {

	/**
	 * Contains the POST variables
	 * 
	 * @var array
	 */
	public $post;

	/**
	 * The body of the request. Only JSON is supported at the moment.
	 * 
	 * @var array 
	 */
	public $payload;
	
		
	/**
	 * Contains the GET variables.
	 * 
	 * @var array
	 */
	public $get;

	public function __construct() {
		if($this->isJson()){
			
			$rawPayload = file_get_contents('php://input');
			
//			var_dump($rawPayload);
			
			
			$this->payload = $rawPayload != "" ? json_decode($rawPayload, true) : [];
					
			// Check if the post is filled with an array. Otherwise make it an empty array.
			if(!is_array($this->payload)){
				//$this->post = array();
				
				throw new \Exception("Malformed JSON posted: \n\n".var_export($rawPayload, true));
			}

		}else
		{
			$this->post=$_POST;
		}

		$this->get=$_GET;
	}	


	/**
	 * Get's the content type header
	 *
	 * @return string
	 */
	public function getContentType() {
		if (PHP_SAPI == 'cli') {
			return 'cli';
		} else {
			return isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : '';
		}
	}
	
	/**
	 * Get the request method
	 * 
	 * @return string PUT, POST, DELETE, GET, PATCH, HEAD
	 */
	public function getMethod(){
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Check if the request posted a JSON body
	 *
	 * @return boolean
	 */
	public function isJson() {
		return isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], 'application/json') !== false;
	}

	/**
	 * Check if this request SSL secured
	 *
	 * @return boolean
	 */
	public function isHttps() {
		return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');
	}

	/**
	 * Return true if this is a HTTP post
	 *
	 * @return boolean
	 */
	public function isPost(){
		return $_SERVER['REQUEST_METHOD']==='POST';
	}

	/**
	 * Redirect to another
	 * 
	 * @param string $url
	 */
	public function redirect($url){
		header('Location: '.$url);
		exit();
	}
}
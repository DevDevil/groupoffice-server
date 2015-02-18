<?php

namespace GO\Core\Controller;

use DateTime;
use Exception;
use GO\Core\AbstractObject;
use GO\Core\App;
use GO\Core\Data\Store;
use GO\Core\Db\AbstractRecord;
use GO\Core\Exception\HttpException;
use GO\Core\Exception\MissingControllerActionParameter;
use GO\Core\Http\Router;
use GO\Core\AbstractModel;
use GO\Core\Auth\Model\User;
use ReflectionMethod;

/**
 * Abstract controller class
 *
 * The router routes requests to controller actions.
 * All controllers must extend this or a subclass of this class.
 * 
 * {@see Router The router routes requests to controllers}
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

abstract class AbstractController extends AbstractObject {
	
	
	/**
	 * Create routes for this controller
	 * 
	 * @see Router
	 * @return \GO\Core\Http\RoutesCollection
	 */
	public static function routes() {
		$routes = new \GO\Core\Http\RoutesCollection(self::className());
		
		App::router()->addRoutes($routes);
		
		return $routes;
	}	

	/**
	 * The router object
	 * 
	 * Useful to lookup $this->router->routeParams
	 * 
	 * @var Router
	 */
	protected $router;
	

	/**
	 * Set to true to calculare an MD5 hash and return it as ETag.
	 * 
	 * @var boolean 
	 */
	protected $cacheJsonOutput = false;

	public function __construct(Router $router) {

		$this->router = $router;

		parent::__construct();
	}

	/**
	 * Set HTTP status header
	 * 
	 * @param int $httpCode
	 */
	protected function setStatus($httpCode) {
		header("HTTP/1.1 " . $httpCode);
		header("Status: $httpCode " . HttpException::$codes[$httpCode]);
	}
	
	/**
	 * Set the content type for the response
	 * 
	 * @param string $type eg. "application/json"
	 * @param string $charset
	 */
	protected function setContentType($type, $charset = "UTF-8") {
		header('Content-Type:'.$type.';charset='.$charset.';');
	}

	/**
	 * Return error code and exit
	 * 
	 * @param int $httpCode
	 * @param string $message
	 */
	protected function renderError($httpCode, $message = null, \Exception $exception = null) {
		$this->setStatus($httpCode);

		if (!isset($message)) {
			$message = HttpException::$codes[$httpCode];
		}

		$data['success'] = false;
		$data['errors'][] = $message;
		
		if(isset($exception)){
//			$data['exceptionMessage'] =$exception->getMessage();
			$data['exception'] = explode("\n", (string) $exception);
		}

		return $this->renderJson($data);	
	}
	
	/**
	 * Authenticate the current user
	 * 
	 * Override this for special use cases.
	 * 
	 * @return boolean
	 */
	protected function authenticate(){
		return User::current() != false;
	}
	
	private function jsonEncode($data){
		$json = json_encode($data, JSON_PRETTY_PRINT);
		
		if(empty($json)){
			echo 'JSON encoding error: ';
			
			var_dump($data);
			exit();
		}
		return $json;
	}

	/**
	 * Runs the controller action
	 * 
	 * @return mixed
	 */
	public function run($action) {
		try{
		
			if(!$this->authenticate()){
				throw new HttpException(403);
			}

			$data = $this->callMethodWithParams("action".$action);		

			if(isset($data)){
				
				$this->setContentType('application/json');
				
				$data = $this->jsonEncode($data);

				if ($this->cacheJsonOutput) {
					$this->cacheHeaders(null, md5($data));
				}
				
				//header("Allow: GET, HEAD, PUT, POST, DELETE");
				echo $data;
			}

	//		header('X-XSS-Protection: 1; mode=block');
	//		header('X-Content-Type-Options: nosniff');
		}catch(HttpException $e){	
			$data = $this->renderError($e->getCode(), $e->getMessage(), $e);	
			$data = $this->jsonEncode($data);
			echo $data;
		}
		catch(Exception $e){
			$data = $this->renderError(500, $e->getMessage(), $e);		
			
			$data = $this->jsonEncode($data);
			echo $data;
		}
	}
	
	/**
	 * Runs controller method with GET and route params.
	 * 
	 * For an explanation about route params {@see Router::routeParams}
	 * 
	 * @param string $methodName
	 * @return type
	 * @throws MissingControllerActionParameter
	 */
	protected function callMethodWithParams($methodName){
		
		if(!method_exists($this, $methodName)){
			throw new HttpException(501, $methodName." defined but doesn't exist in controller ".$this->className());
		}
		
		$method = new ReflectionMethod($this, $methodName);

		$rParams = $method->getParameters();

		$givenParams = array_merge($this->router->routeParams, $_GET);

		//call method with all parameters from the $_REQUEST object.
		$methodArgs = array();
		foreach ($rParams as $param) {
			if (!isset($givenParams[$param->getName()]) && !$param->isOptional()) {
				throw new HttpException(400, "Bad request. Missing argument '" . $param->getName() . "' for action method '" . get_class($this) . "->" . $methodName . "'");
			}

			$methodArgs[] = isset($givenParams[$param->getName()]) ? $givenParams[$param->getName()] : $param->getDefaultValue();
		}

		return call_user_func_array([$this, $methodName], $methodArgs);
	}

	/**
	 * Helper funtion to render an array into JSON
	 * 
	 * @param array $json
	 * @param boolean $cache  Turn on caching for JSON. This will calcular an ETag header on the json output so the browser can
	 * cache the response.
	 * @throws Exception
	 */
	protected function renderJson(array $json = []) {
		
		$this->setContentType("application/json");
		
		if (isset($json['debug'])) {
			throw new Exception('debug is a reserved data object');
		}

		if (App::debugger()->enabled) {
			$json['debug'] = App::debugger()->entries;
		}

		if (!isset($json['success'])) {
			$json['success'] = true;
		}


		return $json;
	}

	/**
	 * Send headers so the browser can cache
	 * 
	 * If the If-Modified-Since or If-None-Match headers are sent and they match
	 * a http 304 not modified status will be sent and it will exit.
	 * 
	 * @param DateTime $lastModified
	 * @param string $etagContent
	 * @param DateTime $expires Optionally set an expires header
	 */
	protected function cacheHeaders(DateTime $lastModified = null, $etagContent = null, DateTime $expires = null) {

		//get the HTTP_IF_MODIFIED_SINCE header if set
		$ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
		//get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
		$etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

		header('Cache-Control: private');

		if (isset($lastModified)) {
			header('Modified-At: ' . $lastModified->format('D, d M Y H:i:s') . ' GMT');
		}

		if (isset($etagContent)) {
			header('ETag: ' . $etagContent);
		}

//		header('Expires: '.date('D, d M Y H:i:s', time()+86400*30)); //30 days
//		header("Vary: Authorization");
		header_remove('Pragma');
		
		if(isset($expires)){
			header('Expires: '.$expires->format('D, d M Y H:i:s')); //30 days
		}  else {
			header_remove('Expires');
		}



		if (
				(isset($lastModified) && $ifModifiedSince >= $lastModified->format('U')) || isset($etagContent) && $etagHeader == $etagContent) {
			$this->setStatus(304);
			exit;
		}
	}

	/**
	 * Used for rendering a model response
	 * 
	 * @param AbstractRecord[] $models
	 * @return type
	 */
	protected function renderModel(AbstractModel $model, $returnAttributes = null) {

		if(!$model->isDeleted()){
//		if(App::request()->getMethod() != 'DELETE'){
	
			if (App::request()->getMethod() == 'GET' && isset($model->modifiedAt)) {
				$lastModified = new DateTime($model->modifiedAt);
				$this->cacheHeaders($lastModified, $model->eTag());
			}

			$response = ['data' => []];

			if (isset($returnAttributes)) {
				$returnAttributes = AbstractRecord::parseReturnAttributes($returnAttributes);
				$response['data'] = $model->toArray($returnAttributes);
			} else {
				$response['data'] = $model->toArray();
			}
			
			if($model->isNew()){
				//TODO stip out Read only properties
			}
			
		}
		
		$response['success'] = !$model->hasValidationErrors();

		return $this->renderJson($response);
	}
	
	/**
	 * Used for rendering a store response
	 * 
	 * @param Store $store
	 * @return array
	 */
	protected function renderStore(Store $store) {
		$response = ['success' => true, 'results' => $store->getRecords()];
		
		$this->cacheJsonOutput = true;

		return $this->renderJson($response);
	}
}

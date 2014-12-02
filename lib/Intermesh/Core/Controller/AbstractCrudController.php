<?php
namespace Intermesh\Core\Controller;

use Exception;
use Intermesh\Core\App;
use Intermesh\Core\Exception\HttpException;
use Intermesh\Core\Exception\NotFound;

/**
 * Abstract controller for basic CRUD operations
 *
 * It maps REST operations to action methods:
 * 
 * GET is mapped to:
 * 1. actionStore, if the primary key is not in the route. eg. "contacts" 
 * 2. actionRead, if the primary key is set. eg. "contacts/1"
 * 3. actionNew, if the primary key is set to "0" so you can return default attributes for an empty resource.
 * 
 * POST -> actionCreate
 * PUT -> actionUpdate
 * DELETE -> actionDelete
 * 
 * You may define the methods with parameters. All GET parameters and router parameters will be passed to the controller method.
 * 
 * eg.
 * 
 * <code>
 * protected function actionStore($orderColumn = 'name', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = []) {
 * }
 * </code>
 *
 * @see Router The router routes requests to controllers
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

abstract class AbstractCrudController extends AbstractRESTController{
	
	protected function httpGet() {

		//we assume routeParams holds primary keys!
		if(!isset($this->router->routeConfig['routeParams'])){
			throw new Exception("No primary keys defined in routeParams for route ".$this->router->route);
		}
		
		$firstRouteParam = $this->router->routeConfig['routeParams'][0];
		
		if(!isset($this->router->routeParams[$firstRouteParam])){
			return $this->callMethodWithParams('actionStore');
		}elseif($this->router->routeParams[$firstRouteParam] == '0')
		{
			return $this->callMethodWithParams('actionNew');
		}else{
			return $this->callMethodWithParams('actionRead');
		}	
	}

	/**
	 * Create a new field. Use GET to fetch the default attributes or POST to add a new field.
	 *
	 * The attributes of this field should be posted as JSON in a field object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function httpPost() {
		
		if(!isset(App::request()->payload['data'])){
			throw new HttpException(400, 'Missing data in payload');
		}

		return $this->callMethodWithParams('actionCreate');
	}

	/**
	 * Update a field. Use GET to fetch the default attributes or POST to add a new field.
	 *
	 * The attributes of this field should be posted as JSON in a field object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param int $roleId The ID of the field
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function httpPut() {

		if(!isset(App::request()->payload['data'])){
			throw new HttpException(400, 'Missing data in payload');
		}

		return $this->callMethodWithParams('actionUpdate');
	}

	/**
	 * Delete a field
	 *
	 * @param int $roleId
	 * @throws NotFound
	 */
	public function httpDelete() {
		return $this->callMethodWithParams('actionDelete');
	}
}
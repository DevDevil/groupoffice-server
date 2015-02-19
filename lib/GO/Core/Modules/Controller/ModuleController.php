<?php

namespace GO\Core\Modules\Controller;

use GO\Core\App;
use GO\Core\Auth\Model\User;
use GO\Core\Controller\AbstractController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Modules\Model\Module;
use Sabre\DAV\Exception\Forbidden;


/**
 * The controller for roles. Admin role is required.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ModuleController extends AbstractController {
	/**
	 * Fetch modules
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	protected function actionStore($orderColumn = 'id', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "") {

		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset)
				->search($searchQuery, ['t.name']);

		$modules = Module::findPermitted($query);

		$store = new Store($modules);
//		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}
	
	/**
	 * Create a new module. Use GET to fetch the default attributes or POST to add a new module.
	 *
	 * The attributes of this module should be posted as JSON in a module object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"className":"\GO\Modules\Bands\BandsModule"}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function actionCreate($returnAttributes = []) {
		
		//Check edit permission
		$module = new Module();	
		if(!User::current()->isAdmin()){
			throw new Forbidden();
		}		
		
		$module->setAttributes(App::request()->payload['data']);
		$module->save();

		return $this->renderModel($module, $returnAttributes);
	}

}
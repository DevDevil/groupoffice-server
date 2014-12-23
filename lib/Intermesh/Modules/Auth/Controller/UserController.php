<?php
namespace Intermesh\Modules\Auth\Controller;

use Intermesh\Core\App;
use Intermesh\Core\Controller\AbstractCrudController;
use Intermesh\Core\Data\Store;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Exception\NotFound;
use Intermesh\Modules\Auth\Model\User;



/**
 * The controller for users. Admin role is required.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class UserController extends AbstractCrudController {


//	protected function authenticate() {
//		return parent::authenticate() && User::current()->isAdmin();
//	}

	/**
	 * Fetch users
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	protected function actionStore($orderColumn = 'username', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = [], $where = null) {

		$query = Query::newInstance()
								->orderBy([$orderColumn => $orderDirection])
								->limit($limit)
								->offset($offset);
		
		if (!empty($searchQuery)) {			
			$query->search($searchQuery, ['t.username']);
		}

		if (!empty($where)) {

			$where = json_decode($where, true);

			if (count($where)) {
				$query
						->groupBy(['t.id'])
						->whereSafe($where);
			}
		}
		
		$users = User::find($query);

		$store = new Store($users);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}

	/**
	 * GET a list of users or fetch a single user
	 *
	 * 
	 * @param int $userId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($userId = null, $returnAttributes = []){
		
		if($userId === "current"){
			$user = User::current();
		}else
		{	
			$user = User::findByPk($userId);
		}

		if (!$user) {
			throw new NotFound();			
		}

		return $this->renderModel($user, $returnAttributes);
		
	}
	
	
	/**
	 * Get's the default data for a new user
	 * 
	 * 
	 * 
	 * @param array $returnAttributes
	 * @return array
	 */
	protected function actionNew($returnAttributes = []){
		
		$user = new User();

		return $this->renderModel($user, $returnAttributes);
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
	public function actionCreate($returnAttributes = []) {

		$user = new User();
		$user->setAttributes(App::request()->payload['data']);
		$user->save();		

		return $this->renderModel($user, $returnAttributes);
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
	 * @param int $userId The ID of the field
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($userId, $returnAttributes = []) {

		if($userId === "current"){
			$user = User::current();
		}else
		{	
			$user = User::findByPk($userId);
		}

		if (!$user) {
			throw new NotFound();
		}

		$user->setAttributes(App::request()->payload['data']);
		
		if ($this->isModified('password')) {
			
			if(!User::current()->isAdmin() && !$user->checkPassword($user->currentPassword)){
				$user->setValidationError('currentPassword', 'wrongPassword');
			}
		}
		
		$user->save();
		
		
		return $this->renderModel($user, $returnAttributes);
	}

	/**
	 * Delete a field
	 *
	 * @param int $userId
	 * @throws NotFound
	 */
	public function actionDelete($userId) {
		$user = User::findByPk($userId);

		if (!$user) {
			throw new NotFound();
		}

		$user->delete();

		return $this->renderModel($user);
	}
}
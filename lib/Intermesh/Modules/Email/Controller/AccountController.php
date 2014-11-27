<?php
namespace Intermesh\Modules\Email\Controller;

use Intermesh\Core\App;
use Intermesh\Core\Controller\AbstractCrudController;
use Intermesh\Core\Data\Store;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Exception\NotFound;
use Intermesh\Modules\Email\Model\Account;



/**
 * The controller for accounts. Admin role is required.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class AccountController extends AbstractCrudController {

	/**
	 * Fetch accounts
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	protected function actionStore($orderColumn = 'host', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = []) {

		$accounts = Account::find(Query::newInstance()
								->orderBy([$orderColumn => $orderDirection])
								->limit($limit)
								->offset($offset)
								->search($searchQuery, array('t.accountname'))
		);

		$store = new Store($accounts);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}

	/**
	 * GET a list of accounts or fetch a single account
	 *
	 * 
	 * @param int $accountId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($accountId = null, $returnAttributes = []){
	
		$account = Account::findByPk($accountId);		

		if (!$account) {
			throw new NotFound();			
		}

		return $this->renderModel($account, $returnAttributes);
		
	}
	
	
	/**
	 * Get's the default data for a new account
	 * 
	 * 
	 * 
	 * @param array $returnAttributes
	 * @return array
	 */
	protected function actionNew($returnAttributes = []){
		
		$account = new Account();

		return $this->renderModel($account, $returnAttributes);
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

		$account = new Account();
		$account->setAttributes(App::request()->payload['data']['attributes']);
		$account->save();		

		return $this->renderModel($account, $returnAttributes);
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
	 * @param int $accountId The ID of the field
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($accountId, $returnAttributes = []) {

		$account = Account::findByPk($accountId);		

		if (!$account) {
			throw new NotFound();
		}

		$account->setAttributes(App::request()->payload['data']['attributes']);
		$account->save();
		
		return $this->renderModel($account, $returnAttributes);
	}

	/**
	 * Delete a field
	 *
	 * @param int $accountId
	 * @throws NotFound
	 */
	public function actionDelete($accountId) {
		$account = Account::findByPk($accountId);

		if (!$account) {
			throw new NotFound();
		}

		$account->delete();

		return $this->renderModel($account);
	}
}
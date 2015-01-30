<?php

namespace GO\Modules\Contacts\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Modules\Timeline\Model\Item;

/**
 * The controller for timeline items
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class TimelineController extends AbstractController {

	protected function httpGet($itemId = null, $returnAttributes = []) {
		if (!isset($itemId)) {
			return $this->callMethodWithParams('store');
		} else {

			if ($itemId == 0) {
				$item = new Item();
			} else {
				
				
				$item = Item::findByPk($itemId);

				if (!$item->contact->checkPermission('editAccess')) {
					return $this->renderError(403);
				}
			}

			if (!$item) {
				return $this->renderError(404);
			}

			
			return $this->renderModel($item, $returnAttributes);
			
		}
	}

	/**
	 * Fetch items
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	public function store($orderColumn = 'createdAt', $orderDirection = 'DESC', $limit = 0, $offset = 0, $searchQuery = "", $returnAttributes = [], $where = null) {

		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset);
		
		if(!empty($searchQuery)){
			$query->search($searchQuery, ['name']);
		}
		
		if(!empty($where)){
			
			$where = json_decode($where, true);
			
			if(count($where)){
				$query					
					->groupBy(['t.id'])				
					->whereSafe($where);			
			}
		}

		$items = Item::find($query);

		$store = new Store($items);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}

	/**
	 * Create a new item. Use GET to fetch the default attributes or POST to add a new item.
	 *
	 * The attributes of this item should be posted as JSON in a item object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"item":{"attributes":{"itemname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function httpPost($contactId = 0,$returnAttributes = []) {

		$item = new Item();

		$item->setAttributes(App::request()->payload['data']);
		$item->contactId = $contactId;


		if (!$item->contact->checkPermission('editAccess')) {
			$this->renderError(403);
		}

		$item->save();
		

		return $this->renderModel($item, $returnAttributes);
	}

	/**
	 * Update a item. Use GET to fetch the default attributes or POST to add a new item.
	 *
	 * The attributes of this item should be posted as JSON in a item object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"item":{"attributes":{"itemname":"test",...}}}
	 * </code>
	 * 
	 * @param int $itemId The ID of the item
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 
	 */
	public function httpPut($itemId, $returnAttributes = []) {

		$item = Item::findByPk($itemId);

		if (!$item) {
			$this->renderError(404);
		}
		
		if (!$item->contact->checkPermission('editAccess')) {
			$this->renderError(403);
		}
		$item->setAttributes(App::request()->payload['data']);
		$item->save();
		
		return $this->renderModel($item, $returnAttributes);
	}

	
	/**
	 * Delete a file
	 *
	 * @param int $itemId
	 
	 */
	public function httpDelete($itemId) {
		$item = Item::findByPk($itemId);

		if (!$item) {
			return $this->renderError(404);
		}

		if (!$item->contact->checkPermission('editAccess')) {
			return $this->renderError(403);
		}

		$item->delete();

		return $this->renderModel($item);
	}
}

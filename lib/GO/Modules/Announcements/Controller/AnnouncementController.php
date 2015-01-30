<?php

namespace GO\Modules\Announcements\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\Forbidden;
use GO\Core\Exception\NotFound;
use GO\Modules\Announcements\Model\Announcement;

/**
 * The controller for address books
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class AnnouncementController extends AbstractController {


	/**
	 * Fetch announcements
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	public function actionStore($orderColumn = 'id', $orderDirection = 'DESC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = [], $where = null) {

		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset);

		if (!empty($searchQuery)) {
			$query->search($searchQuery, ['name']);
		}

		if (!empty($where)) {

			$where = json_decode($where, true);

			if (count($where)) {
				$query
						->groupBy(['t.id'])
						->whereSafe($where);
			}
		}

		$announcements = Announcement::find($query);


		$store = new Store($announcements);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}

	/**
	 * Create a new announcement. Use GET to fetch the default attributes or POST to add a new announcement.
	 *
	 * The attributes of this announcement should be posted as JSON in a announcement object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"announcement":{"attributes":{"announcementname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. *,emailAddresses.*. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function actionCreate($returnAttributes = []) {

		$announcement = new Announcement();
		$announcement->setAttributes(App::request()->payload['data']);
		$announcement->save();		

		return $this->renderModel($announcement, $returnAttributes);
	}
	
	public function actionNew($returnAttributes = []){
		$announcement = new Announcement();
		
		return $this->renderModel($announcement, $returnAttributes);
	}

	/**
	 * Get a announcement
	 * 
	 * @param int $id
	 * @param array $returnAttributes
	 * @throws NotFound
	 * @throws Forbidden
	 */
	public function actionRead($id, $returnAttributes = []) {
		$announcement = Announcement::findByPk($id);

		if (!$announcement) {
			throw new NotFound();
		}

		return $this->renderModel($announcement, $returnAttributes);
	}

	/**
	 * Update a announcement. Use GET to fetch the default attributes or POST to add a new announcement.
	 *
	 * The attributes of this announcement should be posted as JSON in a announcement object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"announcement":{"attributes":{"announcementname":"test",...}}}
	 * </code>
	 * 
	 * @param int $id The ID of the announcement
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($id, $returnAttributes = []) {

		$announcement = Announcement::findByPk($id);

		if (!$announcement) {
			throw new NotFound();
		}
	
		$announcement->setAttributes(App::request()->payload['data']);
		$announcement->save();
		
		return $this->renderModel($announcement, $returnAttributes);
	}

	/**
	 * Delete a announcement
	 *
	 * @param int $id
	 * @throws NotFound
	 */
	public function actionDelete($id) {
		$announcement = Announcement::findByPk($id);

		if (!$announcement) {
			throw new NotFound();
		}

		$announcement->delete();

		return $this->renderModel($announcement);
	}


}

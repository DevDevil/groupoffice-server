<?php

namespace GO\Modules\Bands\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractCrudController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\NotFound;
use GO\Modules\Bands\Model\Band;

/**
 * The controller for bands. Admin role is required.
 * 
 * Uses the {@see Band} model.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class BandController extends AbstractCrudController {

	/**
	 * Fetch bands
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @param string $where {@see \GO\Core\Db\Criteria::whereSafe()}
	 * @return array JSON Model data
	 */
	protected function actionStore($orderColumn = 'name', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = [], $where = null) {

		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset);

		if (!empty($searchQuery)) {
			$query->search($searchQuery, ['t.bandname']);
		}

		if (!empty($where)) {

			$where = json_decode($where, true);

			if (count($where)) {
				$query
						->groupBy(['t.id'])
						->whereSafe($where);
			}
		}

		$bands = Band::find($query);

		$store = new Store($bands);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}

	/**
	 * GET a list of bands or fetch a single band
	 *
	 * 
	 * @param int $bandId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($bandId = null, $returnAttributes = ['*','albums']) {

		$band = Band::findByPk($bandId);

		if (!$band) {
			throw new NotFound();
		}

		return $this->renderModel($band, $returnAttributes);
	}

	/**
	 * Get's the default data for a new band
	 * 
	 * @param array $returnAttributes
	 * @return array
	 */
	protected function actionNew($returnAttributes = ['*','albums']) {

		$band = new Band();

		return $this->renderModel($band, $returnAttributes);
	}

	/**
	 * Create a new band. Use GET to fetch the default attributes or POST to add a new band.
	 *
	 * The attributes of this band should be posted as JSON in a band object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"bandname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function actionCreate($returnAttributes = ['*','albums']) {

		$band = new Band();
		$band->setAttributes(App::request()->payload['data']);
		$band->save();

		return $this->renderModel($band, $returnAttributes);
	}

	/**
	 * Update a band. Use GET to fetch the default attributes or POST to add a new band.
	 *
	 * The attributes of this band should be posted as JSON in a band object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"bandname":"test",...}}}
	 * </code>
	 * 
	 * @param int $bandId The ID of the band
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($bandId, $returnAttributes = ['*','albums']) {

		$band = Band::findByPk($bandId);

		if (!$band) {
			throw new NotFound();
		}

		$band->setAttributes(App::request()->payload['data']);

		$band->save();


		return $this->renderModel($band, $returnAttributes);
	}

	/**
	 * Delete a band
	 *
	 * @param int $bandId
	 * @throws NotFound
	 */
	public function actionDelete($bandId) {
		$band = Band::findByPk($bandId);

		if (!$band) {
			throw new NotFound();
		}

		$band->delete();

		return $this->renderModel($band);
	}

}

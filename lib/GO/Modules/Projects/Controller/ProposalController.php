<?php

namespace GO\Modules\Projects\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\NotFound;
use GO\Modules\Projects\Model\Proposal;


/*
 * The controller for the projects module proposal model
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ProposalController extends AbstractController{	
	
	/**
	 * Fetch proposals
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	public function actionStore($orderColumn = 'id', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = [], $where = null) {

		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset);
//
//		if (!empty($searchQuery)) {
//			$query->search($searchQuery, ['name']);
//		}

		if (!empty($where)) {

			$where = json_decode($where, true);

			if (count($where)) {
				$query
						->groupBy(['t.id'])
						->whereSafe($where);
			}
		}

		$proposals = Proposal::find($query);


		$store = new Store($proposals);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}
	
	protected function actionNew($returnAttributes=[]){
		$proposal = new Proposal();
		return $this->renderModel($proposal, $returnAttributes);
	}
	
	
	/**
	 * GET a single proposal
	 *
	 * The attributes of this proposal should be posted as JSON
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"name":"test",...}}}
	 * </code>
	 * 
	 * @param int $proposalId The ID of the proposal
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($proposalId, $returnAttributes = []){
		
		$proposal = Proposal::findByPk($proposalId);
		
		if (!$proposal) {
			throw new NotFound();
		}

//
//		if (!$proposal->checkPermission('readAccess')) {
//			throw new Forbidden();
//		}

		return $this->renderModel($proposal, $returnAttributes);

	}

	
	/**
	 * Create a new proposal. Use GET to fetch the default attributes or POST to add a new proposal.
	 *
	 * The attributes of this proposal should be posted as JSON in a proposal object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"field":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function actionCreate($returnAttributes = []) {
		
		
//		if (!ProjectsModule::model()>single()->checkPermission('createAccess')) {
//			throw new Forbidden();
//		}

		$proposal = new Proposal();
		$proposal->setAttributes(App::request()->payload['data']);
		$proposal->save();
		

		return $this->renderModel($proposal, $returnAttributes);
	}

	/**
	 * Update a proposal. Use GET to fetch the default attributes or POST to add a new proposal.
	 *
	 * The attributes of this proposal should be posted as JSON in a proposal object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"field":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param int $proposalId The ID of the proposal
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($proposalId, $returnAttributes = []) {

		$proposal = Proposal::findByPk($proposalId);

		if (!$proposal) {
			throw new NotFound();
		}
		
//		if (!$proposal->checkPermission('editAccess')) {
//			throw new Forbidden();
//		}

		$proposal->setAttributes(App::request()->payload['data']);
		$proposal->save();
		
		return $this->renderModel($proposal, $returnAttributes);
	}

	/**
	 * Delete a proposal
	 *
	 * @param int $proposalId
	 * @throws NotFound
	 */
	public function actionDelete($proposalId) {
		$proposal = Proposal::findByPk($proposalId);

		if (!$proposal) {
			throw new NotFound();
		}
		
//		if (!$proposal->checkPermission('deleteAccess')) {
//			throw new Forbidden();
//		}

		$proposal->delete();

		return $this->renderModel($proposal);
	}
}
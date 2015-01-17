<?php
namespace GO\Modules\Notes\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractCrudController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\Forbidden;
use GO\Core\Exception\NotFound;
use GO\Core\Auth\Model\User;
use GO\Modules\Notes\Model\Note;
use GO\Modules\Notes\NotesModule;

/*
 * The controller for the notes module
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class NoteController extends AbstractCrudController{	
	
	/**
	 * Fetch notes
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	public function actionStore($orderColumn = 'title', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = [], $where = null) {

		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset);

		if (!empty($searchQuery)) {
			$query->search($searchQuery, ['title']);
		}

		if (!empty($where)) {

			$where = json_decode($where, true);

			if (count($where)) {
				$query
						->groupBy(['t.id'])
						->whereSafe($where);
			}
		}

		$notes = Note::findPermitted($query);


		$store = new Store($notes);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}
	
	protected function actionNew($returnAttributes=[]){
		$note = new Note();
		return $this->renderModel($note, $returnAttributes);
	}
	
	
	/**
	 * GET a list of notes or fetch a single note
	 *
	 * The attributes of this note should be posted as JSON in a role object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"name":"test",...}}}
	 * </code>
	 * 
	 * @param int $noteId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($noteId, $returnAttributes = []){
		
		if($noteId == "current"){
			$note = User::current()->note;
		}else
		{
			$note = Note::findByPk($noteId);
		}

		if (!$note) {
			throw new NotFound();
		}


		if (!$note->checkPermission('readAccess')) {
			throw new Forbidden();
		}

		return $this->renderModel($note, $returnAttributes);

	}

	
	/**
	 * Create a new field. Use GET to fetch the default attributes or POST to add a new field.
	 *
	 * The attributes of this field should be posted as JSON in a field object
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
		
		
		if (!NotesModule::model()->checkPermission('createAccess')) {
			throw new Forbidden();
		}

		$note = new Note();
		$note->setAttributes(App::request()->payload['data']);
		$note->save();
		

		return $this->renderModel($note, $returnAttributes);
	}

	/**
	 * Update a field. Use GET to fetch the default attributes or POST to add a new field.
	 *
	 * The attributes of this field should be posted as JSON in a field object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"field":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param int $noteId The ID of the field
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($noteId, $returnAttributes = []) {

		if($noteId == "current"){
			$note = User::current()->note;
		}else
		{
			$note = Note::findByPk($noteId);
		}

		if (!$note) {
			throw new NotFound();
		}
		
		if (!$note->checkPermission('editAccess')) {
			throw new Forbidden();
		}

		$note->setAttributes(App::request()->payload['data']);
		$note->save();
		
		return $this->renderModel($note, $returnAttributes);
	}

	/**
	 * Delete a field
	 *
	 * @param int $noteId
	 * @throws NotFound
	 */
	public function actionDelete($noteId) {
		$note = Note::findByPk($noteId);

		if (!$note) {
			throw new NotFound();
		}
		
		if (!$note->checkPermission('deleteAccess')) {
			throw new Forbidden();
		}

		$note->delete();

		return $this->renderModel($note);
	}
}
<?php
namespace GO\Modules\Projects\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractCrudController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\Forbidden;
use GO\Core\Exception\NotFound;
use GO\Modules\Auth\Model\User;
use GO\Modules\Projects\Model\Task;
use GO\Modules\Projects\ProjectsModule;

/*
 * The controller for the projects module to handle tasks
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class TaskController extends AbstractCrudController{	
	
	/**
	 * Fetch tasks
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

		$tasks = Task::findPermitted($query);


		$store = new Store($tasks);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}
	
	protected function actionNew($returnAttributes=[]){
		$task = new Task();
		return $this->renderModel($task, $returnAttributes);
	}
	
	
	/**
	 * GET a list of tasks or fetch a single task
	 *
	 * The attributes of this task should be posted as JSON in a role object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"name":"test",...}}}
	 * </code>
	 * 
	 * @param int $taskId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($taskId, $returnAttributes = []){
		
		if($taskId == "current"){
			$task = User::current()->task;
		}else
		{
			$task = Task::findByPk($taskId);
		}

		if (!$task) {
			throw new NotFound();
		}


		if (!$task->checkPermission('readAccess')) {
			throw new Forbidden();
		}

		return $this->renderModel($task, $returnAttributes);

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
		
		
		if (!ProjectsModule::model()->single()->checkPermission('createAccess')) {
			throw new Forbidden();
		}

		$task = new Task();
		$task->setAttributes(App::request()->payload['data']);
		$task->save();
		

		return $this->renderModel($task, $returnAttributes);
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
	 * @param int $taskId The ID of the field
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($taskId, $returnAttributes = []) {

		if($taskId == "current"){
			$task = User::current()->task;
		}else
		{
			$task = Task::findByPk($taskId);
		}

		if (!$task) {
			throw new NotFound();
		}
		
		if (!$task->checkPermission('editAccess')) {
			throw new Forbidden();
		}

		$task->setAttributes(App::request()->payload['data']);
		$task->save();
		
		return $this->renderModel($task, $returnAttributes);
	}

	/**
	 * Delete a field
	 *
	 * @param int $taskId
	 * @throws NotFound
	 */
	public function actionDelete($taskId) {
		$task = Task::findByPk($taskId);

		if (!$task) {
			throw new NotFound();
		}
		
		if (!$task->checkPermission('deleteAccess')) {
			throw new Forbidden();
		}

		$task->delete();

		return $this->renderModel($task);
	}
}
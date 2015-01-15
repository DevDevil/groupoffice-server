<?php
namespace GO\Modules\Projects\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractCrudController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\Forbidden;
use GO\Core\Exception\NotFound;
use GO\Modules\Auth\Model\User;
use GO\Modules\Projects\Model\Project;
use GO\Modules\Projects\ProjectsModule;

/*
 * The controller for the projects module
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ProjectController extends AbstractCrudController{	
	
	/**
	 * Fetch projects
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	public function actionStore($orderColumn = 'name', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = [], $where = null) {

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

		$projects = Project::findPermitted($query);


		$store = new Store($projects);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}
	
	protected function actionNew($returnAttributes=[]){
		$project = new Project();
		return $this->renderModel($project, $returnAttributes);
	}
	
	
	/**
	 * GET a list of projects or fetch a single project
	 *
	 * The attributes of this project should be posted as JSON in a role object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"name":"test",...}}}
	 * </code>
	 * 
	 * @param int $projectId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($projectId, $returnAttributes = []){
		
		if($projectId == "current"){
			$project = User::current()->project;
		}else
		{
			$project = Project::findByPk($projectId);
		}

		if (!$project) {
			throw new NotFound();
		}


		if (!$project->checkPermission('readAccess')) {
			throw new Forbidden();
		}

		return $this->renderModel($project, $returnAttributes);

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
		
		
		if (!ProjectsModule::model()>single()->checkPermission('createAccess')) {
			throw new Forbidden();
		}

		$project = new Project();
		$project->setAttributes(App::request()->payload['data']);
		$project->save();
		

		return $this->renderModel($project, $returnAttributes);
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
	 * @param int $projectId The ID of the field
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($projectId, $returnAttributes = []) {

		if($projectId == "current"){
			$project = User::current()->project;
		}else
		{
			$project = Project::findByPk($projectId);
		}

		if (!$project) {
			throw new NotFound();
		}
		
		if (!$project->checkPermission('editAccess')) {
			throw new Forbidden();
		}

		$project->setAttributes(App::request()->payload['data']);
		$project->save();
		
		return $this->renderModel($project, $returnAttributes);
	}

	/**
	 * Delete a field
	 *
	 * @param int $projectId
	 * @throws NotFound
	 */
	public function actionDelete($projectId) {
		$project = Project::findByPk($projectId);

		if (!$project) {
			throw new NotFound();
		}
		
		if (!$project->checkPermission('deleteAccess')) {
			throw new Forbidden();
		}

		$project->delete();

		return $this->renderModel($project);
	}
}
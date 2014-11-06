<?php

namespace Intermesh\Modules\Auth\Controller;

use Flow\Exception;
use Intermesh\Core\App;
use Intermesh\Core\Controller\AbstractRESTController;
use Intermesh\Core\Data\Store;
use Intermesh\Core\Db\Criteria;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Core\Exception\Forbidden;
use Intermesh\Modules\Auth\Model\Role;

class PermissionsController extends AbstractRESTController {

	public function httpGet($modelId, $modelName, $orderColumn = 'name', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "") {

		$model = $modelName::findByPk($modelId);

		if (!$model->currentUserCanManagePermissions()) {
			throw new Forbidden();
		}

		$relation = $model->getRolesRelation();
		$roleModelName = $relation->getRelatedModelName();

		$rf = new RelationFactory(Role::className());

		Role::addRuntimeRelation($rf->hasMany('permissions', $roleModelName, 'roleId'));

		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset)
				->search($searchQuery, array('t.name'))
				->joinRelation(
				'permissions', true, 'LEFT', Criteria::newInstance()->where(['permissions.' . $roleModelName::resourceKey() => $modelId])
		);



		$roles = Role::find($query);

		$store = new Store($roles);
		$store->setReturnAttributes(['*', 'permissions.*', 'permissions.permissionDependencies']);


		return $this->renderStore($store);
	}

	public function httpPut($modelId, $modelName) {

		$result = array('success' => true, 'permissions' => []);

		$model = $modelName::findByPk($modelId);

		if (!$model->currentUserCanManagePermissions()) {
			throw new Forbidden();
		}

		$relation = $model->getRolesRelation();
		$roleModelName = $relation->getRelatedModelName();


		foreach (App::request()->payload['permissions'] as $permissions) {


			$model = $roleModelName::findByPk([
						'roleId' => $permissions['roleId'],
						$roleModelName::resourceKey() => $modelId
			]);

//			var_dump($model);

			if (!$model) {
				$model = new $roleModelName;
				$model->{$roleModelName::resourceKey()} = $modelId;
			}
			$model->setAttributes($permissions);
			if (!$model->save()) {
				throw new Exception(var_export($model->getValidationErrors()));
			}
		}


		return $this->renderJson($result);
	}

}

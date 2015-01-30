<?php

namespace GO\Core\Auth\Controller;

use GO\Core\Controller\AbstractController;
use GO\Core\Data\Store;
use GO\Core\Db\Criteria;
use GO\Core\Db\Query;
use GO\Core\Auth\Model\Role;
use GO\Core\Auth\Model\UserRole;

class UserRolesController extends AbstractController {

	protected function httpGet($userId, $orderColumn = 'name', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $availableOnly = false) {

		if ($availableOnly) {
			$roles = Role::find(Query::newInstance()
									->orderBy([$orderColumn => $orderDirection])
									->limit($limit)
									->offset($offset)
									->search($searchQuery, array('t.name'))
									->joinAdvanced(
											UserRole::className(), Criteria::newInstance()
											->where('t.id = userRole.roleId')
											->andWhere(['userRole.userId' => $userId])
											, 'userRole', 'LEFT')
									->where(['userRole.roleId' => null])
			);
		} else {
			$roles = Role::find(Query::newInstance()
									->orderBy([$orderColumn => $orderDirection])
									->limit($limit)
									->offset($offset)
									->search($searchQuery, array('t.name'))
									->joinRelation('userRole')
									->groupBy(['t.id'])
									->where(['userRole.userId' => $userId]));
		}

		$store = new Store($roles);

		return $this->renderStore($store);
	}

}

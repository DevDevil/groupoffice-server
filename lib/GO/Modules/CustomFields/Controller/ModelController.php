<?php

namespace GO\Modules\CustomFields\Controller;

use GO\Core\Controller\AbstractRESTController;
use GO\Modules\Modules\ModuleUtils;

class ModelController extends AbstractRESTController{
	
	
	protected function httpGet() {
		
		$modelClasses = ModuleUtils::getModelNames();
		
		$customFieldModels = [];
		foreach($modelClasses as $modelClass){
			
			if(is_subclass_of($modelClass, "\GO\Core\Db\AbstractRecord") && ($relation = $modelClass::getRelation('customfields'))){
				$customFieldModels[] = ['modelName' => $modelClass, 'customFieldsModelName' => $relation->getRelatedModelName()];
			}
		}
		
		return $this->renderJson(['results' => $customFieldModels, 'success' => true]);
	}
}

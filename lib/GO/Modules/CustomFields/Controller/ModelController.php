<?php

namespace GO\Modules\CustomFields\Controller;

use GO\Core\Controller\AbstractController;
use GO\Core\Modules\ModuleUtils;

class ModelController extends AbstractController{
	
	
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

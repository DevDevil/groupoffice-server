<?php

namespace GO\Modules\CustomFields\Controller;

use GO\Core\Controller\AbstractController;
use GO\Core\Db\AbstractRecord;
use GO\Core\Util\ClassFinder;

class ModelController extends AbstractController{	

	protected function actionGet() {
		
		$classFinder = new ClassFinder(AbstractRecord::className());		
		$modelClasses = $classFinder->find();
		
		$customFieldModels = [];
		foreach($modelClasses as $modelClass){
			
			if($relation = $modelClass::getRelation('customfields')){
				$customFieldModels[] = ['modelName' => $modelClass, 'customFieldsModelName' => $relation->getRelatedModelName()];
			}
		}
		
		return $this->renderJson(['results' => $customFieldModels, 'success' => true]);
	}
}

<?php

namespace GO\Modules\CustomFields;

use GO\Core\AbstractModule;
use GO\Modules\CustomFields\Controller\FieldController;
use GO\Modules\CustomFields\Controller\FieldSetController;

class CustomFieldsModule extends AbstractModule {

	public function routes() {
		
		Controller\ModelController::routes()
			->get('customfields/models', 'get');
		
		FieldSetController::routes()
			->get('customfields/fieldsets', 'store')
			->get('customfields/fieldsets/0','new')
			->get('customfields/fieldsets/:fieldSetId','read')
			->put('customfields/fieldsets/:fieldSetId', 'update')
			->post('customfields/fieldsets', 'create')
			->delete('customfields/fieldsets/:fieldSetId','delete');
		
		FieldController::routes()
			->get('customfields/:fieldSetId/fields', 'store')
			->get('customfields/:fieldSetId/fields/0','new')
			->get('customfields/:fieldSetId/fields/:fieldId','read')
			->put('customfields/:fieldSetId/fields/:fieldId', 'update')
			->post('customfields/:fieldSetId/fields', 'create')
			->delete('customfields/:fieldSetId/fields/:fieldId','delete');
	}

//	return [
//			'customfields' => [
//					'children' => [
//						'models' => [
//								'controller' => Controller\ModelController::className()
//						],
//						'fieldsets' => [
//							'routeParams' => ['modelName', 'fieldSetId'],
//							'controller' => FieldSetController::className(),
//							'children' => [
//								'fields' => [
//									'routeParams' => ['fieldId'],
//									'controller' => FieldController::className(),
//								]
//							]
//						]
//					]
//				]
//		];
	
}

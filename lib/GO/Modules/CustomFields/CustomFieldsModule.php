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
			->get('customfields/fieldsets/:modelName', 'store')
			->get('customfields/fieldsets/:modelName/0','new')
			->get('customfields/fieldsets/:modelName/:fieldSetId','read')
			->put('customfields/fieldsets/:modelName/:fieldSetId', 'update')
			->post('customfields/fieldsets/:modelName', 'create')
			->delete('customfields/fieldsets/:modelName/:fieldSetId','delete');
		
		FieldController::routes()
			->get('customfields/fieldsets/:modelName/:fieldSetId/fields', 'store')
			->get('customfields/fieldsets/:modelName/:fieldSetId/fields/0','new')
			->get('customfields/fieldsets/:modelName/:fieldSetId/fields/:fieldId','read')
			->put('customfields/fieldsets/:modelName/:fieldSetId/fields/:fieldId', 'update')
			->post('customfields/fieldsets/:modelName/:fieldSetId/fields', 'create')
			->delete('customfields/fieldsets/:modelName/:fieldSetId/fields/:fieldId','delete');
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

<?php

namespace GO\Modules\CustomFields;

use GO\Core\AbstractModule;
use GO\Modules\CustomFields\Controller\FieldController;
use GO\Modules\CustomFields\Controller\FieldSetController;

class CustomFieldsModule extends AbstractModule {

	public function routes() {
		return [
			'customfields' => [
					'children' => [
						'models' => [
								'controller' => Controller\ModelController::className()
						],
						'fieldsets' => [
							'routeParams' => ['modelName', 'fieldSetId'],
							'controller' => FieldSetController::className(),
							'children' => [
								'fields' => [
									'routeParams' => ['fieldId'],
									'controller' => FieldController::className(),
								]
							]
						]
					]
				]
		];
	}

}

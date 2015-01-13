<?php

namespace Intermesh\Modules\CustomFields;

use Intermesh\Core\AbstractModule;
use Intermesh\Modules\CustomFields\Controller\FieldController;
use Intermesh\Modules\CustomFields\Controller\FieldSetController;

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

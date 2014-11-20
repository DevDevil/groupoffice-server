<?php

namespace Intermesh\Modules\Notes;

use Intermesh\Core\AbstractModule;
use Intermesh\Modules\Notes\Controller\NoteController;
use Intermesh\Modules\Notes\Controller\ThumbController;

class NotesModule extends AbstractModule {

	public static function getRoutes() {
		return [
//			'notebooks' => [
//				'routeParams' => ['notebookId'],
//				'children' => [
				'notes' => [
						'routeParams' => ['noteId'],
						'controller' => NoteController::className(),
						'children' => [
								'noteImages' => [
										'routeParams' => ['noteImageId'],
										'controller' => ThumbController::className(),
										'children' => [
												'thumb' => [
														'controller' => ThumbController::className(),
												]
										]
								]
						]
				]
//				]
//			]
		];
	}

}

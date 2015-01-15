<?php

namespace GO\Modules\Notes;

use GO\Core\AbstractModule;
use GO\Modules\Notes\Controller\NoteController;
use GO\Modules\Notes\Controller\ThumbController;

class NotesModule extends AbstractModule {

	public function routes() {
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

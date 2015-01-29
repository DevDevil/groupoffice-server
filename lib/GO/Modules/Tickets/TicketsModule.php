<?php
namespace GO\Modules\Tickets;

use GO\Core\AbstractModule;
use GO\Modules\Tickets\Controller\TicketController;

class TicketsModule extends AbstractModule {
	public function routes() {
        return [
            'tickets' => [
                'controller' => TicketController::className(),
                'routeParams' => ['id'],

                'children' => [
//                    'hello' => [
//                        'controller' => HelloController::className()
//                    ]
                ]
            ],
//			'agents'=> [
//                'controller' => AgentController::className(),
//                'routeParams' => ['id'],
//
//                'children' => [
//                ]
//            ]
        ];
    }
}
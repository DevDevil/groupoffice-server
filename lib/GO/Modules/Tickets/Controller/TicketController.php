<?php

namespace GO\Modules\Tickets\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractCrudController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\NotFound;
use GO\Modules\Tickets\Model\Ticket;

/**
 * The controller for bands. Admin role is required.
 * 
 * Uses the {@see Band} model.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class TicketController extends AbstractCrudController {

    /**
     * Fetch bands
     *
     * @param string $orderColumn Order by this column
     * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
     * @param int $limit Limit the returned records
     * @param int $offset Start the select on this offset
     * @param string $searchQuery Search on this query.
     * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
     * @param string $where {@see \GO\Core\Db\Criteria::whereSafe()}
     * @return array JSON Model data
     */
    protected function actionStore($orderColumn = 'number', $orderDirection = 'DESC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = [], $where = null) {

        $query = Query::newInstance()
                ->orderBy([$orderColumn => $orderDirection])
                ->limit($limit)
                ->offset($offset);

        if (!empty($searchQuery)) {
            $query->search($searchQuery, ['t.title']);
        }

        if (!empty($where)) {

            $where = json_decode($where, true);

            if (count($where)) {
                $query
                    ->groupBy(['t.id'])
                    ->whereSafe($where);
            }
        }

        $tickets = Ticket::findPermitted($query);

        $store = new Store($tickets);
        $store->setReturnAttributes($returnAttributes);

        return $this->renderStore($store);
    }

    /**
     * GET a list of bands or fetch a single band
     *
     * 
     * @param int $id The ID of the role
     * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
     * @return JSON Model data
     */
    protected function actionRead($id = null, $returnAttributes = ['*','roles']) {

        $ticket = Ticket::findByPk($id);

        if (!$ticket) {
            throw new NotFound();
        }
		
		if($ticket->permission()->check('read')) {
            throw new Forbidden();
        }
		
        return $this->renderModel($ticket, $returnAttributes);
    }

    /**
     * Get's the default data for a new band
     * 
     * @param array $returnAttributes
     * @return array
     */
    protected function actionNew($returnAttributes = ['*']) {

        $ticket = new Ticket();

        return $this->renderModel($ticket, $returnAttributes);
    }

    /**
     * Create a new band. Use GET to fetch the default attributes or POST to add a new band.
     *
     * The attributes of this band should be posted as JSON in a band object
     *
     * <p>Example for POST and return data:</p>
     * <code>
     * {"data":{"attributes":{"bandname":"test",...}}}
     * </code>
     * 
     * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
     * @return JSON Model data
     */
    public function actionCreate($returnAttributes = ['*']) {

        $band = new Ticket();
        $band->setAttributes(App::request()->payload['data']);
        $band->save();

        return $this->renderModel($band, $returnAttributes);
    }

    /**
     * Update a band. Use GET to fetch the default attributes or POST to add a new band.
     *
     * The attributes of this band should be posted as JSON in a band object
     *
     * <p>Example for POST and return data:</p>
     * <code>
     * {"data":{"attributes":{"bandname":"test",...}}}
     * </code>
     * 
     * @param int $id The ID of the band
     * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
     * @return JSON Model data
     * @throws NotFound
     */
    public function actionUpdate($id, $returnAttributes = ['*']) {

        $ticket = Ticket::findByPk($id);

        if (!$ticket) {
            throw new NotFound();
        }

        $ticket->setAttributes(App::request()->payload['data']);

        $ticket->save();

        return $this->renderModel($ticket, $returnAttributes);
    }

    /**
     * Delete a ticket
     *
     * @param int $bandId
     * @throws NotFound
     */
    public function actionDelete($id) {
        $ticket = Ticket::findByPk($id);

        if (!$ticket) {
            throw new NotFound();
        }

        $ticket->delete();

        return $this->renderModel($ticket);
    }

}
<?php

namespace Intermesh\Modules\Tags\Controller;

use Intermesh\Core\App;
use Intermesh\Core\Controller\AbstractCrudController;
use Intermesh\Core\Data\Store;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Exception\NotFound;
use Intermesh\Modules\Tags\Model\Tag;

/**
 * The controller for address books
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class TagController extends AbstractCrudController {


	/**
	 * Fetch tags
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	public function actionStore($orderColumn = 'name', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = []) {


		$findParams = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset)
				->search($searchQuery, ['name']);


		$tags = Tag::find($findParams);

		$store = new Store($tags);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}
	
	/**
	 * Create a new tag. Use GET to fetch the default attributes or POST to add a new tag.
	 *
	 * The attributes of this tag should be posted as JSON in a tag object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"tag":{"attributes":{"tagname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function actionNew($returnAttributes = []) {

		$tag = new Tag();

		return $this->renderModel($tag, $returnAttributes);
	}

	/**
	 * Create a new tag. Use GET to fetch the default attributes or POST to add a new tag.
	 *
	 * The attributes of this tag should be posted as JSON in a tag object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"tag":{"attributes":{"tagname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function actionCreate($returnAttributes = []) {

		$tag = new Tag();

		$tag->setAttributes(App::request()->payload['data']['attributes']);
		$tag->save();

		return $this->renderModel($tag, $returnAttributes);
	}
	
	public function actionRead($tagId, $returnAttributes = []){
		$tag = Tag::findByPk($tagId);

		if (!$tag) {
			throw new NotFound();
		}
		
		return $this->renderModel($tag, $returnAttributes);
	}

	/**
	 * Update a tag. Use GET to fetch the default attributes or POST to add a new tag.
	 *
	 * The attributes of this tag should be posted as JSON in a tag object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"tag":{"attributes":{"tagname":"test",...}}}
	 * </code>
	 * 
	 * @param int $tagId The ID of the tag
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($tagId, $returnAttributes = []) {

		$tag = Tag::findByPk($tagId);

		if (!$tag) {
			throw new NotFound();
		}

		$tag->setAttributes(App::request()->payload['data']['attributes']);
		$tag->save();

		return $this->renderModel($tag, $returnAttributes);
	}

	/**
	 * Delete a tag
	 *
	 * @param int $tagId
	 * @throws NotFound
	 */
	public function actionDelete($tagId) {
		$tag = Tag::findByPk($tagId);

		if (!$tag) {
			throw new NotFound();
		}

		$tag->delete();

		return $this->renderModel($tag, $returnAttributes);
	}

}

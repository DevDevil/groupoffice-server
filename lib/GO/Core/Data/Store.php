<?php
namespace GO\Core\Data;

use Closure;
use Exception;
use GO\Core\AbstractObject;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Finder;
use GO\Core\Db\Query;
use GO\Core\Db\Relation;

/**
 * Data store object
 *
 * Create a store response with this class.
 *
 * <p>Example</p>
 * <code>
 * public function actionStore($orderColumn='username', $orderDirection='ASC', $limit=10, $offset=0, $searchQuery=""){

		$users = User::find(Query::newInstance()
						->orderBy([$orderColumn => $orderDirection])
						->limit($limit)
						->offset($offset)
						->search($searchQuery, array('t.username','t.email'))
						);

		$store = new Store($users);


		if(isset(App::request()->post['returnAttributes'])){
			$store->setReturnAttributes(App::request()->post['returnAttributes']);
		}

		$store->format('specialValue', function(User $model){
			return $model->username." is special";
		});

		echo $this->view->render('store', $store);
	}
 * </code>
 */
class Store extends AbstractObject{

	/**
	 *
	 * @var Finder
	 */
	private $_finder;


	private $_formatters=array();

	private $_returnAttributes=array();

	/**
	 * The default Query limit if none given
	 *
	 * @see Query::limit()
	 * @var int
	 */
	public $defaultLimit=10;

	/**
	 * Constructor of the store
	 *
	 * @param Finder $finder
	 * @throws Exception
	 */
	public function __construct(Finder $finder) {
		parent::__construct();

		$this->_finder=$finder;

//		if($finder->getQuery()->limit > $this->maxLimit){
//			throw new Exception("Limit may not be greater than ".$this->maxLimit);
//		}

		if(!isset($finder->getQuery()->limit)){
			$finder->getQuery()->limit($this->defaultLimit);
		}
	}

	/**
	 * Format a record attribute.
	 *
	 * @param string $attibuteName Name of the record attribute
	 * @param Closure $function The function is called with the [[ActiveRecord]] model as argument
	 */
	public function format($attibuteName, Closure $function){
		$this->_formatters[$attibuteName]=$function;
	}

	/**
	 * Set the attributes to return from the model. It also adjust the select part
	 * in the SQL query and automatically joins relations.
	 *
	 * @see AbstractRecord::getAttributes()
	 * @param array|string $returnAttributes comma separated string can be provided or array.
	 */
	public function setReturnAttributes($returnAttributes=[]){

		if(empty($returnAttributes)){
			return $this;
		}
		
		$this->_returnAttributes = AbstractRecord::parseReturnAttributes($returnAttributes);
		
		

//		$arClassName = $this->_finder->recordClassName;
//		$columns = Columns::getColumns($arClassName);
//		$selectAll=false;
//		$select = $this->_finder->getQuery()->select.',';
		
		foreach($this->_returnAttributes as $attribute){

//			if($attribute==='*'){
//				$selectAll = true;
//			}

			$parts = explode('.', $attribute);

			if(count($parts)>1){
				

				array_pop($parts);			
				$relation = call_user_func([$this->_finder->recordClassName, 'getRelation'], $parts[0]);				
				/* @var $relation Relation */
				
				if(!$relation){
					throw new Exception("Relation '".$parts[0]."' not found!");
				}
				
				if($relation->isA(Relation::TYPE_BELONGS_TO) || $relation->isA(Relation::TYPE_HAS_ONE)){				
					$relationDef = implode('.', $parts);
					if(!isset($this->_finder->getQuery()->joinRelations[$relationDef])){
						$this->_finder->getQuery()->joinRelation($relationDef, true, 'LEFT');
					}
				}
			}else
			{
//				if(!$selectAll && isset($columns[$attribute])){
//					$select .= 't.`'.$attribute.'`, ';
//				}
			}
		}

//		if(!$selectAll){
//			$this->_finder->getQuery()->select(trim($select,' ,'));
//		}else
//		{
//			$this->_finder->getQuery()->select('t.*');
//		}
		
		return $this;
	}

	private function _formatRecord($record, $model){
		foreach($this->_formatters as $attributeName=>$function){
//			if(empty($this->_returnAttributes) || isset($this->_returnAttributes['attributes'][$attributeName])){
				$record['attributes'][$attributeName]=$function($model);
//			}
		}

		return $record;
	}

	/**
	 * Get's the store records
	 *
	 * @return array Records
	 */
	public function getRecords(){
		$records = [];

		foreach($this->_finder as $model){
			
			/* @var $model AbstractRecord */

			$record = $model->toArray($this->_returnAttributes);
			$records[]=$this->_formatRecord($record, $model);
		}
		
		return $records;
	}
}
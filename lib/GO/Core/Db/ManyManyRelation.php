<?php

namespace GO\Core\Db;

/**
 * The ManyMany relation object. eg.
 * users and roles have a many many relation.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ManyManyRelation extends Relation {

	/**
	 * The name of the link model eg. authRoleUser
	 *
	 * @var string
	 */
	public $linkModelClassName;

	/**
	 * Set's the link model on a many many relation.
	 *
	 * Eg. a user has a many many relation with roles. The link model
	 *
	 * is authRoleUser in this case. It connects the User and Role models.
	 *
	 * @param string $linkModelClassName The name of the link model eg. authRoleUser
	 * @param string $linkModelMainTableColumn The column name that refers to the main table. eg. userId.
	 * @return \GO\Core\Db\ManyManyRelation
	 */
	public function setLinkModel($linkModelClassName) {
		$this->linkModelClassName = $linkModelClassName;

		return $this;
	}

	public function find(AbstractRecord $model, Criteria $extraQuery = null) {

		$linkTableAlias = 'manyManyLink';
		$relatedModelName = $this->relatedModelName;

		$query = Query::newInstance()
//						->select('t.*, manyManyLink.*')
						->joinModel($this->linkModelClassName, 'id', $linkTableAlias, $this->foreignKey);
		
		$query->andWhere([$linkTableAlias.'.'.$this->key => $model->{$model->primaryKeyColumn()}]);

		if (isset($extraQuery)) {
			$query->mergeWith($extraQuery);
		}

		return $relatedModelName::find($query);
	}

	/**
	 * Set's the relation of $model to $value
	 *
	 * @param \GO\Core\Db\AbstractRecord $model
	 * @param AbstractRecord[]|int[] $value Primary keys
	 */
	public function set(AbstractRecord $model, &$value) {

		$linkModelName = $this->linkModelClassName;

		$relatedModelName = $this->relatedModelName;
		
		$foreignKeys = array();

		foreach ($value as $foreignKey) {

			if (is_array($foreignKey)) {
				//Array of attributes of the related model
				
				$delete = isset($foreignKey['attributes']['markDeleted']);
					
				if(!isset($foreignKey['attributes'][$relatedModelName::primaryKeyColumn()])){
					
					if($delete){
						//The client created a new one but also deleted it. Skip it.
						continue;
					}
					
					$relatedModel = new $relatedModelName;
					$relatedModel->setAttributes($foreignKey['attributes']);
					if(!$relatedModel->save()){
						return false;
					}
					
					$foreignKey=$relatedModel->{$relatedModel->primaryKeyColumn()};
				}else
				{
					$foreignKey= $foreignKey['attributes'][$relatedModelName::primaryKeyColumn()];
				}
				
								
				
			} elseif (is_a($foreignKey, AbstractRecord::className())) {
				$foreignKey = $foreignKey->{$foreignKey->primaryKeyColumn()};
				$delete=false;
			}
			
			$primaryKey = array(
						$this->key => $model->id,
						$this->foreignKey => $foreignKey);

			$foreignKeys[] = $primaryKey[$this->foreignKey];
			
			$link = $linkModelName::findByPk($primaryKey);

			if (!$link && !$delete) {
				$manyMany = $linkModelName::newInstance()
								->setAttributes($primaryKey);
				
				if(!$manyMany->save()){					
					$this->failedRelatedModel=$manyMany;
					return false;					
				}
								
			}elseif($link && $delete){				
				if(!$link->delete()){
					return false;
				}
			}
		}
		
		return true;
	}
}

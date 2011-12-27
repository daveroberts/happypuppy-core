<?php

namespace HappyPuppy;
require('Relation.php');
require("hasManyRelations.php");
require("belongsToRelations.php");
require("hasOneRelations.php");
require("habtmRelations.php");

class Relations
{
	private $_has_many;
	private $_belongs_to;
	private $_has_one;
	private $_habtm;
	private $_model;
	function __construct($model){
		$this->_model = $model;
		$this->_has_many = new HasManyRelations($model);
		$this->_belongs_to = new BelongsToRelations($model);
		$this->_has_one = new HasOneRelations($model);
		$this->_habtm = new HabtmRelations($model);
	}
	
	// methods to add relations
	public function addHasMany($relation_name, $sort_by='', $foreign_class='', $foreign_table = '',$foreign_key = ''){
		$has_many_relation = new hasManyRelation($this->_model, $relation_name, $sort_by, $foreign_class, $foreign_table, $foreign_key);
		$this->_has_many->addRelation($has_many_relation);
	}
	public function addBelongsTo($relation_name, $foreign_class='', $foreign_table = '',$foreign_key = ''){
		$belongs_to_relation = new belongsToRelation($this->_model, $relation_name, $foreign_class, $foreign_table, $foreign_key);
		$this->_belongs_to->addRelation($belongs_to_relation);
	}
	public function addHasOne($relation_name, $foreign_class='', $foreign_table = '', $foreign_key = ''){
		$has_one_relation = new hasOneRelation($this->_model, $relation_name, $foreign_class, $foreign_table, $foreign_key);
		$this->_has_one->addRelation($has_one_relation);
	}
	public function AddHabtm($relation_name, $sort_by='', $foreign_class='', $foreign_table = '', $foreign_table_pk='', $link_table = '', $link_table_fk_here = '', $link_table_fk_foreigntable = ''){
		$habtm_relation = new habtmRelation($this->_model, $relation_name, $sort_by, $foreign_class, $foreign_table, $foreign_table_pk, $link_table, $link_table_fk_here, $link_table_fk_foreigntable);
		$this->_habtm->addRelation($habtm_relation);
	}
	
	// does this relation exist?
	public function hasRelation($relation_name){
		// iterate through all relation stores.  Return true if any return true
		if ($this->_has_many->hasRelation($relation_name)){ return true; }
		if ($this->_belongs_to->hasRelation($relation_name)){ return true; }
		if ($this->_has_one->hasRelation($relation_name)){ return true; }
		if ($this->_habtm->hasRelation($relation_name)){ return true; }
		return false;
	}
	
	// return the relation object
	// TODO see how this is used
	public function getRelationType($relation_name){
		if ($this->_has_many->hasRelation($relation_name)){ return $this->_has_many->getRelationType($relation_name); }
		if ($this->_belongs_to->hasRelation($relation_name)){ return $this->_belongs_to->getRelationType($relation_name); }
		if ($this->_has_one->hasRelation($relation_name)){ return $this->_has_one->getRelationType($relation_name); }
		if ($this->_habtm->hasRelation($relation_name)){ return $this->_habtm->getRelationType($relation_name); }
		throw new \Exception("No relationship found with name ".$relation_name);
	}
	
	// return the values of the relation object
	public function getRelationValues($relation_name, &$debug){
		if ($this->_has_many->hasRelation($relation_name)){ return $this->_has_many->getRelationValues($relation_name, $debug); }
		if ($this->_belongs_to->hasRelation($relation_name))
		{
			$relation = $this->_belongs_to->getRelationType($relation_name);
			$fk = $relation->foreign_key;
			if (IdentityMap::is_set($relation->foreign_table, $this->_model->$fk))
			{
				return IdentityMap::get($relation->foreign_table, $this->_model->$fk);
			}
			else
			{
				return $this->_belongs_to->getRelationValues($relation_name, $debug);
			}
		}
		if ($this->_has_one->hasRelation($relation_name)){ return $this->_has_one->getRelationValues($relation_name, $debug); }
		if ($this->_habtm->hasRelation($relation_name)){ return $this->_habtm->getRelationValues($relation_name, $debug); }
		throw new \Exception("No relationship found with name ".$relation_name);
	}
	// TODO see how this is used
	// does not call DB
	public function setRelation($relation_name, $new_value){
		if ($this->_has_many->hasRelation($relation_name))
		{
			return $this->_has_many->setRelation($relation_name, $new_value);
		}
		if ($this->_belongs_to->hasRelation($relation_name))
		{
			return $this->_belongs_to->setRelation($relation_name, $new_value);
		}
		if ($this->_has_one->hasRelation($relation_name))
		{
			return $this->_has_one->setRelation($relation_name, $new_value);
		}
		if ($this->_habtm->hasRelation($relation_name))
		{
			return $this->_habtm->setRelation($relation_name, $new_value);
		}
		throw new \Exception("No relationship found with name ".$relation_name);
	}
	public function buildFromForm($arr){
		foreach($arr as $k=>$v){
			if (strcmp(substr($k, 0, 8), "rel_ids_") == 0) {
				$relation_name = substr($k, 8);
				$this->setRelationIDs($relation_name, $v);
			}
		}
	}
	// does not call DB
	public function setRelationIDs($relation_name, $ids){
		if ($this->_has_many->hasRelation($relation_name))
		{
			return $this->_has_many->setRelationIDs($relation_name, $ids);
		}
		if ($this->_belongs_to->hasRelation($relation_name))
		{
			return $this->_belongs_to->setRelationIDs($relation_name, $ids);
		}
		if ($this->_has_one->hasRelation($relation_name))
		{
			return $this->_has_one->setRelationIDs($relation_name, $ids);
		}
		if ($this->_habtm->hasRelation($relation_name))
		{
			return $this->_habtm->setRelationIDs($relation_name, $ids);
		}
		return false;
	}
	// does not call DB
	public function addIntoRelation($relation_name, $key, $value, $fromDB = false){
		if ($this->_has_many->hasRelation($relation_name))
		{
			return $this->_has_many->addIntoRelation($relation_name, $key, $value, $fromDB);
		}
		if ($this->_belongs_to->hasRelation($relation_name))
		{
			return $this->_belongs_to->addIntoRelation($relation_name, $key, $value, $fromDB);
		}
		if ($this->_has_one->hasRelation($relation_name))
		{
			return $this->_has_one->addIntoRelation($relation_name, $key, $value, $fromDB);
		}
		if ($this->_habtm->hasRelation($relation_name))
		{
			return $this->_habtm->addIntoRelation($relation_name, $key, $value, $fromDB);
		}
		return false;
	}
	
	public function save(&$debug, $stop_before_alter){
		$result = $this->_has_many->saveAllRelations($debug, $stop_before_alter);
		if (!$result){ return false; }
		$result = $this->_habtm->saveAllRelations($debug, $stop_before_alter);
		if (!$result){ return false; }
		$result = $this->_has_one->saveAllRelations($debug, $stop_before_alter);
		if (!$result){ return false; }
		return true;
	}
	public function destroy($destroy_dependents, &$debug, $stop_before_alter){
		$this->_has_many->destroy($destroy_dependents, $debug, $stop_before_alter);
		$this->_belongs_to->destroy($destroy_dependents, $debug, $stop_before_alter);
		$this->_has_one->destroy($destroy_dependents, $debug, $stop_before_alter);
		$this->_habtm->destroy($destroy_dependents, $debug, $stop_before_alter);
		return true;
	}
	
	public function prettyPrint(){
		$out = '';
		$out .= $this->_has_many->prettyPrint();
		$out .= $this->_belongs_to->prettyPrint();
		$out .= $this->_has_one->prettyPrint();
		$out .= $this->_habtm->prettyPrint();
		return $out;
	}
}

?>

<?php

namespace HappyPuppy;
require('Relation.php');
require("hasManyRelations.php");
require("hasOneRelations.php");
require("habtmRelations.php");

class Relations
{
	private $_has_many;
	private $_has_one;
	private $_habtm;
	private $_dbobject;
	function __construct($dbobject){
		$this->_dbobject = $dbobject;
		$this->_has_many = new HasManyRelations($dbobject);
		$this->_has_one = new HasOneRelations($dbobject);
		$this->_habtm = new HabtmRelations($dbobject);
	}
	
	// methods to add relations
	public function addHasMany($relation_name, $sort_by='', $foreign_class='', $foreign_table = '',$foreign_key = ''){
		$has_many_relation = new hasManyRelation($this->_dbobject, $relation_name, $sort_by, $foreign_class, $foreign_table,$foreign_key);
		$this->_has_many->addRelation($has_many_relation);
	}
	public function addHasOne($relation_name, $foreign_class='', $foreign_table = '', $foreign_key = ''){
		$has_one_relation = new hasOneRelation($this->_dbobject, $relation_name, $foreign_class, $foreign_table, $foreign_key);
		$this->_has_one->addRelation($has_one_relation);
	}
	public function AddHabtm($relation_name, $sort_by='', $foreign_class='', $foreign_table = '', $foreign_table_pk='', $link_table = '', $link_table_fk_here = '', $link_table_fk_foreigntable = ''){
		$habtm_relation = new habtmRelation($this->_dbobject, $relation_name, $sort_by, $foreign_class, $foreign_table, $foreign_table_pk, $link_table, $link_table_fk_here, $link_table_fk_foreigntable);
		$this->_habtm->addRelation($habtm_relation);
	}
	
	// does this relation exist?
	public function hasRelation($relation_name){
		// iterate through all relation stores.  Return true if any return true
		if ($this->_has_many->hasRelation($relation_name)){ return true; }
		if ($this->_has_one->hasRelation($relation_name)){ return true; }
		if ($this->_habtm->hasRelation($relation_name)){ return true; }
		return false;
	}
	
	// return the relation object
	// TODO see how this is used
	public function getRelationType($relation_name){
		if ($this->_has_many->hasRelation($relation_name)){ return $this->_has_many->getRelationType($relation_name); }
		if ($this->_has_one->hasRelation($relation_name)){ return $this->_has_one->getRelationType($relation_name); }
		if ($this->_habtm->hasRelation($relation_name)){ return $this->_habtm->getRelationType($relation_name); }
		return null;
	}
	
	// return the values of the relation object
	public function getRelationValues($relation_name){
		if ($this->_has_many->hasRelation($relation_name)){ return $this->_has_many->getRelationValues($relation_name); }
		if ($this->_has_one->hasRelation($relation_name)){ return $this->_has_one->getRelationValues($relation_name); }
		if ($this->_habtm->hasRelation($relation_name)){ return $this->_habtm->getRelationValues($relation_name); }
		return null;
	}
	// TODO see how this is used
	public function setRelation($relation_name, $new_value){
		if ($this->_has_many->hasRelation($relation_name))
		{
			return $this->_has_many->setRelation($relation_name, $new_value);
		}
		if ($this->_has_one->hasRelation($relation_name))
		{
			return $this->_has_one->setRelation($relation_name, $new_value);
		}
		if ($this->_habtm->hasRelation($relation_name))
		{
			return $this->_habtm->setRelation($relation_name, $new_value);
		}
		return false;
	}
	public function buildFromForm($arr){
		foreach($arr as $k=>$v){
			if (strcmp(substr($k, 0, 8), "rel_ids_") == 0) {
				$relation_name = substr($k, 8);
				$this->setRelationIDs($relation_name, $v);
			}
		}
	}
	public function setRelationIDs($relation_name, $ids){
		if ($this->_has_many->hasRelation($relation_name))
		{
			return $this->_has_many->setRelationIDs($relation_name, $ids);
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
	public function addIntoRelation($relation_name, $key, $value, $fromDB = false){
		if ($this->_has_many->hasRelation($relation_name))
		{
			return $this->_has_many->addIntoRelation($relation_name, $key, $value, $fromDB);
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
	
	public function save($debug = false){
		$result = $this->_has_many->saveAllRelations($debug);
		if (!$result){ return false; }
		$result = $this->_habtm->saveAllRelations($debug);
		if (!$result){ return false; }
		$result = $this->_has_one->saveAllRelations($debug);
		if (!$result){ return false; }
		return true;
	}
	public function destroy($destroy_dependents){
		$this->_has_many->destroy($destroy_dependents);
		$this->_has_one->destroy($destroy_dependents);
		$this->_habtm->destroy($destroy_dependents);
		return true;
	}
	
	public function prettyPrint(){
		$out = '';
		$out .= $this->_has_many->prettyPrint();
		$out .= $this->_has_one->prettyPrint();
		$out .= $this->_habtm->prettyPrint();
		return $out;
	}
}

?>

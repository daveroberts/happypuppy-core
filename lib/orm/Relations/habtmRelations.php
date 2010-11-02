<?php

namespace HappyPuppy;
require('habtmRelation.php');
require_once('RelationCollection.php');

class HabtmRelations extends RelationCollection
{
	function __construct($dbobject){
		parent::__construct($dbobject, true);
	}
	protected function doBuildRelation($name){
		$relation = $this->_relations[$name];
		$sort_by = $relation->sort_by;
		$link_table = $relation->link_table;
		$foreign_table = $relation->foreign_table;
		$foreign_class = $relation->foreign_class;
		$link_table_fk_here = $relation->link_table_fk_here;
		$link_table_fk_foreigntable = $relation->link_table_fk_foreigntable;
		$foreign_table_pk = $relation->foreign_table_pk;
		$cur_object_tablename = $this->_dbobject->tablename;
		$pk = $this->_dbobject->pk;
		$pk_val = $this->_dbobject->$pk;
		$sql = "SELECT f.* FROM ".$foreign_table." f ";
		$sql .=" LEFT JOIN ".$link_table." fp ON f.".$foreign_table_pk.'=fp.'.$link_table_fk_foreigntable." ";
		$sql .=" LEFT JOIN ".$cur_object_tablename." p ON fp.".$link_table_fk_here.'=p.'.$pk." ";
		$sql .=" WHERE p.".$pk."='".$pk_val."' ";
		if ($sort_by != "")
		{
			$sql .= " ORDER BY f.".$sort_by." ";
		}
		$db_results = DB::query($sql);
		$this->_cached_values[$name] = array();
		foreach($db_results as $db_row)
		{
			$obj = new $foreign_class();
			$obj->buildFromDB($db_row);
			$this->_cached_values[$name][] = $obj;
		}
	}
	
	protected function doSetRelationIDs($relation_name, $ids){
		if (!$this->hasRelation($relation_name)){ throw new Exception("No relation named ".$relation_name); }
		if ($ids == null){ $ids = array(); }
		if (!is_array($ids)){ throw new Exception($relation_name." must be set to an array"); }
		$relation = $this->_relations[$relation_name];
		unset($this->_cached_values[$relation_name]);
		$this->_cached_values[$relation_name] = array();
		$foreign_class = $relation->foreign_class;
		foreach($ids as $id)
		{
			$obj = $foreign_class::Get($id);
			$this->_cached_values[$relation_name][$id] = $obj;
		}
	}
	public function doSetRelation($relation_name, $value){
		if (!like_array($value)){ throw new Exception($relation_name." can only be an array, even if it's one item"); }
		$this->_cached_values[$relation_name] = array();
		foreach($value as $obj)
		{
			$pk_col = $obj->pk;
			$pk_val = $obj->$pk_col;
			$this->_cached_values[$relation_name][$pk_val] = $obj;
		}
	}
	
	public function saveRelation($relation_name, $new_ids, $debug = false){
		if (!$this->hasRelation($relation_name)){ throw new Exception("No relation named ".$relation_name); }
		if (!is_array($new_ids)){ throw new Exception($relation_name." must be set to an array"); }
		$relation = $this->_relations[$relation_name];
		
		// get the old habtm IDs
		$old_ids = array();
		
		$this_pk_col = $this->_dbobject->pk;
		$this_pk_val = $this->_dbobject->$this_pk_col;
		$link_here_col = $relation->link_table_fk_here;
		$link_foreign_col = $relation->link_table_fk_foreigntable;
		$link_table = $relation->link_table;
		
		$sql = "SELECT lt.".$link_foreign_col." FROM ".$link_table." lt where lt.".$link_here_col."=".$this_pk_val;
		if ($debug){ print($sql); }
		$db_results = DB::query($sql);
		foreach($db_results as $db_row)
		{
			$old_ids[] = $db_row[$link_foreign_col];
		}
		
		// iterate over the db values
		foreach($old_ids as $old_id){
			// get the objects primary key, is this an object we will need to update?
			if (in_array($old_id, $new_ids))
			{
				// we already have this one
				$key = array_search($old_id, $new_ids);
				unset($new_ids[$key]);
			}
			else
			{
				// delete the link between these two objects
				$sql = "DELETE FROM ".$relation->link_table." WHERE ";
				$sql .= $link_here_col."=".$this_pk_val." AND ".$link_foreign_col."=".$old_id." LIMIT 1";
				if ($debug){ print($sql); }
				else { $db_results = DB::query($sql); }
			}
		}
		// Update the entries which where not already pointing here
		$gen_obj = new $relation->foreign_class();
		$foreign_pk_col = $gen_obj->pk;
		$this_pk_col = $this->_dbobject->pk;
		$this_pk_val = $this->_dbobject->$this_pk_col;
		foreach($new_ids as $new_id)
		{
			$sql = "INSERT INTO ".$relation->link_table." ";
			$sql .= "(".$link_here_col.", ".$link_foreign_col.") VALUES ";
			$sql .= "(".$this_pk_val.", ".$new_id.")";
			if ($debug){ print($sql); }
			else { $db_results = DB::query($sql); }
		}
		if ($debug){ return false; }
		$this->buildRelation($relation_name);
		return true;
	}
	public function destroy($destroy_dependents){
		foreach($this->_relations as $relation){
			$this_pk_val = $this->_dbobject->pkval;
			$link_here_col = $relation->link_table_fk_here;
			$link_table = $relation->link_table;

			$sql = "DELETE FROM $link_table WHERE $link_here_col = ".addslashes($this_pk_val);
			$db_results = DB::exec($sql);
		}
	}
}

?>

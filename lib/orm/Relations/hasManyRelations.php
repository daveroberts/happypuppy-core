<?php

namespace HappyPuppy;
require('hasManyRelation.php');
require_once('RelationCollection.php');

class HasManyRelations extends RelationCollection
{
	function __construct($model){
		parent::__construct($model, true);
	}
	protected function doBuildRelation($name, &$debug){
		$relation = $this->_relations[$name];
		$sort_by = $relation->sort_by;
		$foreign_table = $relation->foreign_table;
		$foreign_class = $relation->foreign_class;
		$foreign_model = new $foreign_class();
		$foreign_key = $relation->foreign_key;
		$tablename = $this->_model->tablename;
		$pk = $this->_model->pk;
		$pk_val = $this->_model->$pk;
		$sql = "SELECT a.* FROM ".$foreign_table." a ";
		$sql .=" LEFT JOIN ".$tablename." b ON a.".$foreign_key.'=b.'.$pk." ";
		$sql .=" WHERE b.".$pk."='".$pk_val."' ";
		if ($sort_by != "")
		{
			$sql .= " ORDER BY a.".$sort_by." ";
		}
		
		$debug[] = $sql;
		
		$this->_cached_values[$name] = array();
		
		// if we have the foreign table fully loaded, then just pull cached results
		if (isset(Model::$all_rows_loaded[$foreign_table]) &&
					Model::$all_rows_loaded[$foreign_table])
		{
			foreach(IdentityMap::GetAll($foreign_table) as $obj)
			{
				$pk_string = $obj->pk;
				if ($obj->$foreign_key == $pk_val)
				{
					$this->_cached_values[$name][$obj->$pk_string] = $obj;
				}
			}
			return;
		}
		
		$db_results = DB::query($sql);
		foreach($db_results as $db_row)
		{
			$obj = new $foreign_class();
			$pk_string = $obj->pk;
			$obj->buildFromDB($db_row);
			$this->_cached_values[$name][$obj->$pk_string] = $obj;
		}
	}
	
	protected function doSetRelationIDs($relation_name, $ids){
		if (!$this->hasRelation($relation_name)){ throw new Exception("No relation named ".$relation_name); }
		if (!is_array($ids)){ throw new Exception($relation_name." must be set to an array"); }
		$relation = $this->_relations[$relation_name];
		unset($this->_cached_values[$relation_name]);
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
	
	public function saveRelation($relation_name, $new_ids, &$debug, $stop_before_alter){
		throw new Exception("Need to rethink this");
		if (!$this->hasRelation($relation_name)){ throw new Exception("No relation named ".$relation_name); }
		if (!is_array($new_ids)){ throw new Exception($relation_name." must be set to an array"); }
		$relation = $this->_relations[$relation_name];
		
		// get the old hasMany IDs
		$old_ids = array();
		
		$this_pk_col = $this->_model->pk;
		$this_pk_val = $this->_model->$this_pk_col;
		$foreign_fk_col = $relation->foreign_key;
		$foreign_table = $relation->foreign_table;
		$gen_obj = new $relation->foreign_class();
		$foreign_table_pk = $gen_obj->pk;

		$sql = "SELECT t.".$foreign_table_pk." FROM ".$foreign_table." t where t.".$foreign_fk_col."=".$this_pk_val;
		if ($debug){ print($sql); }
		else { $db_results = DB::query($sql); }
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
				$sql = "UPDATE ".$foreign_table." SET ".$foreign_fk_col."=NULL WHERE ".$foreign_table_pk."=".$old_id." LIMIT 1";
				if ($debug){ print($sql); }
				else { DB::exec($sql); }
			}
		}
		// Update the entries which where not already pointing here
		
		foreach($new_ids as $new_id)
		{
			$sql = "UPDATE ".$foreign_table." SET ".$foreign_fk_col."=".$new_id." WHERE ".$foreign_table_pk."=".$new_id." LIMIT 1";			$db_results = DB::query($sql);
			if ($debug){ print($sql); }
			else { DB::exec($sql); }
		}
		if ($debug){ return false; }
		$this->buildRelation($relation_name, $debug);
		return true;
	}
	public function destroy($destroy_dependents, &$debug, $stop_before_alter){
		foreach($this->_relations as $relation_name=>$relation){
			if ($destroy_dependents){
				$obj_arr = $this->_model->$relation_name;
				foreach($obj_arr as $obj){
					$obj->destroy($debug, $debug_log);
				}
			} else {
				$this_pk_val = $this->_model->pkval;
				$foreign_fk_col = $relation->foreign_key;
				$foreign_table = $relation->foreign_table;

				$sql = "UPDATE $foreign_table SET $foreign_fk_col = NULL WHERE $foreign_fk_col = ".addslashes($this_pk_val);
				$debug[] = $sql;
				if ($stop_before_alter){ $debug[] = "Stopped SQL execution"; return false; }
				$db_results = DB::exec($sql);
			}
		}
	}
}

?>
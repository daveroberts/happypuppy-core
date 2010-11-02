<?php

namespace HappyPuppy;
require('hasOneRelation.php');
require_once('RelationCollection.php');

class HasOneRelations extends RelationCollection
{
	function __construct($dbobject){
		parent::__construct($dbobject, false);
	}
	public function doBuildRelation($name){
		$relation = $this->_relations[$name];
		$sort_by = $relation->sort_by;
		$foreign_table = $relation->foreign_table;
		$foreign_class = $relation->foreign_class;
		$foreign_key = $relation->foreign_key;
		$foreign_key_value = '';
		$foreign_key_value = $this->_dbobject->$foreign_key;
		if ($foreign_key_value == null)
		{
			return null;
		}
		$obj = new $foreign_class();
		$sql = "SELECT a.* FROM ".$foreign_table." a ";
		$pk_string = $this->_dbobject->pk;
		$pk_foreign = $obj->pk;
		$sql .=" WHERE a.".$pk_foreign."='".$foreign_key_value."' ";
		$db_results = DB::query($sql);
		$this->_cached_values[$name] = null;
		if (count($db_results) == 1)
		{
			$obj->buildFromDB(reset($db_results));
			$this->_cached_values[$name] = $obj;
		}
	}

	protected function doSetRelationIDs($relation_name, $id){
		if (!$this->hasRelation($relation_name)){ throw new Exception("No relation named ".$relation_name); }
		if (is_array($id)){ throw new Exception($relation_name." cannot be an array"); }
		$relation = $this->_relations[$relation_name];
		unset($this->_cached_values[$relation_name]);
		$foreign_class = $relation->foreign_class;
		
		if ($id == null)
		{
			$this->_cached_values[$relation_name] = null;
		}
		else
		{
			$obj = $foreign_class::Get($id);
			$this->_cached_values[$relation_name] = $obj;
		}
	}
	public function doSetRelation($relation_name, $value){
		if (like_array($value)){ throw new Exception($relation_name." can't be set to an array"); }
		$this->_cached_values[$relation_name] = $value;
	}
	
	public function saveRelation($relation_name, $new_ids, $debug = false){
		if (!$this->hasRelation($relation_name)){ throw new Exception("No relation named ".$relation_name); }
		$relation = $this->_relations[$relation_name];
		$new_id = $new_ids[0];
		$foreign_table = $relation->foreign_table;
		$foreign_class = $relation->foreign_class;
		$foreign_key = $relation->foreign_key;
		$tablename = $this->_dbobject->tablename;
		$pk_col = $this->_dbobject->pk;
		$pk_val = $this->_dbobject->$pk_col;
		
		/*$sql = "SELECT t.".$foreign_key." FROM ".$this->_dbobject->tablename." t where t.".$pk_col." = ".$pk_val;
		$db_results = DB::query($sql);
		$old_id = $db_results[0][$foreign_key];*/

		if ($new_id == null){ $new_id = "NULL"; }
		
		// update association
		$sql = "UPDATE ".$tablename." ";
		$sql .= "SET ".$foreign_key."=".$new_id." ";
		$sql .= "WHERE ".$pk_col."=".$pk_val." ";
		$sql .= "LIMIT 1";
		if ($debug){ print($sql); return false; }
		DB::exec($sql);
		$this->buildRelation($relation_name);
		return true;
	}
	public function destroy($destroy_dependents){
		foreach($this->_relations as $relation){
			$tablename = $this->_dbobject->tablename;
			$pk = $this->_dbobject->pk;
			$pk_value = $this->_dbobject->pkval;
			$foreign_key = $relation->foreign_key;

			$sql = "UPDATE $tablename SET $foreign_key = null where $pk = ".addslashes($pk_value)." LIMIT 1";
			$db_results = DB::exec($sql);
		}
	}
}

?>

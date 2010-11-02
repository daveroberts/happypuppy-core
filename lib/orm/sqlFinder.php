<?php

namespace HappyPuppy;
class sqlFinder
{
	private $_dbo;
	function __construct($dbobject)
	{
		$this->_dbo = $dbobject;
	}
	public function find($args, $debug = false)
	{
		if ($args == null){ return $this->find_all(array()); }
		if (is_int($args)){ return $this->find_by_id($args); }
		if (is_array($args))
		{
			$all_ints = true;
			foreach($args as $k=>$v)
			{
				if (!is_int($k) || !is_int($v)){ $all_ints = false; break; }
			}
			if ($all_ints){ return $this->find_by_ids($args); }
			return $this->find_all($args, $debug);
		}
		return null;
	}
	public function findBy($name, $val)
	{
		return $this->find_all(array("conditions"=>"$name = '".addslashes($val)."'"));
	}
	private function find_by_id($id)
	{
		$sql = "SELECT * FROM ".$this->_dbo->tablename." WHERE ".$this->_dbo->pk."=".addslashes($id);
		$db_results = DB::query($sql);
		if (count($db_results) == 0){ return null; }
		$this->_dbo->buildFromDB(reset($db_results));
		return true;
	}
	private function find_by_ids($ids)
	{
		$sql = "SELECT * FROM ".$this->_dbo->tablename." WHERE ".$this->_dbo->pk." IN (";
		foreach($ids as $id)
		{
			$sql .= $id.", ";
		}
		$sql = rtrim($sql, ", ");
		$sql .= ")";
		$db_results = DB::query($sql);
		if (count($db_results) == 0){ return null; }
		$this->_dbo->buildFromDB(reset($db_results));
		return $this->_dbo;
	}
	private function find_all($args, $debug = false)
	{
		$conditions = $args["conditions"];
		$count = $args["count"];
		$order = $args["order"];
		$limit = $args["limit"];
		$offset = $args["offset"];
		$includes = $args["includes"];
		$sql = "SELECT ";
		if ($count != null){ $sql .= "COUNT(*) ";} else { $sql .= "* "; }
		$sql .= " FROM ".$this->_dbo->tablename." ";
		if ($conditions != null)
		{
			$sql .= " WHERE ".$conditions." ";
		}
		if ($order != null)
		{
			$sql .= " ORDER BY ".$order." ";
		}
		if ($limit != null)
		{
			$sql .= " LIMIT ".$limit." ";
		}
		if ($offset != null)
		{
			$sql .= " OFFSET ".$offset." ";
		}
		if ($debug){ return $sql; }
		$db_results = DB::query($sql);
		if ($count)
		{
			return reset(reset($db_results));
		}
		$obj_array = $this->_dbo->buildAll($db_results);
		$pk_col = $this->_dbo->pk;
		if ($includes != null)
		{
			$this_table = $this->_dbo->tablename;
			foreach($includes as $include)
			{
				$relation = $this->_dbo->getRelationType($include);
				$relation_name = $relation->name;
				$sort_by = $relation->sort_by;
				$foreign_class = $relation->foreign_class;
				$foreign_table = $relation->foreign_table;
				$foreign_obj = new $foreign_class;
				if ($relation instanceof hasOneRelation)
				{
					$foreign_key = $relation->foreign_key;
					$foreign_pk_col = $foreign_obj->pk;
					$sql = "SELECT t.".$pk_col." AS THIS_PK, f.* FROM ".$foreign_table." f";
					$sql .= " LEFT JOIN ".$this_table." t ON f.".$foreign_pk_col."=t.".$foreign_key." ";
					$sql .= " WHERE f.".$foreign_key." IS NOT NULL ";
					if ($sort_by) { $sql .= " ORDER BY ".$sort_by." "; }
					$db_results = DB::query($sql);
					// Can't use build_all because we want to use the THIS_PK information
					// which would be lost if we didn't use it here
					foreach($db_results as $db_row)
					{
						$foreign_obj = new $foreign_class;
						$foreign_obj->buildFromDB($db_row);
						$this_obj = &$obj_array[$db_row["THIS_PK"]];
						$this_obj->setRelation($relation_name, $foreign_obj);
					}
				}
				else if ($relation instanceof hasManyRelation)
				{
					$foreign_key = $relation->foreign_key;
					$sql = "SELECT t.".$pk_col." AS THIS_PK, f.* FROM ".$foreign_table." f";
					$sql .= " LEFT JOIN ".$this_table." t ON f.".$foreign_key."=t.".$pk_col." ";
					$sql .= " WHERE f.".$foreign_key." IS NOT NULL ";
					if ($sort_by) { $sql .= " ORDER BY ".$sort_by." "; }
					$db_results = DB::query($sql);
					foreach($db_results as $db_row)
					{
						$foreign_obj = new $foreign_class;
						$foreign_obj->buildFromDB($db_row);
						$foreign_pk_col = $foreign_obj->pk;
						$foreign_pk_val = $foreign_obj->$foreign_pk_col;
						$this_obj = &$obj_array[$db_row["THIS_PK"]];
						$this_obj->addIntoRelation($relation_name, $foreign_pk_val, $foreign_obj, true);
					}
				}
				else if ($relation instanceof habtmRelation)
				{
					$foreign_pk_col = $foreign_obj->pk;
					$link_table = $relation->link_table;
					$link_table_fk_here = $relation->link_table_fk_here;
					$link_table_fk_foreigntable = $relation->link_table_fk_foreigntable;
					$foreign_table_pk = $relation->foreign_table_pk;
					$sql = "SELECT t.".$pk_col." AS THIS_PK, f.* FROM ".$foreign_table." f ";
					$sql .= " LEFT JOIN ".$link_table." l ON f.".$foreign_pk_col."=l.".$link_table_fk_foreigntable." ";
					$sql .= " LEFT JOIN ".$this_table." t ON l.".$link_table_fk_here."=t.".$pk_col." ";
					if ($sort_by) { $sql .= " ORDER BY ".$sort_by." "; }
					$db_results = DB::query($sql);
					foreach($db_results as $db_row)
					{
						$foreign_obj = new $foreign_class;
						$foreign_obj->buildFromDB($db_row);
						$foreign_pk_col = $foreign_obj->pk;
						$foreign_pk_val = $foreign_obj->$foreign_pk_col;
						$this_obj = &$obj_array[$db_row["THIS_PK"]];
						$this_obj->addIntoRelation($relation_name, $foreign_pk_val, $foreign_obj, true);
					}
				}
			}
		}
		return $obj_array;
	}
}

?>

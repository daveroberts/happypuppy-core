<?php
namespace HappyPuppy;
class UniqueFieldValidator
{
	private $_field_name;
	private $_scope_by;
	private $_error_message = 'has already been taken';

	function __construct($field_name, $scope_by = array(), $error_msg = ''){
		$this->_field_name = $field_name;
		$this->_scope_by = $scope_by;
		if ($error_msg != ''){ $this->_error_message = $error_msg; }
	}
	public function isUniqueInsert($dbobject, &$error_msg){
		$obj_arr = $this->getObjArray($dbobject);
		if (count($obj_arr) > 0)
		{
			$fn = $this->_field_name;
			$error_msg = $this->_field_name."(".$dbobject->$fn.") ".$this->_error_message;
			return false;
		}
		return true;
	}
	public function isUniqueUpdate($dbobject, &$error_msg){
		$obj_arr = $this->getObjArray($dbobject);
		if (count($obj_arr) == 0){ return true; }
		if (count($obj_arr) == 1){
			$obj = reset($obj_arr);
			$pk = $obj->pk;
			if ($obj->$pk == $dbobject->$pk){
				return true;
			}
		}
		$fn = $this->_field_name;
		$error_msg = $this->_field_name."(".$dbobject->$fn.") ".$this->_error_message;
		return false;
	}
	private function getObjArray($dbobject){
		$fn = $this->_field_name;
		$conditions = "$fn='".addslashes($dbobject->$fn)."'";
		foreach($this->_scope_by as $field){
			$fn = $field;
			$conditions .= " AND $fn='".addslashes($dbobject->$fn)."'";
		}
		return $dbobject->pFind(array("conditions"=>$conditions));
	}
}
?>

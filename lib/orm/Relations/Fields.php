<?

namespace HappyPuppy;
class Fields
{
	private $_dbobject;
	private $_pk;
	private $_db_field_values = array();  // just use getters and setters
	private $_unique_fields = array();
	private $_cached_field_values = array();

	function __construct($dbobject){
		$this->_dbobject = $dbobject;
	}
	public function addUniqueField($field_name, $scope_by = array(), &$error_msg = ''){
		$this->_unique_fields[] = new UniqueFieldValidator($field_name, $scope_by, &$error_msg);
	}
	public function getPK(){
		if (!isset($this->_pk))
		{
			$field_structure = DB::get_field_structure($this->_dbobject->tablename);
			$this->_pk = $field_structure["pk"];
		}
		return $this->_pk;
	}
	public function hasField($field_name){
		$field_structure = DB::get_field_structure($this->_dbobject->tablename);
		return array_key_exists($field_name, $field_structure["fields"]);
	}
	public function getField($name){
		if ($this->hasField($name))
		{
			return $this->_cached_field_values[$name];
		}
		else
		{
			throw new Exception($name." is not a valid field");
		}
	}
	public function setFieldFromDB($name, $value){
		$this->_db_field_values[$name] = $value;
	}
	public function setFieldFromForm($name, $value){
		$this->_cached_field_values[$name] = $value;
	}
	private function fieldNames(){
		$field_structure = DB::get_field_structure($this->_dbobject->tablename);
		return array_keys($field_structure["fields"]);
	}
	private function isDateField($name){
		$field_structure = DB::get_field_structure($this->_dbobject->tablename);
		$type = $field_structure["fields"][$name];
		return $type == 'date';
	}

	public function buildFromDB($arr){
		foreach($this->fieldNames() as $field)
		{
			$this->setFieldFromDB($field, $arr[$field]);
		}
		$this->_cached_field_values = $this->_db_field_values;
	}
	public function buildFromForm($arr){
		foreach($this->fieldNames() as $field)
		{
			if (isset($arr[$field]))
			{
				$this->setFieldFromForm($field, $arr[$field]);
			}
		}
	}

	public function save(&$error_msg, $debug = false){
		if ($this->getField($this->getPK()) == null)
		{
			$before_insert = $this->beforeInsert($error_msg);
			if (!$before_insert){ return false; }
			return $this->insert($debug);
		}
		else
		{
			$before_update = $this->beforeUpdate($error_msg);
			if (!$before_update){ return false; }
			return $this->update($debug);
		}
	}
	private function beforeInsert(&$error_msg){
		if (method_exists($this, "before_insert")){
			$before_insert_result = $this->before_insert($error_msg);
			if (!$before_insert_result){ return false; }
		}
		foreach($this->_unique_fields as $uniqueFieldValidator){
			$before_insert_result = $uniqueFieldValidator->isUniqueInsert($this->_dbobject, $error_msg);
			if (!$before_insert_result){ return false; }
		}
		return true;
	}
	private function insert($debug = false){
		$sql = "INSERT INTO ".$this->_dbobject->tablename." (";
		foreach($this->fieldNames() as $field)
		{
			if ($field == $this->getPK()){ continue; }
			if (array_key_exists($field, $this->_cached_field_values))
			{
				$sql .= $field.", ";
			}
		}
		$sql = rtrim($sql, ", ");
		$sql .= ") VALUES (";
		foreach($this->fieldNames() as $field)
		{
			if ($field == $this->getPK()){ continue; }
			if (array_key_exists($field, $this->_cached_field_values))
			{
				if ($this->isDateField($field)){
					$date = Fields::formatDate($this->getField($field));
					if (!$date){ $date = "NULL"; }
					$sql .= "'".addslashes($date)."', ";
				} else {
					$sql .= "'".addslashes($this->getField($field))."', ";
				}
			}
		}
		$sql = rtrim($sql, ", ");
		$sql .= ")";
		if ($debug){ print $sql; return false; }
		$result = DB::exec($sql);
		if ($result)
		{
			$id = DB::lastInsertId();
			$this->setFieldFromDB($this->getPK(), $id);
			$this->setFieldFromForm($this->getPK(), $id);
		}
		return $result;
	}
	private function beforeUpdate(){
		if (method_exists($this, "before_update")){
			$before_update_result = $this->before_update(&$error_msg);
			if (!$before_update_result){ return false; }
		}
		foreach($this->_unique_fields as $uniqueFieldValidator){
			$before_update_result = $uniqueFieldValidator->isUniqueUpdate($this->dbobject, $error_msg);
			if (!$before_update_result){ return false; }
		}
		return true;
	}
	private function update($debug = false){
		$sql = "UPDATE ".$this->_dbobject->tablename." SET ";
		foreach($this->fieldNames() as $field)
		{
			if ($field == $this->getPK()){ continue; }
			if (array_key_exists($field, $this->_cached_field_values) &&
				$this->_cached_field_values[$field] != $this->_db_field_values[$field])
			{
				if ($this->isDateField($field)){
					$date = Fields::formatDate($this->getField($field));
					if (!$date){ $date = "NULL"; }
					$sql .= $field."='".addslashes($date)."', ";
				} else {
					$sql .= $field."='".addslashes($this->getField($field))."', ";
				}
				
			}
		}
		$sql = rtrim($sql, ", ");
		$sql .= " WHERE ".$this->getPK()."='".$this->getField($this->getpk())."'";
		if ($debug){ print($sql); return false; }
		$result = DB::exec($sql);
		return $result;
	}

	public function delete(){
		$sql = "DELETE FROM ".$this->_dbobject->tablename." WHERE ".$this->getPK()."='".addslashes($this->getField($this->getPK()))."' LIMIT 1";
		return DB::exec($sql);
	}
	public function destroy(){
		return $this->delete();
	}
	public function prettyPrint(){
		$out = '';
		foreach($this->fieldNames() as $field)
		{
			$out .= $field.": ".$this->getField($field)."\n";
		}
		return $out;
	}

	public static function formatDate($date){
		$month; $day; $year;
		//match the format of the date
		if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
			// YYYY-MM-DD
			$year = $parts[1];
			$month = $parts[2];
			$day = $parts[3];
		} else if (preg_match ("/^([0-9][0-9]?)\/([0-9][0-9]?)\/([0-9]{2}[0-9]?[0-9]?)$/", $date, $parts)) {
			// MM/DD/YYYY or M/D/YY or any inbetween format
			$month = $parts[1];
			$day = $parts[2];
			$year = $parts[3];
		} else {
			return false;
		}
		//check weather the date is valid of not
		if(!checkdate($month,$day,$year)) { return false; }
		return $year.'-'.$month.'-'.$day;
	}
}

?>

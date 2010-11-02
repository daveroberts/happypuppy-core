<?
namespace HappyPuppy;
require("Relations/Fields.php");
require("UniqueFieldValidator.php");
require("Relations/Relations.php");
require("dbobjectCollection.php");
require("sqlFinder.php");
require('IdentityMap.php');

//TODO separate cached fields versus uncached fields
//TODO dbobject groups as arrays
//TODO changing relationships
//TODO saving relationships
//TODO static find methods (requires php5.3)
//TODO better select box
//TODO check the form inputs for habtm again
//TODO form populating (reloading) on invalid data
//TODO form validation
//TODO If you have a person, how to create a bank account under that person
//FIXME return empty array if none in a many association, not null
abstract class dbobject
{
	private $_tablename; // use setter to set.  Get with $obj->tablename (underscore to prevent DB field clash)
	private $_description; // use setter to set.  Get with $obj->__description (underscore to prevent DB field clash)
	private $_fields; // cache of values for fields
	private $_relations;
	private $_sqlFinder;
	
	function __construct($tablename = '')
	{
		if ($tablename != ''){ $this->setTablename($tablename); }
		$this->_fields = new Fields($this);
		$this->_relations = new Relations($this);
		$this->_sqlFinder = new sqlFinder($this);
	}
	
	// Field Related
	public function isUniqueField($field_name, $scope_by = array(), &$error_msg = ''){
		$this->_fields->addUniqueField($field_name, $scope_by, &$error_msg);
	}
	public function setDescription($value){
		$this->_description = $value;
	}
	public function setTablename($value){
		$this->_tablename = $value;
	}
	public function hasField($field_name){
		return $this->_fields->hasField($field_name);
	}

	// Relation Related
	
	// Add Relations
	protected function has_many($relation_name, $sort_by='', $foreign_class = '', $foreign_table = '', $foreign_key = ''){
		$this->_relations->addHasMany($relation_name, $sort_by, $foreign_class, $foreign_table, $foreign_key);
	}
	protected function has_one($relation_name, $foreign_class = '', $foreign_table = '', $foreign_key = ''){
		$this->_relations->addHasOne($relation_name, $foreign_class, $foreign_table, $foreign_key);
	}
	protected function habtm($relation_name, $sort_by='', $foreign_class = '', $foreign_table = '', $foreign_table_pk = '', $link_table = '', $link_table_fk_here = '', $link_table_fk_foreigntable = ''){
		$this->_relations->addHabtm($relation_name, $sort_by, $foreign_class, $foreign_table, $foreign_table_pk, $link_table, $link_table_fk_here, $link_table_fk_foreigntable);
	}
	public function hasRelation($relation_name){
		return $this->_relations->hasRelation($relation_name);
	}
	public function getRelationType($relation_name){
		return $this->_relations->getRelationType($relation_name);
	}
	public function setRelation($relation_name, $new_value){
		return $this->_relations->setRelation($relation_name, $new_value);
	}
	public function setRelationIDs($relation_name, $ids){
		return $this->_relations->setRelationIDs($relation_name, $ids);
	}
	public function addIntoRelation($relation_name, $key, $value, $fromDB = false){
		return $this->_relations->addIntoRelation($relation_name, $key, $value, $fromDB);
	}
	
	public function __get($name){
		if ($name == "tablename")
		{
			if ($this->_tablename == ''){
				$classname = get_called_class();
				$classname = substr($classname, strrpos($classname, "\\") + 1);
				if ($_ENV["config"]["plural_db_tables"] == 1) {
					$this->_tablename = Inflector::plural($classname);
				} else {
					$this->_tablename = $classname;
				}
			}
			return $this->_tablename;
		}
		else if ($name == "__description")
		{
			if ($this->_description == ''){
				if ($this->_fields->hasField("name")){ $this->_description = "name"; }
				else if ($this->_fields->hasField("description")){ $this->_description = "description"; }
			}
			return $this->_description;
		}
		else if ($name == "pk")
		{
			return $this->_fields->getPK();
		}
		else if ($name == "pkval")
		{
			return $this->_fields->getField($this->_fields->getPK());
		}
		else if ($this->_fields->hasField($name))
		{
			return $this->_fields->getField($name);
		}
		else if ($this->_relations->hasRelation($name))
		{
			return $this->_relations->getRelationValues($name);
		}
		return null;
	}
	public function __set($name, $value){
		if ($this->_fields->hasField($name))
		{
			$this->_fields->setFieldFromForm($name, $value);
			return;
		}
		if ($this->hasRelation($name))
		{
			$this->_relations->setRelation($name, $value);
			return;
		}
		throw new \Exception($name." is not a field or relation");
	}
	public static function __callStatic($name, $args){
		if (substr($name, 0, 6) == "FindBy")
		{
			$name = substr($name, 6);
			return self::FindBy($name, $args[0]);
		}
		throw new \Exception($name." is not a valid method");
	}
	public function buildAll($db_results){
		$obj_array = array();
		$klass = get_class($this);
		$pk_col = $this->pk;
		foreach($db_results as $db_row)
		{
			$obj = new $klass();
			$obj->buildFromDB($db_row);
			$obj_array[$obj->$pk_col] = $obj;
		}
		return $obj_array;
	}
	public function buildFromDB($arr){
		$this->_fields->buildFromDB($arr);
		$pk_id = $arr[$this->pk];
		IdentityMap::set($this->tablename,$pk_id,$this);
	}
	public function build($arr){
		if ($arr == null) { throw new \Exception("Array passed to build is null"); }
		$this->_fields->buildFromForm($arr);
		$this->_relations->buildFromForm($arr);
	}
	public static function FindBySQL($sql){
		$db_results = DB::query($sql);
		$klass = get_called_class();
		$obj = new $klass();
		return $obj->buildAll($db_results);
	}
	public static function Count($args, $debug = false){
		$classname = get_called_class();
		$dbobject = new $classname();
		return $dbobject->pCount($args, $debug);
	}
	public function pCount($args, $debug = false){
		$args["count"] = true;
		return $this->_sqlFinder->find($args, $debug);
	}
	public static function Find($args, $debug = false){
		$classname = get_called_class();
		$dbobject = new $classname();
		return $dbobject->pFind($args, $debug);
	}
	public function pFind($args, $debug = false){
		return $this->_sqlFinder->find($args, $debug);
	}
	public static function FindBy($name, $val){
		$classname = get_called_class();
		$dbobject = new $classname();
		return $dbobject->pFindBy($name, $val);
	}
	public function pFindBy($name, $val){
		return $this->_sqlFinder->findBy($name, $val);
	}
	public function loadRelation($sql, $relation_name){
		$db_results = DB::query($sql);
		$relation = $this->_relations->getRelationType($relation_name);
		foreach($db_results as $db_row)
		{
			$foreign_klass = $relation->foreign_class;
			$foreign_obj = new $foreign_klass();
			$foreign_obj->buildFromDB($db_row);
			$klass = get_class($this);
			$obj = new $klass();
			$obj = $obj->get($db_row["__id"]);
			$obj->addIntoRelation($relation_name, $foreign_obj->pkval, $foreign_obj);
			$x = 0;
		}
	}
	public function save(&$error_msg = '', $debug = false){
		if (method_exists($this, "before_save"))
		{
			$before_save_result = $this->before_save($error_msg);
			if (!$before_save_result){ return false; }
		}
		$result = $this->_fields->save($error_msg, $debug);
		if (!$result){ return false; }
		$result = $this->_relations->save($error_msg, $debug);
		if (!$result){ return false; }
		return true;
	}
	public function delete(){
		$result = $this->_fields->delete();
		if (!result){ return false; }
		return true;
	}
	public function destroy($destroy_dependents = false){
		// deletes a record and all of its has_many orphans
		$result = $this->_relations->destroy($destroy_dependents);
		if (!result){ return false; }
		$result = $this->_fields->destroy();
		if (!result){ return false; }
		return true;
	}
	public function prettyPrint(){
		$out = get_class($this)." object\n";
		$out .= $this->_fields->prettyPrint();
		$out .= $this->_relations->prettyPrint();
		$out .= "---\n";
		return $out;
	}
	
	public static function Get($pk_id){
		$classname = get_called_class();
		$dbobject = new $classname();
		// check identity map first
		if (IdentityMap::is_set($dbobject->tablename, $pk_id))
		{
			return IdentityMap::get($dbobject->tablename, $pk_id);
		}
		else
		{
			$sql = "SELECT * FROM ".$dbobject->tablename." t WHERE t.".$dbobject->pk."=".addslashes($pk_id);
			$db_results = DB::query($sql);
			if (count($db_results) == 0){ return null; }
			$dbobject->buildFromDB(reset($db_results));
			return $dbobject;
		}
	}
	public function First(){
		$classname = get_called_class();
		$dbobject = new $classname();
		$sql = "SELECT TOP 1 * FROM ".$dbobject->tablename;
		$db_results = DB::query($sql);
		if (count($db_results) == 0){ return null; }
		$dbobject->buildFromDB(reset($db_results));
		return $dbobject;
	}
	public static function All($sort_by = ''){
		$classname = get_called_class();
		$dbobject = new $classname();
		$sql = "SELECT * FROM ".$dbobject->tablename.' ';
		if ($sort_by != "")
		{
			$sql .= " ORDER BY ".$sort_by." ";
		}
		$db_results = DB::query($sql);
		$obj_array = array();
		foreach($db_results as $db_row)
		{
			$klass = get_class($dbobject);
			$obj = new $klass();
			$obj->buildFromDB($db_row);
			$obj_array[] = $obj;
		}
		return $obj_array;
	}
}
?>

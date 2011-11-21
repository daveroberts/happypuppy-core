<?php
namespace HappyPuppy;
require("Relations/Fields.php");
require("UniqueFieldValidator.php");
require("Relations/Relations.php");
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
abstract class Model
{
	private $_tablename; // use setter to set.  Get with $obj->tablename (underscore to prevent DB field clash)
	private $_description; // use setter to set.  Get with $obj->__description (underscore to prevent DB field clash)
	private $_fields; // cache of values for fields
	private $_relations;
	private $_sqlFinder;
	
	public static $all_rows_loaded;
	
	function __construct($tablename = '')
	{
		if ($tablename != ''){ $this->setTablename($tablename); }
		$this->_fields = new Fields($this);
		$this->_relations = new Relations($this);
		$this->_sqlFinder = new sqlFinder($this);
		if(!is_array(self::$all_rows_loaded))
		{
			self::$all_rows_loaded = array();
		}
	}
	
	// Field Related
	public function isUniqueField($field_name, $scope_by = array(), &$error_msg = ''){
		$this->_fields->addUniqueField($field_name, $scope_by, $error_msg);
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
	protected function belongs_to($relation_name, $foreign_class = '', $foreign_table = '', $foreign_key = ''){
		$this->_relations->addBelongsTo($relation_name, $foreign_class, $foreign_table, $foreign_key);
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
				$this->_tablename = strtolower($this->_tablename);
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
			$arr = array();
			return $this->_relations->getRelationValues($name, $arr);
		}
		else
		{
			$refl = new \ReflectionClass($this);
			throw new \Exception('"'.$name."\" isn't a valid model or relationship on ".$refl->getShortName());
		}
	}
	// used by forms
	public function getValue($name, &$debug = array())
	{
		if ($this->_fields->hasField($name))
		{
			return $this->_fields->getField($name, $debug);
		}
		else if ($this->_relations->hasRelation($name))
		{
			$arr = array();
			return $this->_relations->getRelationValues($name, $debug);
		}
	}
	public function getRelationValues($name, &$debug = array()){
		return $this->_relations->getRelationValues($name, $debug);
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
	public function setRelationValues($name, $value, &$debug = array(), $stop_before_alter = false){
		if ($this->hasRelation($name))
		{
			$this->_relations->setRelation($name, $value, $debug, $stop_before_alter);
			return;
		}
	}
	public static function __callStatic($name, $args){
		if (substr($name, 0, 6) == "FindBy")
		{
			$name = substr($name, 6);
			if (count($args) == 1){ $args[1] = false; }
			return self::FindBy($name, $args[0], $args[1]);
		}
		if (substr($name, 0, 5) == "GetBy")
		{
			
			$name = substr($name, 5);
			if (count($args) == 1){ $args[1] = false; }
			return self::GetBy($name, $args[0], $args[1]);
		}
		throw new \Exception($name." is not a valid method");
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
	public static function FindBySQL($sql, $params){
		$num_args = func_num_args();
		$args = array();
		if ($num_args > 1){
			$args = func_get_args();
			array_shift($args); // chop off sql
		}
		foreach($args as $arg){
			$sql = Model::replaceNextArg($sql, $arg);
		}
		$db_results = DB::query($sql);
		$klass = get_called_class();
		$obj = new $klass();
		return $obj->buildAll($db_results);
	}
	public static function Count($args, &$debug = array()){
		$classname = get_called_class();
		$model = new $classname();
		return $model->pCount($args, $debug);
	}
	public function pCount($args, $debug, &$debug_log){
		$args["count"] = true;
		return $this->_sqlFinder->find($args, $debug);
	}
	public static function WhereDebug(&$debug, $conditions, $params = null)
	{
		$classname = get_called_class();
		return forward_static_call_array(array($classname, 'PWhere'), func_get_args());
	}
	public static function Where($conditions, $params = null)
	{
		$classname = get_called_class();
		$args = func_get_args();
		return forward_static_call_array(array($classname, 'PWhere'), $args);
	}
	// removed debug pass by reference
	// forward_static_call_array was not allowing pass by reference
	private static function PWhere($conditions, $params)
	{
		$classname = get_called_class();
		$num_args = func_num_args();
		$args = array();
		if ($num_args == 2)
		{
			$args[] = $params;
		}
		else if ($num_args == 3){
			if (is_string($params) || is_int($params)){
				$args[] = $params;
			} else if (is_array($params)){
				$args = $params;
			} else {
				throw new \Exception("Argument passed to Where must be either a string or an array");
			}
		} else if ($num_args > 2) {
			$args = func_get_args();
			array_shift($args); // chop off debug
			array_shift($args); // chop off conditions
		}
		foreach($args as $arg){
			$conditions = Model::replaceNextArg($conditions, $arg);
		}
		return $classname::Find(array("conditions"=>$conditions), $debug);
	}
	private static function replaceNextArg($conditions, $var){
		$pos = strpos($conditions, '?');
		$conditions = substr($conditions,0,$pos).addslashes($var).substr($conditions,$pos + 1);
		return $conditions;
	}
	public static function Find($args, &$debug = array()){
		$classname = get_called_class();
		$model = new $classname();
		return $model->pFind($args, $debug);
	}
	public function pFind($args, &$debug){
		return $this->_sqlFinder->find($args, $debug);
	}
	public static function FindBy($name, $val, &$debug = array()){
		$classname = get_called_class();
		$model = new $classname();
		return $model->pFindBy($name, $val, $debug);
	}
	public function pFindBy($name, $val, &$debug){
		return $this->_sqlFinder->findBy($name, $val, $debug);
	}
	public static function GetBy($name, $val, &$debug = array()){
		$classname = get_called_class();
		$model = new $classname();
		$results = $model->pFindBy($name, $val, $debug);
		if (count($results) == 1){ return end($results); }
		return null;
	}
	public static function LoadRelation($sql, $relation_name){
		$db_results = DB::query($sql);
		$classname = get_called_class();
		$model = new $classname();
		$relation = $model->_relations->getRelationType($relation_name);
		foreach($db_results as $db_row)
		{
			$foreign_klass = $relation->foreign_class;
			$foreign_obj = new $foreign_klass();
			$foreign_obj->buildFromDB($db_row);
			$obj = new $classname();
			$obj = $obj->get($db_row["__id"]);
			$obj->addIntoRelation($relation_name, $foreign_obj->pkval, $foreign_obj);
		}
	}
	public function save(&$error_msg = '', &$debug = array(), $stop_before_alter = false){
		if (method_exists($this, "beforeSave"))
		{
			$before_save_result = $this->beforeSave($error_msg);
			if (!$before_save_result){ return false; }
		}
		$result = $this->_fields->save($error_msg, $debug, $stop_before_alter);
		if (!$result){ return false; }
		$result = $this->_relations->save($debug, $stop_before_alter);
		if (!$result){ return false; }
		return true;
	}
	public function delete(&$debug = array(), $stop_before_alter = false){
		$result = $this->_fields->delete($debug, $stop_before_alter);
		if (!result){ return false; }
		return true;
	}
	public function destroy($destroy_dependents = false, &$debug = array(), $stop_before_alter = false){
		// deletes a record and all of its has_many orphans
		$result = $this->_relations->destroy($destroy_dependents, $debug, $stop_before_alter);
		if (!$result && $debug == false){ return false; }
		$result = $this->_fields->destroy($debug, $stop_before_alter);
		if (!$result){ return false; }
		return true;
	}
	public function prettyPrint(){
		$out = get_class($this)." object\n";
		$out .= $this->_fields->prettyPrint();
		$out .= $this->_relations->prettyPrint();
		$out .= "---\n";
		return $out;
	}
	
	public static function Get($pk_id, &$debug = array()){
		$classname = get_called_class();
		$model = new $classname();
		// check identity map first
		if (IdentityMap::is_set($model->tablename, $pk_id) && $debug == false)
		{
			return IdentityMap::get($model->tablename, $pk_id);
		}
		else
		{
			$sql = "SELECT * FROM ".$model->tablename." t WHERE t.".$model->pk."=".addslashes($pk_id);
			$debug[] = $sql;
			$db_results = DB::query($sql);
			if (count($db_results) == 0){ return null; }
			$model->buildFromDB(reset($db_results));
			return $model;
		}
	}
	public static function First(&$debug = array()){
		$classname = get_called_class();
		$model = new $classname();
		$sql = "SELECT TOP 1 * FROM ".$model->tablename;
		$debug[] = $sql;
		$db_results = DB::query($sql);
		if (count($db_results) == 0){ return null; }
		$model->buildFromDB(reset($db_results));
		return $model;
	}
	public static function All($sort_by = '', &$debug = array()){
		$classname = get_called_class();
		$model = new $classname();
		
		if (self::$all_rows_loaded[$model->tablename] && $debug == false)
		{
			return IdentityMap::GetAll($model->tablename);
		}
		
		$sql = "SELECT * FROM ".$model->tablename.' ';
		if ($sort_by != ""){
			$sql .= " ORDER BY `".$sort_by."` ";
		} else {
			if ($model->hasField("name")){
				$sql .= " ORDER BY `name` ";
			}
		}
		$debug[] = $sql;
		$db_results = DB::query($sql);
		$obj_array = array();
		foreach($db_results as $db_row)
		{
			$klass = get_class($model);
			$obj = new $klass();
			$obj->buildFromDB($db_row);
			$obj_array[] = $obj;
		}
		self::$all_rows_loaded[$model->tablename] = true;
		return $obj_array;
	}

	public static function collectionToXML($collection, $includes = array(), $excludes = array(), $name = '')
	{
		$doc = new \DOMDocument('1.0');
		$doc->formatOutput = true;
		
		if ($name == '')
		{
			$classname = get_called_class();
			$classname = substr($classname, strrpos($classname, "\\") + 1);
			$pluralname = strtolower(Inflector::plural($classname));
			$name = $pluralname;
		}

		$root = $doc->createElement($name);
		$root = $doc->appendChild($root);
		
		foreach($collection as $model)
		{
			$refl = new \ReflectionClass(get_class($model));
			$modelname = strtolower($refl->getShortName());
			$el = $doc->createElement($modelname);
			$model->addXMLAttributes($doc, $el, $includes, $excludes);
			$el = $root->appendChild($el);
		}
		return $doc->saveXML();
	}
	public function addXMLAttributes($doc, &$el, $includes = array(), $excludes = array())
	{
		$this->_fields->addXMLAttributes($doc, $el, $excludes);
		foreach($includes as $key=>$relation)
		{
			if (is_array($relation))
			{
				$this->addXMLRelation($doc, $el, $key, $relation);
			}
			else
			{
				$this->addXMLRelation($doc, $el, $relation);
			}
		}
	}
	public function addXMLRelation($doc, &$el, $relation_name, $includes = array())
	{
		if ($this->hasRelation($relation_name))
		{
			$relation = $this->getRelationType($relation_name);
			$relation_type = $relation->getType();
			if ($relation_type == 'hasOneRelation'){
				$rel = $this->$relation_name;
				if ($rel != null){
					$rel_el = $doc->createElement($relation_name);
					$rel->addXMLAttributes($doc, $rel_el, $includes);
					$rel_el = $el->appendChild($rel_el);
				}
			} else if ($relation_type == 'hasManyRelation' || $relation_type == 'habtmRelation') {
				$rel_col_el = $doc->createElement($relation_name);
				foreach($this->$relation_name as $model)
				{
					$refl = new \ReflectionClass(get_class($model));
					$modelname = strtolower($refl->getShortName());
					$rel_el = $doc->createElement($modelname);
					$model->addXMLAttributes($doc, $rel_el);
					$rel_el = $rel_col_el->appendChild($rel_el);
				}
				$rel_col_el = $el->appendChild($rel_col_el);
			} else {
				throw new \Exception("$relation_name isn't a valid relationship");
			}
		}
	}
	// used when building results for has many relationship
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
}
?>

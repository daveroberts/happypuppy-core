<?

namespace HappyPuppy;
abstract class RelationCollection
{
	protected $_dbobject;
	protected $_array_based;
	protected $_relations;
	protected $_cached_values;
	protected $_dirty_marks;
	function __construct($dbobject, $array_based){
		$this->_dbobject = $dbobject;
		$this->_array_based = $array_based;
		$this->_relations = array();
		$this->_cached_values = array();
		$this->_dirty_marks = array();
	}
	
	// load relation
	protected function buildRelation($relation_name)
	{
		$this->doBuildRelation($relation_name);
		$this->_dirty_marks[$relation_name] = false;
	}
	protected abstract function doBuildRelation($relation_name);
	
	// relationship add / has / get
	public function addRelation($relation){
		$this->_relations[$relation->name] = $relation;
	}
	public function hasRelation($relation_name){
		return array_key_exists($relation_name, $this->_relations);
	}
	public function getRelationType($relation_name){
		if ($this->hasRelation($relation_name))
		{
			return $this->_relations[$relation_name];
		}
		return null;
	}
	public function getRelationValues($relation_name){
		if (!array_key_exists($relation_name, $this->_cached_values))
		{
			$this->buildRelation($relation_name);
		}
		return $this->_cached_values[$relation_name];
	}
	
	// change relation
	public function addIntoRelation($relation_name, $key, $value){
		if ($this->_array_based)
		{
			$this->_cached_values[$relation_name][$key] = $value;
		}
		else
		{
			$this->_cached_values[$relation_name] = $value;
		}
	}
	public function setRelationIDs($relation_name, $ids){
		$this->_dirty_marks[$relation_name] = true;
		return $this->doSetRelationIDs($relation_name, $ids);
	}
	protected abstract function doSetRelationIDs($relation_name, $ids);
	public function setRelation($relation_name, $value){
		$this->_dirty_marks[$relation_name] = true;
		return doSetRelation($relation_name, $value);
	}
	protected abstract function doSetRelation($relation_name, $value);
	private function isDirty($relation_name){
		return $this->_dirty_marks[$relation_name] == true;
	}
	
	// save relations
	public function saveAllRelations($debug = false){
		foreach($this->_relations as $relation_name=>$relation)
		{
			$result = $this->save($relation_name, $debug);
			if (!$result){ return false; }
		}
		return true;
	}
	public function save($relation_name, $debug = false){
		if (!$this->hasRelation($relation_name)){ throw new Exception("No Relation named: ".$relation_name); }
		if (!$this->isDirty($relation_name)){ return true; }
		$new_ids = array();
		if ($this->_array_based)
		{
			foreach($this->_cached_values[$relation_name] as $k=>$v)
			{
				$new_ids[] = $k;
			}
		}
		else
		{
			$obj = $this->_cached_values[$relation_name];
			if ($obj != null)
			{
				$pk = $obj->pk;
				$new_ids[] = $obj->$pk;
			}
			else
			{
				$new_ids[] = null;
			}
		}
		$result = $this->saveRelation($relation_name, $new_ids, $debug);
		if ($result)
		{
			$this->_dirty_marks[$relation_name] = false;
		}
		return $result;
	}
	protected abstract function saveRelation($relation_name, $ids);
	public abstract function destroy($destroy_dependents);
	
	public function prettyPrint(){
		$out = '';
		foreach($this->_relations as $relation_name=>$relation)
		{
			$out .= $relation_name.': ';
			$this->getRelationValues($relation_name);
			$values = $this->_cached_values[$relation_name];
			if (is_array($values))
			{
				if (empty($values))
				{
					$out .= "none\n";
				}
				else
				{
					foreach($values as $id=>$val)
					{
						$out .= $id.", ";
					}
					$out = substr($out, 0, strlen($out) - 2);
					$out .= "\n";
				}
			}
			else
			{
				$out .= $values->pkval."\n";
			}
		}
		return $out;
	}
}

?>
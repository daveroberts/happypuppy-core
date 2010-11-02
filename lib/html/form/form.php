<?

namespace HappyPuppy;
class form
{
	private $model; // a dbobject

	function __construct($model){
		if ($model == null){ throw new Exception("The model passed to the form is null"); }
		$this->model = $model;
	}
	public function start($hp_action){
		$url;
		if (substr($hp_action,0, 1) == '/'){
			$url = \rawurl_from_appurl($hp_action);
		} else {
			$url = \rawurl_from_action($hp_action);
		}
		return "<form method='post' action='".$url."'>";
	}
	public function label($label, $field, $options = array()){
		$l = new HtmlLabel($this->inputFieldDefaultID($field), $label, $options);
		return $l->toString();
	}
	public function input($property, $options = array()){
		if ($this->model->hasField($property))
		{
			return $this->inputField($property, $options);
		}
		if ($this->model->hasRelation($property))
		{
			return $this->inputRelationship($property, $options);
		}
		throw new \Exception("$property is neither a field nor a relationship for ".get_class($this->model));
	}
	public function hidden($name, $value){
		$hid = new HtmlHidden($this->inputFieldDefaultID($name), $value);
		return $hid->toString();
	}
	public function submit($value, $html_options = array()){
		$s = new HtmlSubmit($value, $html_options);
		return $s->toString();
	}
	public function end(){
		return "</form>";
	}
	private function inputFieldDefaultID($name){
		$refl = new \ReflectionClass(get_class($this->model));
		$modelname = $refl->getShortName();
		if ($this->model->hasRelation($name)){
			return $modelname."[rel_ids_".$name."]";
		} else {
			return $modelname."[".$name."]";
		}
	}
	private function inputField($name, $htmlOptions){
		$field_structure = DB::get_field_structure($this->model->tablename);
		$type = $field_structure["fields"][$name];
		if (substr($type, 0, 7) == "varchar" ||
			substr($type, 0, 3) == "int" ||
			substr($type, 0, 5) == "float" ||
			substr($type, 0, 4) == "date")
		{
			$t = new HtmlTextbox($this->inputFieldDefaultID($name), '', $this->model->$name, $htmlOptions);
			return $t->toString();
		}
		else if (substr($type, 0, 7) == "tinyint")
		{
			$hid = new HtmlHidden($this->inputFieldDefaultID($name), "0");
			$cb = new HtmlCheckbox($this->inputFieldDefaultID($name), 1, $this->model->$name);
			return $hid->toString().$cb->toString();
		}
		else
		{
			throw new \Exception("$type isn't supported");
		}
		return $type;
	}
	private function inputRelationship($name, $options){
		if ($this->model->hasRelation($name))
		{
			$relation = $this->model->getRelationType($name);
			$relation_type = $relation->getType();
			if ($relation_type == 'hasManyRelation'){
				return $this->inputHasMany($name, $options);
			} else if ($relation_type == 'hasOneRelation') {
				return $this->inputHasOne($name, $options);
			} else if ($relation_type == 'habtmRelation') {
				return $this->inputHABTM($name, $options);
			} else {
				throw new \Exception("$name isn't a valid relationship");
			}
		}
	}
	private function inputHasMany($name, $options){
		$relation = $this->model->getRelation($name);
		$foreign_model = $relation->foreign_class;
		$foreign_class = new $foreign_model();
		$pk = $foreign_class->pk;
		$all = $foreign_class->getAll();
		$related_entities = $this->model->$name;
		$related_ids = array();
		foreach($related_entities as $entity)
		{
			$re_pk = $entity->pk;
			$related_ids[] = $entity->$re_pk;
		}
		$input_options = array();
		$description = $foreign_class->getDescription();
		foreach($all as $foreign_entity)
		{
			$input_options[$foreign_entity->$pk] = $foreign_entity->$description;
		}
		$refl = new \ReflectionClass(get_class($this->model));
		$modelname = $refl->getShortName();
		$idname = $modelname."[rel_ids_".$name."][]";
		$cl = new CheckboxList($idname, $input_options, $related_ids);
		return $cl->toString();
		//$s = new HtmlSelect($idname, $input_options, true, $related_ids);
		//return $s->toString();
	}
	private function inputHasOne($name, $options){
		$relation = $this->model->getRelationType($name);
		$foreign_model = $relation->foreign_class;
		$foreign_class = new $foreign_model();
		$pk = $foreign_class->pk;
		$all = $foreign_model::All();
		$select_box_description = $foreign_class->__description;
		$related_entity = $this->model->$name;
		$re_pk = $related_entity->pk;
		$related_id = $related_entity->$re_pk;
		$sel_options = array();
		foreach($all as $foreign_entity)
		{
			$sel_options[$foreign_entity->$pk] = $foreign_entity->$select_box_description;
		}
		$refl = new \ReflectionClass(get_class($this->model));
		$modelname = $refl->getShortName();
		$s = new HtmlSelect($modelname."[rel_ids_".$name."]", $sel_options, true, false, $related_id);
		return $s->toString();
	}
	private function inputHABTM($name, $options){
		return "Pretty much the same as the hasMany";
	}
	
}

?>
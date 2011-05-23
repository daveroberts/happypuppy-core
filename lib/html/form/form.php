<?php

namespace HappyPuppy;
class form
{
	private $model;
	private $modifier;

	function __construct($model, $modifier = ''){
		if ($model == null){ throw new \Exception("The model passed to the form is null"); }
		$this->model = $model;
		$this->modifier = $modifier;
	}
	public function start($location, $html_options = array()){
		return \form_start($location, $html_options);
	}
	public function hiddenID()
	{
		if (isset($this->model->pkval) &&
			!is_empty($this->model->pkval) &&
			$this->model->pkval != null)
		{
			return $this->hidden($this->model->pk, $this->model->pkval);
		}
		{
			return "";
		}
	}
	public function label($field, $label = '', $html_options = array()){
		if ($label == ''){ $label = $field; }
		return \label($label, $this->inputFieldDefaultID($field), $html_options);
	}
	public function hidden($field, $value){
		return \hidden($this->inputFieldDefaultID($field), $value);
	}
	public function inputHidden($property, $options = array())
	{
		return $this->input($property, $options, true);
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
	public function radio($name, $value, $options = array()){
		$selected = null;
		$mv = $this->model->getValue($name);
		if(strcasecmp($mv, $value) == 0)
		{
			$selected = true;
		}
		$r = new HtmlRadio($this->inputFieldDefaultID($name), $value, $selected, '', $options);
		return $r->toString();
	}
	public function submit($value, $html_options = array()){
		$s = new HtmlSubmit($value, $html_options);
		return $s->toString();
	}
	public function end(){
		return "</form>";
	}
	private function inputFieldDefaultID($name){
		$field_name = '';
		$refl = new \ReflectionClass(get_class($this->model));
		$modelname = $refl->getShortName();
		if ($this->model->hasRelation($name)){
			$field_name = $modelname."[rel_ids_".$name;
			if (strcmp($this->modifier,'') != 0){ $field_name .= '-'.$this->modifier; }
			$field_name .= "]";
		} else {
			$field_name = $modelname."[".$name;
			if (strcmp($this->modifier,'') != 0){ $field_name .= '-'.$this->modifier; }
			$field_name .= "]";
		}
		return $field_name;
	}
	private function inputField($name, $htmlOptions){
		$field_structure = DB::get_field_structure($this->model->tablename);
		$type = $field_structure["fields"][$name];
		if (substr($type, 0, 7) == "varchar" ||
			substr($type, 0, 3) == "int" ||
			substr($type, 0, 5) == "float" ||
			substr($type, 0, 4) == "date")
		{
			$t = new HtmlTextbox($this->inputFieldDefaultID($name), $this->model->$name, '', $htmlOptions);
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
		$related_ids = array();
		if ($related_entity != null)
		{
			$related_id = $related_entity->pkval;
			$related_ids[] = $related_id;
		}
		$sel_options = array();
		$default_values = array();
		if (isset($options["default_values"])){ $default_values = $options["default_values"]; }
		$default_ids = array();
		if (isset($options["default_ids"])){ $default_ids = $options["default_ids"]; }
		if (isset($options["default_value"])){ $default_values[] = $options["default_value"]; }
		if (isset($options["default_id"])){ $default_ids[] = $options["default_id"]; }
		foreach($all as $foreign_entity)
		{
			if (in_array($foreign_entity->$pk, $default_ids)){
				$related_ids[] = $foreign_entity->$pk;
			}
			if (in_array($foreign_entity->$select_box_description, $default_values)){
				$related_ids[] = $foreign_entity->$pk;
			}
			$sel_options[$foreign_entity->$pk] = $foreign_entity->$select_box_description;
		}
		$refl = new \ReflectionClass(get_class($this->model));
		$modelname = $refl->getShortName();
		$include_blank = false;
		if ($options["include_blank"] === true){ $include_blank = true; }
		$s = new HtmlSelect($modelname."[rel_ids_".$name."]", $sel_options, $include_blank, false, $related_ids);
		return $s->toString();
	}
	private function inputHABTM($name, $options){
		return "Pretty much the same as the hasMany";
	}
	
}

?>
<?

namespace HappyPuppy;
require_once('htmlElement.php');

class HtmlCheckbox extends HtmlElement
{
	var $checked;
	var $value;
	function __construct($id, $value, $checked = false, $name= '')
	{
		parent::__construct("input", true);
		$this->checked = $checked;
		$this->id = $id;
		$this->value = $value;
		$this->checked = $checked;
		if ($name == ''){ $name = $id; }
		$this->htmlOptions["name"] = $name;
		$this->htmlOptions["type"] = "checkbox";
	}
	function toString(){
		$this->htmlOptions["value"] = $this->value;
		if ($this->checked){
			$this->htmlOptions["checked"] = "checked";
		}
		return parent::toString();
	}
	public static function make($id, $value, $checked=false, $name=''){
		$cb = new HtmlCheckbox($id, $value, $checked, $name);
		return $cb->toString();
	}
}

?>
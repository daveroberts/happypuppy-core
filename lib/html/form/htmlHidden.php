<?
namespace HappyPuppy;
class HtmlHidden extends HtmlElement
{
	var $default_value;
	function __construct($name, $default_value, $id = '')
	{
		parent::__construct("input", true);
		$this->default_value = $default_value;
		if ($id == ''){ $id = $name; }
		$this->id = $id;
		$this->htmlOptions["name"] = $name;
		$this->htmlOptions["type"] = "hidden";
	}
	
	function toString()
	{
		$this->htmlOptions["value"] = $this->default_value;
		return parent::toString();
	}
	
	public static function make($name, $value){
		$hid = new HtmlHidden($name, $value);
		return $hid->toString();
	}
}

?>
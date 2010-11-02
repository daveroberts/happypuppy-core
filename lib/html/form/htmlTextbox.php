<?
namespace HappyPuppy;
class HtmlTextbox extends HtmlElement
{
	var $default_value;
	function __construct($id, $name = '', $default_value = '', $htmlOptions = array())
	{
		parent::__construct("input", true, $htmlOptions);
		$this->default_value = $default_value;
		$this->id = $id;
		if ($name == ''){ $name = $id; }
		$this->htmlOptions["name"] = $name;
		$this->htmlOptions["type"] = "text";
	}
	
	function toString()
	{
		$this->htmlOptions["value"] = $this->default_value;
		return parent::toString();
	}
}

?>
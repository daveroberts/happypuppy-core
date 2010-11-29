<?php
namespace HappyPuppy;
class HtmlTextbox extends HtmlElement
{
	var $default_value;
	function __construct($name, $default_value = '', $id='', $htmlOptions = array())
	{
		parent::__construct("input", true, $htmlOptions);
		$this->default_value = $default_value;
		$this->name = $name;
		if ($id == ''){ $id = $name; }
		$this->id = $id;
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
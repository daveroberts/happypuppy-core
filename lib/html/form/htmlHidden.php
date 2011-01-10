<?php
namespace HappyPuppy;
class HtmlHidden extends HtmlElement
{
	var $default_value;
	function __construct($name, $default_value, $id = '', $html_options = array())
	{
		parent::__construct("input", true, $html_options);
		$this->default_value = $default_value;
		if ($id == ''){ $id = $name; }
		$this->id = $id;
		$this->htmlOptions["name"] = $name;
		$this->htmlOptions["type"] = "hidden";
	}
	
	function toString()
	{
		$this->htmlOptions["value"] = htmlentities($this->default_value);
		return parent::toString();
	}
}

?>
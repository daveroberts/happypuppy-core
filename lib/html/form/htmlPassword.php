<?php
namespace HappyPuppy;
class HtmlPassword extends HtmlElement
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
		$this->htmlOptions["type"] = "password";
	}
	
	function toString()
	{
		$this->htmlOptions["value"] = htmlentities($this->default_value);
		return parent::toString();
	}
}

?>
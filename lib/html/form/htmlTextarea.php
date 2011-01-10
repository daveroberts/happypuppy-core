<?php
namespace HappyPuppy;
class HtmlTextarea extends HtmlElement
{
	var $default_value;
	function __construct($name, $default_value = '', $id='', $htmlOptions = array())
	{
		parent::__construct("textarea", false, $htmlOptions);
		$this->default_value = $default_value;
		$this->name = $name;
		if ($id == ''){ $id = $name; }
		$this->id = $id;
		$this->htmlOptions["name"] = $name;
	}
	
	function toString()
	{
		$this->innerHTML = $this->default_value;
		return parent::toString();
	}
}

?>
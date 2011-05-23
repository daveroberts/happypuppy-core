<?php
namespace HappyPuppy;
class HtmlRadio extends HtmlElement
{
	var $val;
	function __construct($groupname, $value, $selected = null, $id='', $htmlOptions = array())
	{
		parent::__construct("input", true, $htmlOptions);
		$this->val = $value;
		if ($id == ''){ $id = $groupname; }
		$this->id = $id;
		$this->htmlOptions["name"] = $groupname;
		$this->htmlOptions["type"] = "radio";
		if ($selected)
		{
			$this->htmlOptions["checked"] = "checked";
		}
	}
	
	function toString()
	{
		$this->htmlOptions["value"] = htmlentities($this->val);
		return parent::toString();
	}
}

?>
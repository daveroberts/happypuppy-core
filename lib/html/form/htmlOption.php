<?
namespace HappyPuppy;
class HtmlOption extends HtmlElement
{
	var $val;
	var $name;
	var $selected;
	function __construct($val, $name, $selected = false)
	{
		parent::__construct("option", false);
		$this->val = $val;
		$this->name = $name;
		$this->selected = $selected;
	}
	
	function toString()
	{
		if ($this->selected)
		{
			$this->htmlOptions["selected"] = "selected";
		}
		$this->htmlOptions["value"] = $this->val;
		$this->innerHTML = $this->name;
		return parent::toString();
	}
}

?>
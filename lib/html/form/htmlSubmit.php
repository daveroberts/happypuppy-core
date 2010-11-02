<?
namespace HappyPuppy;
class HtmlSubmit extends HtmlElement
{
	var $value;
	function __construct($value='', $html_options)
	{
		parent::__construct("input", true, $html_options);
		$this->value = $value;
		$this->htmlOptions["type"] = "submit";
	}
	
	function toString()
	{
		if ($this->value != "")
		{
			$this->htmlOptions["value"] = $this->value;
		}
		return parent::toString();
	}
}

?>
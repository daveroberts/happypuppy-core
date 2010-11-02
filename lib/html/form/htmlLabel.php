<?
namespace HappyPuppy;
class HtmlLabel extends HtmlElement
{
	function __construct($for, $innerHTML = '', $html_options = array())
	{
		parent::__construct("label", false, $html_options);
		if (!array_key_exists("id", $html_options))
		{
			$html_options["id"] = 'lbl'.$for;
		}
		$this->htmlOptions["for"] = $for;
		$this->innerHTML = $innerHTML;
	}
	
	function toString()
	{
		return parent::toString();
	}
	
	public static function make($for, $innerHTML = '', $html_options = array()){
		$l = new HtmlLabel($for, $innerHTML, $html_options);
		return $l->toString();
	}
}

?>
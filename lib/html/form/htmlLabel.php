<?php
namespace HappyPuppy;
class HtmlLabel extends HtmlElement
{
	function __construct($innerHTML, $for = '', $html_options = array())
	{
		parent::__construct("label", false, $html_options);
		if ($for != ''){
			$this->htmlOptions["for"] = $for;
			if (!array_key_exists("id", $html_options))
			{
				$html_options["id"] = 'lbl'.$for;
			}
		}
		$this->innerHTML = $innerHTML;
	}
	
	function toString()
	{
		return parent::toString();
	}
}

?>
<?php

namespace HappyPuppy;
require_once('htmlElement.php');

class HtmlAnchor extends HtmlElement
{
	var $href;
	var $html;
	function __construct($href, $html='', $html_options = array())
	{
		parent::__construct("a", false, $html_options);
		$this->href = $href;
		$this->html = $html;
	}
	
	function toString()
	{
		$this->htmlOptions["href"] = $this->href;
		if (strcmp($this->html, '') == 0)
		{
			$this->innerHTML = $this->href;
		}
		else
		{
			$this->innerHTML = $this->html;
		}
		return parent::toString();
	}
}

?>
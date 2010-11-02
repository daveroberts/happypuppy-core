<?

namespace HappyPuppy;
require_once('htmlElement.php');

class HtmlAnchor extends HtmlElement
{
	var $href;
	var $text;
	function __construct($href, $text='', $html_options = array())
	{
		parent::__construct("a", false, $html_options);
		$this->href = $href;
		$this->text = $text;
	}
	
	function toString()
	{
		$this->htmlOptions["href"] = $this->href;
		if (strcmp($this->text, '') == 0)
		{
			$this->innerHTML = $this->href;
		}
		else
		{
			$this->innerHTML = $this->text;
		}
		return parent::toString();
	}
}

?>
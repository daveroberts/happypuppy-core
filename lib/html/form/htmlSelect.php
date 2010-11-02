<?
namespace HappyPuppy;
class HtmlSelect extends HtmlElement
{
	var $opts;
	var $include_blank;
	var $multiple;
	var $selected_ids;
	function __construct($id, $opts, $include_blank = false, $multiple = false, $selected_ids = array(), $html_options = array())
	{
		parent::__construct("select", false);
		$this->opts = $opts;
		$this->include_blank = $include_blank;
		$this->multiple = $multiple;
		$this->selected_ids = $selected_ids;
		$this->id = $id;
		if (!array_key_exists('name', $html_options)){ $html_options['name'] = $id; }
		$this->htmlOptions = $html_options;
	}
	
	function toString()
	{
		if (!is_array($this->selected_ids))
		{
			$this->selected_ids = array($this->selected_ids);
		}
		if ($this->include_blank) {
			$o = new HtmlOption('', '');
			$this->innerHTML .= $o->toString();
		}
		foreach($this->opts as $val=>$name)
		{
			$o = new HtmlOption($val, $name);
			if (in_array($val, $this->selected_ids))
			{
				$o->selected = true;
			}
			$this->innerHTML .= $o->toString();
		}
		if ($this->multiple)
		{
			$this->htmlOptions["multiple"] = "multiple";
		}
		return parent::toString();
	}
}

?>
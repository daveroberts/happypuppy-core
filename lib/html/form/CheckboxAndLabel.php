<?
namespace HappyPuppy;
class CheckboxAndLabel
{
	private $_id;
	private $_labeltext;
	private $_html_options;
	function __construct($id, $labeltext, $html_options = array())
	{
		$this->_id = $id;
		$this->_labeltext = $labeltext;
		$this->_html_options;
	}
	
	function toString()
	{
		$label = new HtmlLabel()
		if (!is_array($this->selected_ids))
		{
			$this->selected_ids = array($this->selected_ids);
		}
		$current_id = 0;
		foreach($this->opts as $val=>$name)
		{
			$cl = new CheckboxAndLabel($this->name.$current_id, $this->name, $name, $val);
			$current_id++;
			if (in_array($val, $this->selected_ids))
			{
				$cl->checked = true;
			}
			$out .= "<div>".$cl->toString()."</div>\n";
		}
		$out .= "</div>";
		return $out;
	}
}

?>
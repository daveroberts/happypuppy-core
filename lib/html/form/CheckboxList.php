<?
namespace HappyPuppy;
class CheckboxList
{
	var $selected_ids;
	private $opts;
	private $name;
	function __construct($name, $opts, $selected_ids = array())
	{
		$this->opts = $opts;
		$this->selected_ids = $selected_ids;
		$this->name = $name;
	}
	
	function toString()
	{
		$out = "<div>";
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
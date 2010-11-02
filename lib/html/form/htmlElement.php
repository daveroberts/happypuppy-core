<?

namespace HappyPuppy;
class HtmlElement
{
	var $tagname;
	var $id;
	var $single;
	var $htmlOptions;
	var $innerHTML;
	var $doubleQuotes = true;
	
	function __construct($tagname, $single, $htmlOptions = array())
	{
		$this->tagname = $tagname;
		$this->single = $single;
		$this->htmlOptions = $htmlOptions;
	}
	
	public function toString()
	{
		$out = "<".$this->tagname." ";
		if ($this->id != '')
		{
			$this->id = $this->removeBrackets($this->id);
			$out .= " id=".$this->Q().$this->id.$this->Q()." ";
		}
		if (array_key_exists('name', $this->htmlOptions))
		{
			//requires further research
			//$this->htmlOptions['name'] = $this->removeBrackets($this->htmlOptions['name']);
		}
		if (array_key_exists('for', $this->htmlOptions))
		{
			$this->htmlOptions['for'] = $this->removeBrackets($this->htmlOptions['for']);
		}
		foreach($this->htmlOptions as $optname=>$optval)
		{
			$out .= " ".$optname."=".$this->Q().$optval.$this->Q()." ";
		}
		if ($this->single)
		{
			$out .= " />";
			return $out;
		}
		$out .= " >";
		$out .= $this->innerHTML;
		$out .= "</".$this->tagname.">";
		return $out;
	}
	
	private function removeBrackets($s)
	{
		$str = $s;
		$str = str_replace("[", "_", $str);
		$str = str_replace("]", "_", $str);
		if (substr($str, strlen($str) - 1) == '_') {
			$str = substr($str, 0, strlen($str) - 1);
		}
		return $str;
	}
	
	private function Q()
	{
		if ($this->doubleQuotes){ return '"'; }
		else { return "'"; }
	}
}

?>
<?php
namespace HappyPuppy;
class Inflector
{
	private static function irregular(){
		return array(
			"person"=>"people",
		);
	}
	public static function plural($noun){
		$noun = strtolower($noun);
		$irregular = Inflector::irregular();
		if (array_key_exists($noun, $irregular))
		{
			return $irregular[$noun];
		}
		if (substr($noun, strlen($noun) - 1, 1) == 'y')
		{
			$next_to_y = substr($noun, strlen($noun) - 2, 1);
			if (is_equal_ignore_case($next_to_y, 'a') ||
				is_equal_ignore_case($next_to_y, 'e') ||
				is_equal_ignore_case($next_to_y, 'i') ||
				is_equal_ignore_case($next_to_y, 'o') ||
				is_equal_ignore_case($next_to_y, 'u'))
			{
				return $noun.'s';
			}
			else
			{
				return substr($noun, 0, strlen($noun) - 1).'ies';
			}
		}
		if (substr($noun, strlen($noun) - 1, 1) == 's')
		{
			return substr($noun, 0, strlen($noun) - 1).'ses';
		}
		if (substr($noun, strlen($noun) - 1, 1) == 'z')
		{
			return substr($noun, 0, strlen($noun) - 1).'zes';
		}
		return $noun.'s';
	}
	public static function singular($noun){
		$noun = strtolower($noun);
		$irregular = Inflector::irregular();
		$key = array_search($noun, $irregular);
		if ($key)
		{
			return $key;
		}
		if (substr($noun, strlen($noun) - 3, 3) == 'ies')
		{
			return substr($noun, 0, strlen($noun) - 3).'y';
		}
		if (substr($noun, strlen($noun) - 3, 3) == 'zes')
		{
			return substr($noun, 0, strlen($noun) - 3).'z';
		}
		if (substr($noun, strlen($noun) - 3, 3) == 'ses')
		{
			return substr($noun, 0, strlen($noun) - 3).'s';
		}
		return rtrim($noun, 's');
	}
	public static function remove_underscores($name){
		return str_replace("_","",$name);
	}
}
?>

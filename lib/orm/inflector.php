<?
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
			return substr($noun, 0, strlen($noun) - 1).'ies';
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
		if (substr($noun, strlen($nount) - 3, 3) == 'ies')
		{
			return substr($noun, 0, strlen($noun) - 3).'y';
		}
		return rtrim($noun, 's');
	}
	public static function remove_underscores($name){
		return str_replace("_","",$name);
	}
}
?>
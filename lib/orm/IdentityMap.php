<?php
namespace HappyPuppy;
class IdentityMap
{
	private static $values = array(); // ['table']['id'] = object or null
	public static function is_set($table, $id)
	{
		if (!array_key_exists($table, IdentityMap::$values)){ return false; }
		if (!array_key_exists($id, IdentityMap::$values[$table])){ return false; }
		return true;
	}
	public static function set($table, $id, $object)
	{
		IdentityMap::$values[$table][$id] = $object;
	}
	public static function get($table, $id)
	{
		if (IdentityMap::is_set($table, $id))
		{
			return IdentityMap::$values[$table][$id];
		}
		return null;
	}
	public static function all()
	{
		print_r(IdentityMap::$values);
	}
}
?>

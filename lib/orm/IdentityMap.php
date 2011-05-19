<?php
namespace HappyPuppy;
class IdentityMap
{
	private static $values = array(); // ['table']['id'] = object or null
	public static function Is_Set($table, $id = null)
	{
		if (!array_key_exists($table, IdentityMap::$values)){ return false; }
		if ($id == null){ return true; }
		if (!array_key_exists($id, IdentityMap::$values[$table])){ return false; }
		return true;
	}
	public static function Set($table, $id, $object)
	{
		IdentityMap::$values[$table][$id] = $object;
	}
	public static function Get($table, $id)
	{
		if (IdentityMap::is_set($table, $id))
		{
			return IdentityMap::$values[$table][$id];
		}
		return null;
	}
	public static function GetAll($table)
	{
		if (IdentityMap::is_set($table))
		{
			return IdentityMap::$values[$table];
		}
		return null;
	}
	public static function AllRaw()
	{
		print_r(IdentityMap::$values);
	}
	public static function All()
	{
		foreach(IdentityMap::$values as $name=>$table)
		{
			print($name."\n");
			foreach($table as $obj)
			{
				print($obj->prettyPrint());
			}
		}
	}
}
?>

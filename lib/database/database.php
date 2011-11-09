<?php

namespace HappyPuppy;
require("DBMigration.php");
require("DBConnection.php");
require("DBMigrationExec.php");
class DB
{
	static function RootQuery($sql){
		$rootdb = DBConnection::GetRootDB();
		return DB::wQuery($rootdb, $sql);
	}
	static function AppQuery($app, $sql){
		$appdb = DBConnection::GetDB($app);
		return DB::wQuery($appdb, $sql);
	}
	static function query($sql){
		global $db; return DB::wQuery($db, $sql);
	}
	private static function wQuery($db, $sql) {
		if ($db == null){ throw new \Exception("Could not connect to database"); }
		$stmt = $db->prepare($sql);
		$time_start = microtime(true);
		$stmt->execute();
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		if ($_ENV["config"]["env"] == Environment::DEV)
		{
			Debug::sql($sql, $time);
		}
		$arr = array();
		while($row = $stmt->fetch(\PDO::FETCH_ASSOC))
		{
			$arr[] = $row;
		}
		return stripslashes_deep($arr);
	}
	static function RootExec($sql){
		$rootdb = DBConnection::GetRootDB();
		if ($rootdb == null){ throw new \Exception("Could not connect to root database"); }
		return DB::wExec($rootdb, $sql);
	}
	static function appExec($app, $sql){
		$appdb = DBConnection::GetDB($app);
		return DB::wExec($appdb, $sql);
	}
	static function exec($sql){
		global $db; return DB::wExec($db, $sql);
	}
	private static function wExec($db, $sql){
		if ($db == null){ throw new \Exception("Could not connect to database"); }
		$time_start = microtime(true);
		$result = $db->exec($sql);
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		if ($_ENV["config"]["env"] == Environment::DEV)
		{
			Debug::sql($sql, $time);
		}
		return $result; // $db->exec incorrectly returns 0 when 1 row is affected
	}
	static function lastInsertId()
	{
		global $db;
		return $db->lastInsertId();
	}
	static function get_field_structure($tablename)
	{
		global $__field_structure;
		if (!isset($__field_structure[$tablename]))
		{
			DB::build_field_structure($tablename);
		}
		return $__field_structure[$tablename];
	}
	static function build_field_structure($tablename)
	{
		global $__field_structure;
		$__field_structure[$tablename] = array();
		$sql = "DESCRIBE ".$tablename;
		$db_results = DB::query($sql);
		$rows = 0;
		foreach($db_results as $db_row)
		{
			$rows++;
			$__field_structure[$tablename]["fields"][$db_row["Field"]] = $db_row["Type"];
			if ($db_row["Key"] == "PRI")
			{
				$__field_structure[$tablename]["pk"] = $db_row["Field"];
			}
		}
		if ($rows == 0){ throw new \Exception("No table named $tablename found in database"); }
	}
}
function stripslashes_deep($value)
{
	/*$value = is_array($value) ?
		array_map('\HappyPuppy\stripslashes_deep', $value) :
		stripslashes($value);*/
	return $value;
}
?>

<?php

namespace HappyPuppy;
require("DBMigration.php");
require("DBConnection.php");
require("DBMigrationExec.php");
class DB
{
	private static function GetAppOrGlobalDB($app)
	{
		$dbh = null;
		if ($app != null)
		{
			$dbh = DBConnection::GetDB($app);
		}
		else
		{
			global $db;
			$dbh = $db;
		}
		if ($dbh == null){ throw new \Exception("Couldn't get DB: ".$app); }
		return $dbh;
	}
	public static function BeginTransaction($app = null){
		$dbh = DB::GetAppOrGlobalDB($app);
		$dbh->setAttribute(\PDO::ATTR_AUTOCOMMIT,FALSE);
		return $dbh->beginTransaction();
	}
	public static function Commit($app = null){
		$dbh = DB::GetAppOrGlobalDB($app);
		$value = $dbh->commit();
		$dbh->setAttribute(\PDO::ATTR_AUTOCOMMIT,TRUE);
		return $value;
	}
	public static function Rollback($app = null){
		$dbh = DB::GetAppOrGlobalDB($app);
		$value = $dbh->rollBack();
		$dbh->setAttribute(\PDO::ATTR_AUTOCOMMIT,TRUE);
		return $value;
	}
	static function RootQuery($sql){
		$rootdb = DBConnection::GetRootDB();
		return DB::wQuery($rootdb, $sql);
	}
	static function Query($sql, $params = null){
		if($params != null && !is_array($params))
		{
			$args = func_get_args();
			array_shift($args); //chop off $sql
			$params = $args;
		}
		global $db; return DB::wQuery($db, $sql, $params);
	}
	private static function wQuery($db, $sql, $params) {
		$time_start = microtime(true);
		$stm = $db->prepare($sql);
		$result = null;
		if(!$stm)
		{
			throw new \Exception("Could not create statement");
		}
		$result = $stm->execute($params);
		if (!$result)
		{
			throw new \Exception("SQL Exception in : ".$sql);
		}
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		if ($_ENV["config"]["env"] == Environment::DEV)
		{
			Debug::sql($sql, $time);
		}
		$arr = array();
		while($row = $stm->fetch(\PDO::FETCH_ASSOC))
		{
			$arr[] = $row;
		}
		return stripslashes_deep($arr);
	}
	static function RootExec($sql, $params = null){
		$rootdb = DBConnection::GetRootDB();
		return DB::wExec($rootdb, $sql, $params);
	}
	static function AppExec($app, $sql, $params = null){
		$appdb = DBConnection::GetDB($app);
		return DB::wExec($appdb, $sql, $params);
	}
	static function Exec($sql, $params = null){
		if($params != null && !is_array($params))
		{
			$args = func_get_args();
			array_shift($args); //chop off $sql
			$params = $args;
		}
		global $db; return DB::wExec($db, $sql, $params);
	}
	private static function wExec($db, $sql, $params){
		$time_start = microtime(true);
		$stm = $db->prepare($sql);
		$result = null;
		if(!$stm || !$stm->execute($params))
		{
			throw new \Exception("SQL Exception in : ".$sql);
		}
		$result = $stm->rowCount();
		//$result = $db->exec($sql);
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		if ($_ENV["config"]["env"] == Environment::DEV)
		{
			Debug::sql($sql, $time);
		}
		if ($result === false)
		{
			throw new Exception("SQL Exception");
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

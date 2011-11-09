<?php

namespace HappyPuppy;
class DBConnection
{
	public static function GetRootDB()
	{
		if (file_exists($_ENV["docroot"]."config/dbconf.php"))
		{
			require_once($_ENV["docroot"]."config/dbconf.php");
			$method_name = "RootDB";
			if (method_exists("\HappyPuppy\DBConf", $method_name))
			{
				$dbsettings = DBConf::$method_name();
				$pdo = new \PDO("mysql:host=".$dbsettings["hostname"].";dbname=".$dbsettings["dbname"]."", $dbsettings["dbusername"], $dbsettings["dbpassword"]);
				if ($pdo == null){ throw new \Exception("Cannot connect to database as root"); }
				return $pdo;
			}
		}
	}
	public static function GetDBName($app)
	{
		if (file_exists($_ENV["docroot"]."apps/".$app."/db/conf.php"))
		{
			$klass = "\\".$app."\DBConf";
			require_once($_ENV["docroot"]."apps/".$app."/db/conf.php");
			if (method_exists($klass, "DB"))
			{
				$dbsettings = $klass::DB();
				return $dbsettings["dbname"];
			}
		}
		return null;
	}
	public static function GetDB($app)
	{
		if (file_exists($_ENV["docroot"]."apps/".$app."/db/conf.php"))
		{
			$klass = "\\".$app."\DBConf";
			require_once($_ENV["docroot"]."apps/".$app."/db/conf.php");
			if (method_exists($klass, "DB"))
			{
				$dbsettings = $klass::DB();
				$pdo = new \PDO("mysql:host=".$dbsettings["hostname"].";dbname=".$dbsettings["dbname"]."", $dbsettings["dbusername"], $dbsettings["dbpassword"]);
				if ($pdo == null){ throw new \Exception("Cannot connect to database as root"); }
				return $pdo;
			}
		}
	}
	public static function SetDB($app)
	{
		global $db;
		$db = DBConnection::GetDB($app);
	}
}
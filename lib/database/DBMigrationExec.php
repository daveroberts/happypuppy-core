<?php

namespace HappyPuppy;
class DBMigrationExec
{
	static function Version($app, $set_to_version = null)
	{
		$dbname = DBConnection::GetDBName($app);
		if ($set_to_version == null)
		{
			DBMigrationExec::CreateVersionTableIfNotExists($dbname);
			$results = DB::RootQuery("select * from ".$dbname.".dbversion");
			$row = reset($results);
			if ($row == null){ return 0; }
			$version = reset($row);
			return $version;
		}
		else
		{
			DBMigrationExec::CreateVersionTableIfNotExists($dbname);
			$sql = "UPDATE ".$dbname.".`dbversion` SET version=".$set_to_version;
			DB::RootExec($sql);
			return $set_to_version;
		}
	}
	private static function CreateVersionTableIfNotExists($dbname)
	{
		$results = DB::RootQuery("select * from ".$dbname.".dbversion");
		if (count($results) == 0)
		{
			$sql = "CREATE TABLE ".$dbname.".`dbversion` (`version` INT NOT NULL) ENGINE = MYISAM ;";
			DB::RootExec($sql);
			$sql = "INSERT INTO ".$dbname.".`dbversion` (`version`)VALUES ('0');";
			DB::RootExec($sql);
		}
	}
	static function HighestVersionAvailable($app)
	{
		$filename = $_ENV["docroot"]."apps/".$app."/db/migrations.php";
		if (!file_exists($filename)) { return 0; }
		require_once($filename);
		$class_name = "\\".$app."\\Migrations";
		$cur = 0;
		$done = false;
		while (!$done)
		{
			$next = $cur + 1;
			if (method_exists($class_name, "From".$cur."To".$next))
			{
				$cur++;
			} else {
				$done = true;
			}
		}
		return $cur;
	}
	static function MigrateDB($app, $target_version)
	{
		$db_version = DBMigrationExec::Version($app);
		if ($db_version == $target_version){ return; } // nothing to do
		require_once($_ENV["docroot"]."apps/".$app."/db/migrations.php");
		$has_dev_data = false;
		if (is_file($_ENV["docroot"]."apps/".$app."/db/devdata.php"))
		{
			require_once($_ENV["docroot"]."apps/".$app."/db/devdata.php");
			$has_dev_data = true;
		}
		$class_name = "\\".$app."\\Migrations";
		while($target_version != $db_version)
		{
			$next_version;
			if ($db_version < $target_version){ $next_version = $db_version + 1; }
			else { $next_version = $db_version - 1; }
			$class_name = "\\".$app."\\Migrations";
			$method_name = "From".$db_version."To".$next_version;
			if ($next_version > $db_version && !method_exists($class_name, $method_name))
			{
				throw new \Exception("You must have a method named $method_name in $class_name if you want to migrate your database from version $db_version to $target_version");
			}
			$result = $class_name::$method_name();
			// import dev data if applicable
			if ($has_dev_data && $_ENV['config']['env'] == Environment::DEV)
			{
				$class_name = "\\".$app."\\DevData";
				$method_name = "From".$db_version."To".$next_version;
				if (method_exists($class_name, $method_name))
				{
					$result = $class_name::$method_name();
				}
			}
			$db_version = DBMigrationExec::Version($app, $next_version);
		}
	}
}

?>
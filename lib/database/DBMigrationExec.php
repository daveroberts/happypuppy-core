<?php

namespace HappyPuppy;
class DBMigrationExec
{
	static function GetVersion($dbname)
	{
		$db_exists = DBMigrationExec::DatabaseExists($dbname);
		if (!$db_exists){ return 0; }
		$results = DB::RootQuery("select * from ".$dbname.".dbversion");
		$row = reset($results);
		if ($row == null){ return 0; }
		$version = reset($row);
		return $version;
	}
	static function SetVersion($dbname, $set_to_version)
	{
		$db_exists = DBMigrationExec::DatabaseExists($dbname);
		if (!$db_exists){ return false; }
		$sql = "UPDATE ".$dbname.".`dbversion` SET version=".$set_to_version;
		DB::RootExec($sql);
		return $set_to_version;
	}
	private static function CreateDB($dbname)
	{
		$filename = $_ENV["docroot"]."apps/".$dbname."/db/migrations.php";
		if (!file_exists($filename))
		{
			throw new \Exception("Could not find db migration file: ".$filename);
		}
		require_once($filename);
		$class_name = "\\".$dbname."\\Migrations";
		$method_name = "CreateUserAndDB";
		if (!method_exists($class_name, $method_name))
		{
			throw new \Exception("No method ".$method_name." exists in class ".$class_name);
		}
		$arr = $class_name::$method_name();
		$sql = "
			CREATE USER '".$arr['username']."'@'%' IDENTIFIED BY '".$arr['password']."';
			GRANT USAGE ON * . * TO '".$arr['username']."'@'%' IDENTIFIED BY '".$arr['password']."' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;
			CREATE DATABASE `".$arr['dbname']."` ;
			GRANT ALL PRIVILEGES ON `".$arr['dbname']."` . * TO '".$arr['username']."'@'%';
		";
		DB::RootExec($sql);
		DB::RootExec("CREATE TABLE ".$dbname.".`dbversion` (`version` INT NOT NULL) ENGINE = innodb ;");
		DB::RootExec("INSERT INTO ".$dbname.".`dbversion` (`version`)VALUES ('1');");
        DB::RootExec("FLUSH PRIVILEGES;");
		return true;
	}
	private static function DropUserAndDB($app)
	{
		require_once($_ENV["docroot"]."apps/".$app."/db/migrations.php");
		$class_name = "\\".$app."\\Migrations";
		$method_name = "CreateUserAndDB";
		$arr = $class_name::$method_name();
		$sql = "
			DROP USER '".$arr['username']."'@'%';
			DROP DATABASE IF EXISTS `".$arr['dbname']."` ;
		";
		DB::RootExec($sql);
		return true;
	}
	private static function DatabaseExists($dbname)
	{
		$results = DB::RootQuery("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$dbname."'");
		return (count($results) != 0);
	}
	static function HighestVersionAvailable($app)
	{
		$filename = $_ENV["docroot"]."apps/".$app."/db/migrations.php";
		if (!file_exists($filename)) { return 0; }
		require_once($filename);
		$class_name = "\\".$app."\\Migrations";
		$cur = 1;
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
	static function MigrateDB($app, $target_version, &$message = '')
	{
		$num_migrations_performed = 0;
		$num_dev_data_loaded = 0;
		$dbname = DBConnection::GetDBName($app);
		$db_version = DBMigrationExec::GetVersion($dbname);
		if ($db_version == $target_version)
		{
			// nothing to do
			$message = "DB version is already $db_version";
			return true;
		}
		if ($db_version == 0)
		{
			DBMigrationExec::CreateDB($dbname);
			$db_version = DBMigrationExec::GetVersion($dbname);
		}
		if ($target_version == 0)
		{
			DBMigrationExec::DropUserAndDB($dbname);
			return true;
		}
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
			try
			{
				DB::BeginTransaction($dbname);
				$result = $class_name::$method_name();
				DB::Commit($dbname);
			}
			catch(\Exception $e)
			{
				DB::Rollback($dbname);
				$message = "Unabled to complete database migration\n";
				$message .= "Stopped at version ".$db_version."\n";
				$message .= $e->getMessage();
				return false;
			}
			$num_migrations_performed++;
			// import dev data if applicable
			if ($has_dev_data && $_ENV['config']['env'] == Environment::DEV)
			{
				$class_name = "\\".$app."\\DevData";
				$method_name = "From".$db_version."To".$next_version;
				if (method_exists($class_name, $method_name))
				{
					$result = $class_name::$method_name();
					$num_dev_data_loaded++;
				}
			}
			$db_version = DBMigrationExec::SetVersion($dbname, $next_version);
		}
		$message = "Performed $num_migrations_performed migration(s) with $num_dev_data_loaded set(s) of dev data";
		return true;
	}
}

?>

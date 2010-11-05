<?

namespace HappyPuppy;
class DBMigrationExec
{
	public static function MigrateDB($app, $version)
	{
		if ($_ENV['config']['env'] != Environment::DEV){ throw new \Exception('You tried to migrate your application while not in Development mode.  Set your environment $_ENV[\'config\'][\'env\'] to Environment::DEV in /config/hp.php'); }
		$db_version = DBMigrationExec::Version($app);
		//print("DB Version: ".$db_verison." Target version: ".$version); exit();
		if ($db_version > $version)
		{
			DBMigrationExec::MigrateDownTo($app, $version);
		}
		else if ($db_version < $version)
		{
			DBMigrationExec::MigrateUpTo($app, $version);
		}
		else
		{
			// nothing to do, database already is this version
		}
	}
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
		if ($_ENV['config']['env'] != Environment::DEV)
		{
			return 0;
		}
		$filename = $_ENV["docroot"]."apps/".$app."/db/migrations.php";
		if (!file_exists($filename)) { return 0; }
		require_once($filename);
		$class_name = "\\".$app."\\".$app."Migrations";
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
	static function MigrateUpTo($app, $version)
	{
		if ($_ENV['config']['env'] != Environment::DEV)
		{
			throw new \Exception('You tried to migrate your application while not in Development mode.  Set your environment $_ENV[\'config\'][\'env\'] to Environment::DEV in /config/hp.php');
		}
		$db_version = DBMigrationExec::Version($app);
		if ($version <= $db_version){ throw new \Exception("Can't migrate up from database version ".$db_version." to app version ".$version.".  If you believe the database is actually a different version than this, you can edit the dbversion table in your database manually"); }
		require_once($_ENV["docroot"]."apps/".$app."/db/migrations.php");
		$class_name = "\\".$app."\\".$app."Migrations";
		while($db_version < $version)
		{
			$db_version;
			$next_version = $db_version + 1;
			$class_name = "\\".$app."\\".$app."Migrations";
			$method_name = "From".$db_version."To".$next_version;
			if (method_exists($class_name, $method_name))
			{
				$result = $class_name::$method_name();
				$db_version = $next_version;
				DBMigrationExec::Version($app, $db_version);
			}
			else
			{
				throw new \Exception("Tried to load migration from class $class_name, but couldn't find a method named $method_name");
			}
		}
	}
	static function MigrateDownTo($app, $version)
	{
		if ($_ENV['config']['env'] != Environment::DEV)
		{
			throw new \Exception('You tried to migrate your application while not in Development mode.  Set your environment $_ENV[\'config\'][\'env\'] to Environment::DEV in /config/hp.php');
		}
		$db_version = DBMigrationExec::Version($app);
		if ($version >= $db_version){ throw new \Exception("Can't migrate down from database version ".$db_version." to app version $version.  If you believe the database is actually a different version than this, you can edit the dbversion table in your database manually"); }
		require_once($_ENV["docroot"]."apps/".$app."/db/migrations.php");
		$class_name = "\\".$app."\\".$app."Migrations";
		while($db_version > $version)
		{
			$next_version = $db_version - 1;
			$class_name = "\\".$app."\\".$app."Migrations";
			$method_name = "From".$db_version."To".$next_version;
			if (method_exists($class_name, $method_name))
			{
				print("Migrating down: ".$class_name."-".$method_name); 
				$result = $class_name::$method_name();
				$db_version = $next_version;
				print("Changing to ".$db_version." ");
				DBMigrationExec::Version($app, $db_version);
			}
			else
			{
				throw new \Exception("Tried to load migration from class $class_name, but couldn't find a method named $method_name");
			}
		}
	}
}

?>
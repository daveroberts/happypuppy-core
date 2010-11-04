<?

namespace HappyPuppy;
class DB
{
	public static function CreateUserAndDB($rootdb, $username, $password, $dbname)
	{
		$sql = "
			CREATE USER '".$username."'@'%' IDENTIFIED BY '".$password."';
			GRANT USAGE ON * . * TO '".$username."'@'%' IDENTIFIED BY '".$password."' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;
			CREATE DATABASE IF NOT EXISTS `".$dbname."` ;
			GRANT ALL PRIVILEGES ON `".$dbname."` . * TO '".$username."'@'%';
		";
		$rootdb->exec($sql);
	}
	public static function DropUserAndDB($rootdb, $username, $dbname)
	{
		$sql = "
			DROP USER '".$username."'@'%';
			DROP DATABASE IF EXISTS `".$dbname."` ;
		";
		$rootdb->exec($sql);
	}
	public static function CreateTable($tablename, $columns)
	{
		$sql = "
			CREATE TABLE `".$tablename."` (
				`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,";
		foreach($columns as $name=>$type){
			$sql .= DB::ColumnSQL($name, $type);
			$sql .= " , ";
		}
		$sql = substr($sql, 0, strlen($sql) - 2);
		$sql .= ") ENGINE = MYISAM ;";
		DB::Exec($sql);
	}
	private static function ColumnSQL($name, $type)
	{
		$sql = "`".$name."` ";
		if (strcasecmp($type, "string") == 0){
			$sql .= "VARCHAR(255) NOT NULL ";
		} else if (strcasecmp($type, "int") == 0){
			$sql .= "INT NOT NULL ";
		} else if (strcasecmp($type, "bool") == 0 || strcasecmp($type, "boolean") == 0){
			$sql .= "TINYINT NOT NULL ";
		} else if (strcasecmp($type, "text") == 0){
			$sql .= "TEXT NOT NULL ";
		} else if (strcasecmp($type, "date") == 0){
			$sql .= "DATE NOT NULL ";
		} else if (strcasecmp($type, "datetime") == 0){
			$sql .= "DATETIME NOT NULL ";
		} else if (strcasecmp($type, "float") == 0){
			$sql .= "FLOAT NOT NULL ";
		} else {
			$sql .= $type;
		}
		return $sql;
	}
	public static function DropTable($tablename)
	{
		$sql = "DROP TABLE `".$tablename."` ";
		DB::Exec($sql);
	}
	public static function AddColumn($tablename, $colname, $coltype)
	{
		$sql = "ALTER TABLE `".$tablename."` ADD ".DB::ColumnSQL($colname, $coltype);
		DB::Exec($sql);
	}
	public static function DropColumn($tablename, $colname)
	{
		$sql = "ALTER TABLE `".$tablename."` DROP `".$colname."`";
		DB::Exec($sql);
	}
	public static function LoadDB($app)
	{
		if (file_exists($_ENV["docroot"]."config/DBConf.php"))
		{
			require_once($_ENV["docroot"]."config/DBConf.php");
			$method_name = $app."DBInit";
			if (method_exists("\HappyPuppy\DBConf", $method_name))
			{
				DBConf::$method_name();
			}
		}
	}
	public static function MigrateDB($app, $version)
	{
		if ($_ENV['config']['env'] != Environment::DEV){ throw new \Exception('You tried to migrate your application while not in Development mode.  Set your environment $_ENV[\'config\'][\'env\'] to Environment::DEV in /config/hp.php'); }
		if (!DB::Exists()){ throw new \Exception("Can't connect to DB"); }
		if ($db_version > $version)
		{
			MigrateDownTo($version);
		}
		else if ($db_version < $version)
		{
			MigrateUpTo($version);
		}
		else
		{
			// nothing to do, database already is this version
		}
	}
	static function Exists()
	{
		global $db;
		return ($db != null);
	}
	static function Version($set_to_version = null)
	{
		if ($set_to_version == null)
		{
			$results = DB::query("select * from dbversion");
			if (count($results) == 0)
			{
				$sql = "CREATE TABLE `dbversion` (`version` INT NOT NULL) ENGINE = MYISAM ;";
				DB::exec($sql);
				$sql = "INSERT INTO `dbversion` (`version`)VALUES ('0');";
				DB::exec($sql);
			}
			$results = DB::query("select * from dbversion");
			$version = reset(reset($results));
			return $version;
		}
		else
		{
			$sql = "UPDATE `dbversion` SET version=".$set_to_version;
			DB::exec($sql);
		}
	}
	static function HighestVersionAvailable($app)
	{
		if ($_ENV['config']['env'] != Environment::DEV)
		{
			return 0;
		}
		require_once($_ENV["docroot"]."apps/".$app."/db/migrations.php");
		$class_name = "\\".$app."\\".$app."Migrations";
		$cur = 0;
		$done = false;
		while (!$done)
		{
			$next = $cur + 1;
			if (method_exists($class_name, "From$curTo$next"))
			{
				$cur++;
			} else {
				$done = true;
			}
		}
		return $cur;
	}
	static function MigrateUpTo($version)
	{
		if ($_ENV['config']['env'] != Environment::DEV)
		{
			throw new \Exception('You tried to migrate your application while not in Development mode.  Set your environment $_ENV[\'config\'][\'env\'] to Environment::DEV in /config/hp.php');
		}
		if (!DB::Exists()) { throw new \Exception("Couldn't connect to database"); }
		$db_version = DB::Version();
		if ($version <= $db_version){ throw new \Exception("Can't migrate up from database version ".$db_version." to app version $version.  If you believe the database is actually a different version than this, you can edit the dbversion table in your database manually"); }
		require_once($_ENV["docroot"]."apps/".$app."/db/migrations.php");
		$class_name = "\\".$app."\\".$app."Migrations";
		while($db_version < $version)
		{
			$db_version;
			$next_version = $db_version + 1;
			$class_name = "\\".$app."\\".$app."Migrations";
			$method_name = $class_name, "From".$db_version."To".$next_version;
			if (method_exists($method_name))
			{
				$result = $class_name::$method_name();
				if (!$result)
				{
					throw new \Exception("Migration ".$next_version." failed for ".$app);
				}
				else
				{
					$db_version = $next_version;
					DB::Version($db_version);
				}
			}
			else
			{
				throw new \Exception("Tried to load migration from class $class_name, but couldn't find a method named $method_name");
			}
		}
	}
	static function MigrateDownTo($version)
	{
		if ($_ENV['config']['env'] != Environment::DEV)
		{
			throw new \Exception('You tried to migrate your application while not in Development mode.  Set your environment $_ENV[\'config\'][\'env\'] to Environment::DEV in /config/hp.php');
		}
		if (!DB::Exists()) { throw new \Exception("Couldn't connect to database"); }
		$db_version = DB::Version();
		if ($version >= $db_version){ throw new \Exception("Can't migrate down from database version ".$db_version." to app version $version.  If you believe the database is actually a different version than this, you can edit the dbversion table in your database manually");
		require_once($_ENV["docroot"]."apps/".$app."/db/migrations.php");
		$class_name = "\\".$app."\\".$app."Migrations";
		while($db_version > $version)
		{
			$db_version;
			$next_version = $db_version - 1;
			$class_name = "\\".$app."\\".$app."Migrations";
			$method_name = $class_name, "From".$db_version."To".$next_version;
			if (method_exists($method_name))
			{
				$result = $class_name::$method_name();
				if (!$result)
				{
					throw new \Exception("Migration ".$next_version." failed for ".$app);
				}
				else
				{
					$db_version = $next_version;
					DB::Version($db_version);
				}
			}
			else
			{
				throw new \Exception("Tried to load migration from class $class_name, but couldn't find a method named $method_name");
			}
		}
	}
	static function query($sql)
	{
		global $db;
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$arr = array();
		while($row = $stmt->fetch(\PDO::FETCH_ASSOC))
		{
			$arr[] = $row;
		}
		return stripslashes_deep($arr);
	}
	static function exec($sql)
	{
		global $db;
		$db->exec($sql);
		return true; // $db->exec incorrectly returns 0 when 1 row is affected
	}
	static function lastInsertId()
	{
		global $db;
		return $db->lastInsertId();
	}
	static function assoc($sql, $key, $value)
	{
		global $db;
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$arr = array();
		while($row = stripslashes_deep($stmt->fetch(\PDO::FETCH_ASSOC)))
		{
			$arr[$row[$key]] = $row[$value];
		}
		return $arr;
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
	$value = is_array($value) ?
		array_map('\HappyPuppy\stripslashes_deep', $value) :
		stripslashes($value);
	return $value;
}
?>

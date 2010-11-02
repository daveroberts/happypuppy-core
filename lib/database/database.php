<?

namespace HappyPuppy;
class DB
{
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
	public static function MigrateDB($app)
	{
		if (file_exists($_ENV["docroot"]."config/DBConf.php"))
		{
			if ($_ENV['config']['env'] == Environment::DEV && DB::Exists())
			{
				$db_version = DB::Version();
				$method_name = $app."DBVersion";
				if (method_exists("\HappyPuppy\DBConf", $method_name))
				{
					$app_version = DBConf::$method_name();
					if ($app_version > $db_version)
					{
						$dir = $_ENV["docroot"]."apps/".$app."/db/*.php";
						include_dir($dir);
						while($app_version > $db_version)
						{
							$db_version++;
							$class_name = "\\".$app."\\DB".$app.$db_version;
							if (method_exists($class_name, "Up"))
							{
								$result = $class_name::Up();
								if (!$result)
								{
									throw new \Exception("Migration ".$db_version." failed for ".$app);
								}
								else
								{
									DB::Version($db_version);
								}
							}
							else
							{
								throw new \Exception("Tried to load migration from class $class_name, but couldn't find it in $dir");
							}
						}
					}
				}
			}
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

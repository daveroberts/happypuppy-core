<?

namespace HappyPuppy;
class DBConnection
{
	public static function GetRootDB()
	{
		if (file_exists($_ENV["docroot"]."config/DBConf.php"))
		{
			require_once($_ENV["docroot"]."config/DBConf.php");
			$method_name = "RootDB";
			if (method_exists("\HappyPuppy\DBConf", $method_name))
			{
				$dbsettings = DBConf::$method_name();
				return new \PDO("mysql:host=".$dbsettings["hostname"].";dbname=".$dbsettings["dbname"]."", $dbsettings["dbusername"], $dbsettings["dbpassword"]);
			}
		}
	}
	public static function GetDBName($app)
	{
		if (file_exists($_ENV["docroot"]."config/DBConf.php"))
		{
			require_once($_ENV["docroot"]."config/DBConf.php");
			$method_name = $app."DB";
			if (method_exists("\HappyPuppy\DBConf", $method_name))
			{
				$dbsettings = DBConf::$method_name();
				return $dbsettings["dbname"];
			}
		}
		return null;
	}
	public static function GetDB($app)
	{
		if (file_exists($_ENV["docroot"]."config/DBConf.php"))
		{
			require_once($_ENV["docroot"]."config/DBConf.php");
			$method_name = $app."DB";
			if (method_exists("\HappyPuppy\DBConf", $method_name))
			{
				$dbsettings = DBConf::$method_name();
				return new \PDO("mysql:host=".$dbsettings["hostname"].";dbname=".$dbsettings["dbname"]."", $dbsettings["dbusername"], $dbsettings["dbpassword"]);
			}
		}
	}
	public static function SetDB($app)
	{
		global $db;
		$db = DBConnection::GetDB($app);
	}
}
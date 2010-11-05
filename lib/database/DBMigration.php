<?

namespace HappyPuppy;
class DBMigration
{
	public static function CreateUserAndDB($username, $password, $dbname)
	{
		$sql = "
			CREATE USER '".$username."'@'%' IDENTIFIED BY '".$password."';
			GRANT USAGE ON * . * TO '".$username."'@'%' IDENTIFIED BY '".$password."' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;
			CREATE DATABASE IF NOT EXISTS `".$dbname."` ;
			GRANT ALL PRIVILEGES ON `".$dbname."` . * TO '".$username."'@'%';
		";
		DB::RootExec($sql);
	}
	public static function DropUserAndDB($username, $dbname)
	{
		$sql = "
			DROP USER '".$username."'@'%';
			DROP DATABASE IF EXISTS `".$dbname."` ;
		";
		print($sql);
		DB::RootExec($sql);
	}
	public static function CreateTable($app, $tablename, $columns)
	{
		$sql = "
			CREATE TABLE `".$tablename."` (
				`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,";
		foreach($columns as $name=>$type){
			$sql .= DBMigration::ColumnSQL($name, $type);
			$sql .= " , ";
		}
		$sql = substr($sql, 0, strlen($sql) - 2);
		$sql .= ") ENGINE = MYISAM ;";
		DB::AppExec($app, $sql);
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
	public static function DropTable($app, $tablename)
	{
		$sql = "DROP TABLE `".$tablename."` ";
		print($sql);
		DB::AppExec($app, $sql);
	}
	public static function AddColumn($app, $tablename, $colname, $coltype)
	{
		$sql = "ALTER TABLE `".$tablename."` ADD ".DBMigration::ColumnSQL($colname, $coltype);
		DB::AppExec($app, $sql);
	}
	public static function DropColumn($app, $tablename, $colname)
	{
		$sql = "ALTER TABLE `".$tablename."` DROP `".$colname."`";
		DB::AppExec($app, $sql);
	}
}

?>
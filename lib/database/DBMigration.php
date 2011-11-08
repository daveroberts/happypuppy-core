<?php

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
        DB::RootExec("FLUSH PRIVILEGES;");
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
		foreach($columns as $name=>$options){
			$sql .= DBColumn::OptionsToSQL($name, $options);
			$sql .= " , ";
		}
		$sql = substr($sql, 0, strlen($sql) - 2);
		$sql .= ") ENGINE = MYISAM ;";
		DB::AppExec($app, $sql);
	}
	public static function DropTable($app, $tablename)
	{
		$sql = "DROP TABLE `".$tablename."` ";
		print($sql);
		DB::AppExec($app, $sql);
	}
	public static function AddColumn($app, $tablename, $colname, $options)
	{
		$sql = "ALTER TABLE `".$tablename."` ADD ".DBColumn::OptionsToSQL($colname, $options);
		DB::AppExec($app, $sql);
	}
	public static function DropColumn($app, $tablename, $colname)
	{
		$sql = "ALTER TABLE `".$tablename."` DROP `".$colname."`";
		DB::AppExec($app, $sql);
	}
}

class DBColumn
{
	private $name;
	private $type;
	private $nullable;
	private $defaultval;
	function __construct($name, $type = 'string', $nullable = false, $defaultval = null)
	{
		$this->name = $name;
		$this->type = $type;
		$this->nullable = $nullable;
		$this->defaultval = $defaultval;
	}
	public static function OptionsToSQL($name, $options)
	{
		$nullable = false;
		$defaultval = null;
		$arr = preg_split('/;/', $options);
		$type = $arr[0];
		if (isset($arr[1])){ $nullable = $arr[1]; }
		if (isset($arr[2])){ $defaultval = $arr[2]; }
		$dbcol = new DBColumn($name, $type, $nullable, $defaultval);
		return $dbcol->toString();
	}
	public function toString()
	{
		$sql = "`".$this->name."` ";
		if (strcasecmp($this->type, "string") == 0){
			$sql .= "VARCHAR(255) ";
		} else if (strcasecmp($this->type, "int") == 0){
			$sql .= "INT ";
		} else if (strcasecmp($this->type, "bool") == 0 || strcasecmp($this->type, "boolean") == 0){
			$sql .= "TINYINT ";
		} else if (strcasecmp($this->type, "text") == 0){
			$sql .= "TEXT ";
		} else if (strcasecmp($this->type, "date") == 0){
			$sql .= "DATE ";
		} else if (strcasecmp($this->type, "datetime") == 0){
			$sql .= "DATETIME ";
		} else if (strcasecmp($this->type, "float") == 0){
			$sql .= "FLOAT ";
		} else {
			$sql .= $this->type;
		}
		if (!$this->nullable)
		{
			$sql .= " NOT NULL ";
		}
		if ($this->defaultval !== null)
		{
			if (strcasecmp($this->type, "string") == 0){
				$sql .= " DEFAULT '".$this->defaultval."' ";
			} else if (strcasecmp($this->type, "int") == 0){
				$sql .= " DEFAULT ".$this->defaultval." ";
			} else if (strcasecmp($this->type, "bool") == 0 || strcasecmp($this->type, "boolean") == 0){
				if ($this->defaultval) {
					$sql .= " DEFAULT TRUE ";
				} else {
					$sql .= " DEFAULT FALSE ";
				}
			} else if (strcasecmp($this->type, "text") == 0){
				$sql .= " DEFAULT '".$this->defaultval."' ";
			} else if (strcasecmp($this->type, "date") == 0){
				$sql .= " DEFAULT ".$this->defaultval." ";
			} else if (strcasecmp($this->type, "datetime") == 0){
				$sql .= " DEFAULT ".$this->defaultval." ";
			} else if (strcasecmp($this->type, "float") == 0){
				$sql .= " DEFAULT ".$this->defaultval." ";
			} else {
				$sql .= " DEFAULT ".$this->defaultval." ";
			}
		}
		return $sql;
	}
	public static function ColumnSQL($name, $type = 'string', $nullable = false, $defaultval = null)
	{
		$dbcol = new DBColumn($name, $type, $nullable, $defaultval);
		return $dbcol->toString();
	}
}

?>
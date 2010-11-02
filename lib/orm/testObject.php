<?php

require_once("dbobjectCollection.php");

class TestObject
{
	private $dboc;
	function __construct()
	{
		$this->dboc = null;
	}
	function __get($name)
	{
		print("Current status of ".$name."\n");
		print_r($this->dboc);
		print("\nEND STATUS\n");
		if ($this->dboc == null)
		{
			$this->dboc = new dbobjectCollection(array(22,33));
		}
		return $this->dboc;
	}
	function __set($name, $value)
	{
		print("Calling set on ".$name." -> ".$value);
		$this->dboc = $value;
	}
}

?>
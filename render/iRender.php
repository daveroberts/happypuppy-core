<?
require_once("php/phpRender.php");
require_once("haml/hamlRender.php");

interface iRender
{
	public function process($controller_obj, $controller_name, $action);
	public function file_with_arr($file, $arr);
	public function file_with_obj($file, $obj);
	public function file_with_var($file, $varname, $var);
	public function file($file);
}
?>
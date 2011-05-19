<?php

function include_dir($pattern)
{
	ob_start();
	foreach (glob($pattern) as $file)
	{
		include_once($file);
	}
	ob_end_clean();
}

function isAjaxRequest(){
	return 	!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function is_empty($str){
	return strcmp($str, '') == 0;
}

function is_equal($str1, $str2){
	return strcasecmp($str1, $str2) == 0;
}

function is_equal_ignore_case($str1, $str2){
	return strcmp($str1, $str2) == 0;
}

function setflash($str){
	if ($str == null || strcmp($str, '') == 0){ return; }
	$_SESSION["flash"] = $str;
}
function hasflash(){
	return isset($_SESSION["flash"]);
}
function getflash($remove=true){
	if (!hasflash()){ return ""; }
	$retval = $_SESSION["flash"];
	if ($remove){ unset($_SESSION["flash"]); }
	return $retval;
}

function cycle($first, $second)
{
	static $cycle_even_odd = 0;
	$cycle_even_odd++;
	$cycle_even_odd = $cycle_even_odd % 2;
	if ($cycle_even_odd == 0){ return $first; }
	return $second;
}

function osort(&$array, $prop)
{
    usort($array, function($a, $b) use ($prop) {
        return $a->$prop > $b->$prop ? 1 : -1;
    }); 
}


?>
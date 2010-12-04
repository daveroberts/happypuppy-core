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
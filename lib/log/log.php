<?php
namespace HappyPuppy;

function logsql($message)
{
	$logfile = $_ENV["docroot"]."apps/".$_ENV["app"]->name."/log/sql.log";
	@file_put_contents($logfile,date('c')."\n".$message."\n",FILE_APPEND);
}
<?php
namespace HappyPuppy;

function logsql($message)
{
	$logdir = $_ENV["docroot"]."logs/".$_ENV["app"]->name."/";
	if (!file_exists($logdir))
	{
		mkdir($logdir);
	}
	$logfile = $logdir."sql.log";
	if (!file_exists($logfile))
	{
		touch($logfile);
	}
	@file_put_contents($logfile,date('c')."\n".$message."\n",FILE_APPEND);
}

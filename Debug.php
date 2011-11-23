<?php
namespace HappyPuppy;
class Debug // modified from stack overflow
{
	private static $sqls;

	public static function sql($sql, $time)
	{
		logsql($sql);
		if(!is_array(self::$sqls))
		{
			self::$sqls = array();
		}

		$call = debug_backtrace(false);
		$happy_puppy_call = true;
		$x = 0;
		foreach($call as $crumb)
		{
			$class = ''; if (isset($crumb['class'])){ $class = $crumb['class']; }
			if (strcasecmp('HappyPuppy\\', substr($class, 0, strlen('HappyPuppy\\'))) != 0)
			{
				$happy_puppy_call = false;
				break;
			}
			$x++;
		}
		$data = array();
		$data['line'] = "";
		if (isset($call[$x]['line'])){ $data['line'] = $call[$x]['line']; }
		if (strcasecmp($data['line'],'') == 0)
		{
			$data['line'] = $call[$x-1]['line'];
		}
		$data['function'] = $call[$x]['function'];
		if (strcasecmp($data['function'],'') == 0)
		{
			$data['function'] = $call[$x-1]['function'];
		}
		$data['file'] = "";
		if (isset($call[$x]['file'])){ $data['file'] = $call[$x]['file']; }
		if (strcasecmp($data['file'],'') == 0)
		{
			$data['file'] = $call[$x-1]['file'];
		}
		if (strcasecmp($data['file'],'') != 0)
		{
			if (strcasecmp($data['file'],'HappyPuppy') != 0)
			{
				$pos = strpos($data['file'], 'HappyPuppy') + strlen('HappyPuppy') + 1;
				$data['file'] = substr($data['file'], $pos);
			}
		}
		if (strrpos($data['file'], 'happypuppy\\render\\php\\phpRender.php') === strlen($data['file'])-strlen('happypuppy\\render\\php\\phpRender.php'))
		{
			$data['file'] = $call[$x]['args'][0];
			$pos = strpos($data['file'], 'apps');
			$data['file'] = substr($data['file'], $pos);
			$data['function'] = "None";
			$data['line'] = $call[$x-1]['line'];
		}
		$data['class'] = '';
		if (isset($call[$x]['class'])){ $data['class'] = $call[$x]['class']; }
		if (strcasecmp($data['class'],'') == 0)
		{
			$data['class'] = $call[$x-1]['class'];
		}
		$data['sql'] = $sql;
		$data['time'] = $time;
		array_push(self::$sqls, $data);
	}

	public static function getSQL()
	{
		$out = '';
		$total_time = 0;
		if(!is_array(self::$sqls))
		{
			return "No SQL run to generate this page";
		}
		foreach(self::$sqls as $data)
		{
			$out .= "<strong>".$data['sql']."</strong>\n";
			$out .= "Called from file <strong>".$data['file']."</strong> in function <strong>".$data['function']."</strong> on line <strong>".$data['line']."</strong>\n";
			//$out .= "Class: ".$data['class']."\n";
			$out .= "Call took ".$data['time']." seconds\n";
			$total_time = $total_time + $data['time'];
			$out .= "\n";
		}
		$out .= count(self::$sqls)." SQL Call(s) took ".$total_time." seconds\n";
		$out .= "\n";
		return $out;
	}
}
?>

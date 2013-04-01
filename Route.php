<?php
	namespace HappyPuppy; // Woof!
	require_once('Controller.php');
	require_once('ResourceController.php');
	class Route
	{
		var $customRouteString;
		var $omit_app = false;
		var $omit_controller = false;
		var $omit_action = false;

		var $app;
		var $controller;
		var $action;
		var $params;
		var $method = 'GET';

		var $before = array();
		function __construct($app, $controller, $action, $params = array(), $method = 'GET')
		{
			$this->app = $app;
			$this->controller = $controller;
			$this->action = $action;
			$this->method = $method;
			$this->params = $params;
		}
		function GetRouteString()
		{
			if ($this->customRouteString != ''){ return $this->GetCustomRouteString(); }
			else { return $this->GetDefaultRouteString(); }
		}
		function GetRouteParts()
		{
			$parts = array();
			$routestring = $this->GetRouteString();
			if ($routestring == '/'){ return array('/'); }
			$tok = strtok($routestring, "/");
			while ($tok !== false)
			{
				$dot = strpos($tok, ".");
				if ($dot){
					$tok = substr($tok, 0, $dot);
				}
				$parts[] = $tok;
				$tok = strtok("/");
			}
			return $parts;
		}
		public static function GetRespondsTo($url)
		{
			$tok = strtok($url, "/");
			$parts = array();
			while ($tok !== false)
			{
				$parts[] = $tok;
				$tok = strtok("/");
			}
			$lastpart = end($parts);
			$dot = strpos($lastpart, ".");
			if (!$dot){ return '';}
			return substr($lastpart, $dot + 1);
		}
		function PHPAction()
		{
			$phpaction = $this->action;
			if (function_exists($phpaction)) { $phpaction = "_".$phpaction; }
			if ($phpaction == 'list') { $phpaction = '_list'; }
			if ($phpaction == 'new') { $phpaction = '_new'; }
			if ($phpaction == '') { $phpaction = 'index'; }
			return $phpaction;
		}
		public static function NonPHPAction($phpaction)
		{
			$first = substr($phpaction, 0, 1);
			if (strcmp($first, '_') == 0)
			{
				return substr($phpaction, 1);
			}
			else
			{
				return $phpaction;
			}
		}
		function GetParameters($url)
		{
			$routeparts = $this->GetRouteParts();
			$url_parts = array();
			$tok = strtok($url, "/");
			while ($tok !== false)
			{
				$dot = strpos($tok, '.');
				if ($dot){
					$tok = substr($tok, 0, $dot);
				}
				$url_parts[] = $tok;
				$tok = strtok("/");
			}
			$params = array();
			for($x = 0; $x < count($url_parts); $x++)
			{
				if (substr($routeparts[$x], 0, 1) == '$')
				{
					$params[] = $url_parts[$x];
				}
			}
			return $params;
		}
		function appFilename()
		{
			return $_ENV["docroot"].'apps/'.$this->app.'/Application.php';
		}
		function controllerFilename()
		{
			return $_ENV["docroot"].'apps/'.$this->app.'/controllers/'.$this->controllerClassname().".php";
		}
		function controllerClassname()
		{
			return ucwords($this->controller)."Controller";
		}
		private function GetCustomRouteString()
		{
			$routeString = '';
			if (!$this->omit_app)
			{
				$routeString .= '/'.$this->app;
			}
			$routeString .= $this->customRouteString;
			return $routeString;
		}
		private function GetDefaultRouteString()
		{
			$routeString = '/';
			if (!$this->omit_app)
			{
				$routeString .= $this->app.'/';
			}
			if (!$this->omit_controller)
			{
				$routeString .= $this->controller.'/';
			}
			if (!$this->omit_action)
			{
				$routeString .= $this->action.'/';
			}
  			foreach($this->params as $param)
  			{
				$routeString .= '$'.$param.'/';
  			}
			$routeStringEnd = substr($routeString, strlen($routeString) - 1);
			if ($routeStringEnd == '/' && $routeString != '/')
			{
				$routeString = substr($routeString, 0, strlen($routeString) - 1);
			}
			return $routeString;
		}
		function debugInfo($url)
		{
			$out = '';
			$out .= '<strong>Route Path:</strong> '.$this->GetRouteString()."\n";
			$appFilename = $this->appFilename();
			if (strcasecmp($appFilename,'') != 0)
			{
				if (strcasecmp($appFilename,'HappyPuppy') != 0)
				{
					$pos = strpos($appFilename, 'HappyPuppy') + strlen('HappyPuppy') + 1;
					$appFilename = substr($appFilename, $pos);
				}
			}
			$out .= '<strong>App:</strong> '.$this->app.' <strong>File:</strong> '.$appFilename."\n";
			$controllerFilename = $this->controllerFilename();
			if (strcasecmp($controllerFilename,'') != 0)
			{
				if (strcasecmp($controllerFilename,'HappyPuppy') != 0)
				{
					$pos = strpos($controllerFilename, 'HappyPuppy') + strlen('HappyPuppy') + 1;
					$controllerFilename = substr($controllerFilename, $pos);
				}
			}
			$out .= '<strong>Controller:</strong> '.$this->controller.' <strong>Class:</strong> '.$this->controllerClassname().' <strong>File:</strong> '.$controllerFilename."\n";
			if (strcasecmp($this->action, $this->PHPAction()) == 0)
			{
				$out .= '<strong>Action:</strong> '.$this->action."\n";
			}
			else
			{
				$out .= '<strong>Action:</strong> '.$this->action.' (Calls '.$this->PHPAction().' instead) '."\n";
			}
			if (count($this->params) > 0)
			{
				$args = $this->GetParameters($url);
				$out .= '<strong>Params:</strong> ';
				$x = 0;
				foreach($this->params as $param)
				{
					$out .= $param."=".$args[$x].", ";
					$x++;
				}
				$out = substr($out, 0, strlen($out) - 2);
				$out .= "\n";
			}
			if (strcasecmp($this->customRouteString, '') != 0)
			{
				$out .= '<strong>Custom Route String</strong> '.$this->customRouteString."\n";
			}
			$out .= "\n";
			return $out;
		}
	}
?>

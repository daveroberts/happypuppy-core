<?php
	namespace HappyPuppy;
	require_once('Controller.php');
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
		var $responds_to = 'GET';
		function __construct($app, $controller, $action, $params = array(), $responds_to = 'GET')
		{
			$this->app = strtolower($app);
			$this->controller = strtolower($controller);
			$this->action = strtolower($action);
			$this->responds_to = $responds_to;
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
			$parts = split('[/]', $url);
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
			return $_ENV["docroot"].'apps/'.$this->app.'/'.$this->appClassname().'.php';
		}
		function appClassname()
		{
			return $this->app.'Application';
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
	}
?>

<?php
	namespace HappyPuppy;
	class RouteTree
	{
		var $route_tree;
		function __construct()
		{
			$this->route_tree = array();
			$this->GetRoutes();
		}
		function GetRoutes()
		{
			$apps;
			if ($_ENV['config']['env'] == Environment::DEV){ $apps = $_ENV["config"]["all_apps"]; }
			else { $apps = $_ENV["config"]["prod_apps"]; }
			foreach($apps as $app)
			{
				$filepath = $_ENV['docroot'].'apps/'.$app.'/'.$app.'Application.php';
				if (!is_file($filepath)){ throw new Exception("No app file found for $app at $filepath"); }
				require_once($filepath);
				$app_classname = $app.'\\'.$app.'Application';
				$app_instance = new $app_classname($app);
				// can't call init here.  What if a database reference is created?  That should be global
				//$app_instance->__init();
				$app_instance->AddRoutesToList($this);
			}
		}
		function AddRoute($route)
		{
			if ($route->omit_controller && $route->omit_action)
			{
				$asdf = 000;
			}
			$parts = $route->GetRouteParts();
			$num_parts = count($parts);
			$current = &$this->route_tree;
			for($x = 0;$x < $num_parts; $x++)
			{
				$part = strtolower($parts[$x]);
				if (strcmp(substr($part, 0, 1), '_') == 0)
				{
					$part = substr($part, 1);
				}
				if (!is_array($current[$part]))
				{
					$current[$part] = array();
				}
				$current = &$current[$part];
			}
			$current[] = $route;
		}
		function findRoute($url)
		{
			$matchingSubtrees = array(&$this->route_tree);
			$matchingRoutes = array();
			$urlparts = split('[/]', $url);
			if ($url == ''){ $urlparts = array('/'); }
			for($p = 0; $p < count($urlparts); $p++)
			{
				$urlpart = $urlparts[$p];
				$dot = strpos($urlpart, '.');
				if ($dot){
					$urlpart = substr($urlpart, 0, $dot);
				}
				$nextLevelSubtrees = array();
				foreach($matchingSubtrees as $tree)
				{
					if (!is_array($tree)){ continue; }
					foreach($tree as $key=>$val)
					{
						if (is_array($val))
						{
							if (strcasecmp($urlpart, $key) == 0 || substr($key, 0, 1) == '$')
							{
								$nextLevelSubtrees[] = $tree[$key];
							}
						}
					}
				}
				$matchingSubtrees = $nextLevelSubtrees;
			}
			foreach($matchingSubtrees as $matchingSubtree)
			{
				foreach($matchingSubtree as $rts)
				{
					if (!is_array($rts))
					{
						array_push($matchingRoutes, $rts);
					}
				}
			}
			$num_routes = count($matchingRoutes);
			if ($num_routes > 1){ throw new \Exception("Multiple Routes match"); }
			if ($num_routes == 0){ return null; }
			return $matchingRoutes[0];
		}
		function PrettyListOfRoutes()
		{
			$pretty_routes = $this->PrettyListOfRoutesRecur($this->route_tree);
			ksort($pretty_routes);
			foreach ($pretty_routes as $app=>$routes)
			{
				$routestring = array();
				foreach($routes as $key=>$route)
				{
					$routestring[$key] = $route->GetRouteString();
				}
				array_multisort($routestring, $pretty_routes[$app]);
				$x = 0;
			}
			return $pretty_routes;
		}
		function PrettyListOfRoutesRecur($tree)
		{
			$pretty_routes = array();
			foreach($tree as $subtree)
			{
				if (is_array($subtree))
				{
					$subtree_routes = $this->PrettyListOfRoutesRecur($subtree);
					$pretty_routes = array_merge_recursive($pretty_routes, $subtree_routes);
				}
				else
				{
					$route = $subtree;
					if (!array_key_exists($route->app, $pretty_routes))
					{
						$pretty_routes[$route->app] = array();
					}
					$pretty_routes[$route->app][] = $route;
				}
			}
			return $pretty_routes;
		}
	}
?>

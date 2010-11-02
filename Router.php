<?php
	namespace HappyPuppy;
	require_once('Route.php');
	require_once('RouteTree.php');
	require_once('Application.php');
	class Router
	{
		static function URLToRoute($url)
		{
			$routetree = Cache::get("routetree");
			if ($routetree == null || $_ENV['config']['env'] == Environment::DEV)
			{
				$routetree = new RouteTree();
				Cache::set("routetree", $routetree);
			}
			$route = $routetree->findRoute($url);
			return $route;
		}
		static function RunRoute($route, $url)
		{
			Router::LoadApplication($route);
			Router::LoadController($route);
			$_ENV["controller"]->responds_to = Route::GetRespondsTo($url);
			$_ENV["controller"]->run_before_filters($route->action);
			ob_start();
			if (method_exists($_ENV["controller"], $route->PHPAction()))
			{
				$args = $route->GetParameters($url);
				call_user_func_array(array($_ENV["controller"], $route->PHPAction()),$args);
			}
			$out = ob_get_contents();
			ob_end_clean();
			// did the page push out text?
			if ($_ENV["controller"]->text_only)
			{
				print($out);
				exit();
			}
			// if not, we need to collect class variables, package them and process here
			// find out which render engine the page load wants to use:
			$render_engine = $_ENV["config"]["render_engine"];
			if ($_ENV["app"]->render_engine != null){ $render_engine = $_ENV["app"]->render_engine; }
			if ($_ENV["controller"]->render_engine != null){ $render_engine = $_ENV["controller"]->render_engine; }
			require_once('render/iRender.php');
			require_once('render/'.$render_engine.'/'.$render_engine.'Render.php');
			$render_engine_classname = $render_engine.'Render';
			$render_engine_instance = new $render_engine_classname();
			$out = $render_engine_instance->process($_ENV["controller"], $route->controller, $route->action);
			print($out);
		}
		private static function LoadApplication($route)
		{
			require_once($route->appFilename());
			if (!class_exists($route->app.'\\'.$route->appClassname()))
			{
				if ($_ENV['config']['env'] != Environment::DEV)
				{
					not_found();
				}
				else
				{
					print("Can't load application: ".$route->app.".  Make sure you have a folder named ".$route->app." containing a file named ".$route->appClassname().".php containing a class named ".$route->appClassname());
					exit();
				}
			}
			$app_classname = $route->app.'\\'.$route->appClassname();
			$_ENV["app"] = new $app_classname($route->app);
			// load the database
			DB::LoadDB($route->app);
			$_ENV["app"]->__baseinit();
			if(method_exists($_ENV["app"], "__init")){
				$_ENV["app"]->__init();
			}
		}
		private static function LoadController($route)
		{
			require_once($route->controllerFilename());
			if (!class_exists($route->app.'\\'.$route->controllerClassname())){ if ($_ENV['config']['env'] != Environment::DEV){not_found();} else{ print("Can't find controller: ".$route->controllerClassname().'.  Make sure Happy Puppy can see your controllers.  Happy Puppy by default loads DOCROOT/apps/$app/controllers/$controller'); exit(); } }
			$controller_classname = $route->app.'\\'.$route->controllerClassname();
			$_ENV["controller"] = new $controller_classname($_ENV["app"], substr($route->controllerClassname(), 0, strlen($route->controllerClassname())-10));
			$_ENV["controller"]->__baseinit();
			if(method_exists($_ENV["controller"], "__init")){
				$_ENV["controller"]->__init();
			}
		}
	}
	
?>

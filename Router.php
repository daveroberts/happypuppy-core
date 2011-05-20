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
			$_ENV["action"] = $route->action;
			$_ENV["controller"]->responds_to = Route::GetRespondsTo($url);
			$_ENV["controller"]->runFilters($route->action, $route->before);
			//$_ENV["controller"]->runBeforeFilters($route->action);
			ob_start();
			if (method_exists($_ENV["controller"], $route->PHPAction()))
			{
				$args = $route->GetParameters($url);
				call_user_func_array(array($_ENV["controller"], $route->PHPAction()),$args);
			}
			else
			{
				$refl = new \ReflectionClass($_ENV["controller"]);
				throw new \Exception("No method named ".$route->PHPAction()." found in the controller ".$refl->getShortName());
			}
			$out = ob_get_contents();
			ob_end_clean();
			
			$render_engine_instance = null;
			if ($_ENV["controller"]->processView())
			{
				// if not, we need to collect class variables, package them and process here
				// find out which render engine the page load wants to use:
				$render_engine = $_ENV["config"]["render_engine"];
				if ($_ENV["app"]->render_engine != null){ $render_engine = $_ENV["app"]->render_engine; }
				if ($_ENV["controller"]->render_engine != null){ $render_engine = $_ENV["controller"]->render_engine; }
				$render_engine_classname = $render_engine.'Render';
				$render_engine_instance = new $render_engine_classname();
				$out = $render_engine_instance->process($_ENV["controller"], $route->controller, $route->action);
			}
			
			if (isset($_GET["__hpinfo"]))
			{
				Router::showDebugInfo($route, $url, $render_engine_instance);
			}
			else
			{
				// did the page push out text?
				if ($_ENV["controller"]->text_only){
					header("Content-type: text/plain");
				}
				if ($_ENV["controller"]->xml_only){
					header("Content-type: text/xml");
				}
				if ($_ENV["controller"]->json_only){
					header("Content-type: application/json");
				}
				print($out);
			}
		}
		private static function LoadApplication($route)
		{
			require_once($route->appFilename());
			if (!class_exists($route->app.'\\Application'))
			{
				if ($_ENV['config']['env'] != Environment::DEV)
				{
					not_found();
				}
				else
				{
					throw new \Exception("Can't load application: ".$route->app.".  Make sure you have a folder named ".$route->app." containing a file named Application.php containing a class named Application");
				}
			}
			$app_classname = $route->app.'\\Application';
			$_ENV["app"] = new $app_classname($route->app);
			// load the database
			DBConnection::SetDB($route->app);
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
		private static function showDebugInfo($route, $url, $render_engine_instance)
		{
			$debug_info = $route->debugInfo($url);
			$sql_info = Debug::getSQL();
			$flat_output = $_ENV["controller"]->debugInfo();
			if ($render_engine_instance == null)
			{
				$view_file_info = "No render engine called (probably direct output to text, xml or json)";
			}
			else
			{
				$view_file_info = $render_engine_instance->debugInfo($_ENV["controller"], $route->controller, $route->action);
			}
			require('/apps/hptools/views/Service/debuginfo.php');
		}
	}
	
?>

<?

namespace HappyPuppy;
class Controller
{
	var $name = '';
	var $app_instance = '';
	var $title = '';
	function __construct($app_instance, $name){
		$this->app_instance = $app_instance;
		$this->name = $name;
		$this->title = $app_instance->title;
	}
	public function __baseinit(){
		
	}
	// render variables
	var $render_engine = "";
	var $layout = true;
	var $layout_template;
	var $view_template = '';
	var $view_header = '';
	var $text_only = false;
	var $responds_to = '';
	// These are helper methods you may call
	public function render_text($text = null){
		if ($text != null){ echo $text; }
		$this->text_only = true;
	}
	public function render_no_layout($view_template = null)
	{
		$this->layout = false;
		if ($view_template != null){ $this->view_template = $view_template; }
	}
	public function render($layout_template = null, $view_template = null)
	{
		if ($layout_template != null){ $this->layout_template = $layout_template; }
		if ($view_template != null){ $this->view_template = $view_template; }
	}
	public function redirect_to($app_url)
	{
		$this->redirect_to_raw_url(\rawurl_from_appurl($app_url));
	}
	public function redirect_to_action($action)
	{
		$this->redirect_to_raw_url(\rawurl_from_action($action));
	}
	public function redirect_to_raw_url($raw_url)
	{
		header("Location: {$raw_url}");
		exit();
	}
	public function run_before_filters($action)
	{
		$this->run_filters($action, $this->before, $this->not_before);
	}
	private function run_filters($action, $filters, $unfiltered)
	{
		// Determine which filters to run
		$to_run = array();
		if ($filters != null)
		{
			foreach($filters as $filtered_action=>$filtered_methods)
			{
				if ($filtered_methods == "*"){ array_push($to_run, $filtered_action); continue; }
				foreach($filtered_methods as $method)
				{
					if ($method == $action){ array_push($to_run, $filtered_action); continue; }
				}
			}
		}
		if ($unfiltered != null)
		{
			foreach($unfiltered as $filtered_action=>$filtered_methods)
			{
				foreach($filtered_methods as $method)
				{
					if ($method == $action){ unset($to_run[array_search($method, $to_run)]); continue; }
				}
			}
		}
		foreach($to_run as $filtered_action)
		{
			call_user_func(array($this , $filtered_action));
		}
	}
	function AddRoutesToList($route_list)
	{
		// iterate over controller methods, add to router
		$refl = new \ReflectionClass($this); 
  		$methods = $refl->getMethods();
  		foreach($methods as $method)
  		{
  			if ($method->class == 'HappyPuppy\Controller'){ continue; }
  			if ($method->name == '__init'){ continue; }
  			if ($method->name == 'defaultAction'){ continue; }
  			// Controller = 10
  			$controller_name = substr($method->class, 0, strlen($method->class)-10);
  			$slashpos = strpos($controller_name, '\\');
  			if ($slashpos != -1)
  			{
				$controller_name = substr($controller_name, $slashpos + 1);
  			}
  			$rm = new \ReflectionMethod($method->class, $method->name);
  			$params = array();
  			foreach($rm->getParameters() as $p)
  			{
				$params[] = $p->name;
  			}
  			$routes = array();
  			// do we have custom routes?
  			$docstring = $rm->getDocComment();
  			$custom_routes = Controller::GetCustomRoutes($docstring);
  			$method_name = $method->name;
  			$method_name = Route::NonPHPAction($method_name);
  			
  			// start populating routes
  			if (!empty($custom_routes))
  			{
				foreach($custom_routes as $custom_route)
				{
					$route = new Route($this->app_instance->name, $controller_name, $method_name, $params, $custom_route[0]);
					$route->customRouteString = $custom_route[1];
					$routes[] = $route;
				}
  			}
  			else
  			{
				$routes[] = new Route($this->app_instance->name, $controller_name, $method_name, $params, 'GET');
				// if this is the default action, add a route
				$defaultAction = '';
				if (method_exists($this, "defaultAction")){
					$defaultAction = $this->defaultAction();
				}
				if (strcasecmp($method_name, $defaultAction) == 0)
  				{
					$route = new Route($this->app_instance->name, $controller_name, $method_name, $params);
					$route->omit_action = true;
					$routes[] = $route;
					// if this is also the default controller, add yet another route
					$defaultController = '';
					if (method_exists($this->app_instance, "defaultController")){
						$defaultController = $this->app_instance->defaultController();
					}
					if (strcasecmp($defaultController, $controller_name) == 0)
					{
						$route = new Route($this->app_instance->name, $controller_name, $method_name, $params);
						$route->omit_action = true;
						$route->omit_controller = true;
						$routes[] = $route;
					}
  				}
			}
  			foreach($routes as $route)
  			{
  				if (strcasecmp($_ENV["config"]["default_app"], $this->app_instance->name) == 0)
				{
					$route->omit_app = true;
				}
				$route_list->AddRoute($route);
  			}
  		}
	}
	private static function GetCustomRoutes($docstring)
	{
		$return = array();
		foreach(Annotation::parseDocstring($docstring) as $annotation=>$vals)
		{
			if ($annotation == 'Route')
			{
				foreach($vals as $routeinfo)
				{
					$return[] = array($routeinfo[0], $routeinfo[1]);
				}
			}
		}
		return $return;
	}
}
?>

<?php

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
		$vars = get_object_vars($this->app_instance);
		if (count($vars)>0)
		{
			foreach($vars as $key => $value)
			{
				if (is_equal($key, "name")){ continue; }
				if (is_equal($key, "title")){ continue; }
				$this->$key = $value;
			}
		}
	}
	// render variables
	var $render_engine = "";
	var $layout = true;
	var $layout_template;
	var $view_template = '';
	var $view_header = '';
	var $text_only = false;
	var $xml_only = false;
	var $json_only = false;
	var $responds_to = '';
	
	private $__before = null;	
	private $default_action = null;
	private $__name = null;
	
	// These are helper methods you may call
	public function renderText($text = null){
		if ($text != null){ echo $text; }
		$this->text_only = true;
	}
	public function processView()
	{
		return (!$this->text_only && 
			!$this->xml_only &&
			!$this->json_only);
	}
	public function renderXML($xml = null){
		if ($xml != null){ echo $xml; }
		$this->xml_only = true;
	}
	public function renderJSON($json = null){
		if ($json != null){ echo $json; }
		$this->json_only = true;
	}
	public function renderNoLayout($view_template = null)
	{
		$this->layout = false;
		if ($view_template != null){ $this->view_template = $view_template; }
	}
	public function render($layout_template = null, $view_template = null)
	{
		if ($layout_template != null){ $this->layout_template = $layout_template; }
		if ($view_template != null){ $this->view_template = $view_template; }
	}
	public function redirectTo($location)
	{
		header("Location: ".url_for($location));
		exit();
	}
	public function runFilters($action, $methods)
	{
		foreach($methods as $method)
		{
			if (strpos($method, "::"))
			{
				$refl = new \ReflectionClass($this);
				call_user_func("\\".$refl->getNamespaceName()."\\".$method);
			}
			else
			{
				call_user_func(array($this, $method));
			}
		}
	}
	function getDefaultAction()
	{
		if ($this->default_action != null){ return $this->default_action; }
		$rc = new \ReflectionClass($this);
		$docstring = $rc->getDocComment();
		$this->default_action = 'index';
		foreach(Annotation::parseDocstring($docstring) as $annotation=>$vals)
		{
			if (is_equal_ignore_case($annotation, 'DefaultAction'))
			{
				$this->default_action = $vals[0][0];
			}
		}
		return $this->default_action;
	}
	function AddRoutesToList($route_list)
	{
		// iterate over controller methods, add to router
		$refl = new \ReflectionClass($this); 
		
		if ($this->app_instance == null)
		{
			throw new \Exception("Your controller ".$refl->getName()." can't declare a constructor.<br />\nInstead, add a function named __init() and place your constructor's code there.");
		}
		
		if ($this->__isResource())
		{
			$master_route = new Route($this->app_instance->name, $this->__name(), '');
			$list_route = clone $master_route;
			$list_route->action = "list";
			$list_route->before = $this->__getBeforeFilters();
			$list_route->customRouteString = '/'.$this->__name();
			$route_list->AddRoute($list_route);
			$show_route = clone $master_route;
			$show_route->action = "show";
			$show_route->before = $this->__getBeforeFilters();
			$show_route->customRouteString = '/'.$this->__name().'/$id';
			$route_list->AddRoute($show_route);
			$create_route = clone $master_route;
			$create_route->action = "create";
			$create_route->method = "POST";
			$create_route->before = $this->__getBeforeFilters();
			$create_route->customRouteString = '/'.$this->__name();
			$route_list->AddRoute($create_route);
			$update_route = clone $master_route;
			$update_route->action = "update";
			$update_route->method = "PUT";
			$update_route->before = $this->__getBeforeFilters();
			$update_route->customRouteString = '/'.$this->__name().'/$id';
			$route_list->AddRoute($update_route);
			$destroy_route = clone $master_route;
			$destroy_route->action = "delete";
			$destroy_route->method = "DELETE";
			$destroy_route->before = $this->__getBeforeFilters();
			$destroy_route->customRouteString = '/'.$this->__name().'/$id';
			$route_list->AddRoute($destroy_route);
		}
		
  		$methods = $refl->getMethods();
  		foreach($methods as $method)
  		{
  			if ($method->class == 'HappyPuppy\Controller'){ continue; }
  			if ($method->name == '__init'){ continue; }
  			if ($method->name == 'defaultAction'){ continue; }
			if ($method->name == '__construct'){ continue; }
			
			if (!$this->isRoute($method)){ continue; }

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

			$before_filters = $this->getActionBeforeFilters($method);
  			
			$master_route = new Route($this->app_instance->name, $controller_name, $method_name, $params, 'GET');
			$master_route->before = $before_filters;
			
  			// start populating routes
  			if (!empty($custom_routes))
  			{
				foreach($custom_routes as $custom_route)
				{
					$route = clone $master_route;
					$route->responds_to = $custom_route[0];
					$route->customRouteString = $custom_route[1];
					$routes[] = $route;
				}
  			}
  			else
  			{
				$route = clone $master_route;
				if (!$this->__isResource())
				{
					$routes[] = $route;
				}
				// if this is the default action, add a route
				$defaultAction = $this->getDefaultAction();
				if (is_equal($method_name, $defaultAction))
  				{
					$route = clone $master_route;
					$route->omit_action = true;
					$routes[] = $route;
					// if this is also the default controller, add yet another route
					$defaultController = $this->app_instance->getDefaultController();
					if (is_equal($defaultController, $controller_name))
					{
						$route = clone $master_route;
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
	private function __name()
	{
		if ($this->__name != null){ return $this->__name; }
		$refl = new \ReflectionClass($this); 
		$controller_name = substr($refl->name, 0, strlen($refl->name)-10);
		$slashpos = strpos($controller_name, '\\');
		if ($slashpos != -1)
		{
			$controller_name = substr($controller_name, $slashpos + 1);
		}
		$controller_name = strtolower($controller_name);
		$this->__name = $controller_name;
		return $this->__name;
	}
	protected function __isResource() { return false; }
	private static function GetCustomRoutes($docstring)
	{
		$return = array();
		foreach(Annotation::parseDocstring($docstring) as $annotation=>$vals)
		{
			if (strcasecmp($annotation, 'Route') == 0)
			{
				foreach($vals as $routeinfo)
				{
					$return[] = array($routeinfo[0], $routeinfo[1]);
				}
			}
		}
		return $return;
	}
	private function __getBeforeFilters()
	{
		if ($this->__before != null){ return $this->__before; }
		$this->__before = array();
		$refl = new \ReflectionClass($this);
		$docstring = $refl->getDocComment();
		foreach(Annotation::parseDocstring($docstring) as $annotation=>$vals)
		{
			if (strcasecmp($annotation, 'Before') == 0)
			{
				foreach($vals as $methods)
				{
					foreach($methods as $method)
					{
						$this->__before[] = $method;
					}
				}
			}
		}
		foreach(Annotation::parseDocstring($docstring) as $annotation=>$vals)
		{
			if (strcasecmp($annotation, 'NotBefore') == 0)
			{
				foreach($vals as $methods)
				{
					foreach($methods as $not_before_method)
					{
						foreach($this->__before as $k=>$b_method)
						{
							if (is_equal($b_method, $not_before_method))
							{
								unset($this->__before[$k]);
								break;
							}
						}
					}
				}
			}
		}
		return $this->__before;
	}
	private function getActionBeforeFilters($method)
	{
		$before = $this->__getBeforeFilters();
		$rm = new \ReflectionMethod($method->class, $method->name);
		$docstring = $rm->getDocComment();
		foreach(Annotation::parseDocstring($docstring) as $annotation=>$vals)
		{
			if (strcasecmp($annotation, 'Before') == 0)
			{
				foreach($vals as $methods)
				{
					foreach($methods as $method)
					{
						$before[] = $method;
					}
				}
			}
		}
		foreach(Annotation::parseDocstring($docstring) as $annotation=>$vals)
		{
			if (strcasecmp($annotation, 'NotBefore') == 0)
			{
				foreach($vals as $methods)
				{
					foreach($methods as $not_before_method)
					{
						foreach($before as $k=>$b_method)
						{
							if (is_equal($b_method, $not_before_method))
							{
								unset($before[$k]);
								break;
							}
						}
					}
				}
			}
		}
		return $before;
	}
	private function isRoute($method)
	{
		$before = $this->__getBeforeFilters();
		$rm = new \ReflectionMethod($method->class, $method->name);
		$docstring = $rm->getDocComment();
		foreach(Annotation::parseDocstring($docstring) as $annotation=>$vals)
		{
			if (strcasecmp($annotation, 'NotRoute') == 0)
			{
				return false;
			}
		}
		return true;
	}
	public function debugInfo()
	{
		$out = '';
		if ($this->text_only || 
				$this->xml_only ||
				$this->json_only)
		{
				if ($this->text_only) {
					$out .= "Text output only\n";
				} else if ($this->xml_only) {
					$out .= "XML output\n";
				} else {
					$out .= "JSON output\n";
				}
		} else {
		}
		return $out;
	}
}
?>

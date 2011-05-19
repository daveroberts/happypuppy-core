<?php

class PhpRender implements iRender
{
	public function process($controller_obj, $controller_name, $action)
	{
		if ($action == null || $action == ''){ $action = 'index'; }
		$view_template = $this->getViewTemplate($controller_obj, $controller_name, $action);
		if (!file_exists($view_template) AND !__DEBUG__){ not_found(); }
		if ($controller_obj->layout)
		{
			$content = $this->file_with_obj($view_template, $controller_obj);
			
			$head_template = $this->getHeadTemplate($controller_name, $action);
			$head = "";
			if (file_exists($head_template))
			{
				$head = $this->file_with_obj($head_template, $controller_obj);
			}
			$controller_obj->content = $content;
			$controller_obj->head = $head;
			$layout_template = $this->getLayoutTemplate($controller_obj);
			return $this->file_with_obj($layout_template, $controller_obj);
		}
		else
		{
			return $this->file_with_obj($view_template, $controller_obj);
		}
	}
	private function getViewTemplate($controller_obj, $controller_name, $action)
	{
		$view_template = $controller_obj->view_template;
		if ($view_template == '')
		{
			$view_template = $_ENV["app"]->root().'views/'.$controller_name.'/'.$action.'.php';
		}
		else
		{
			$view_template = $_ENV["app"]->root().'views/'.$view_template.'.php';
		}
		return $view_template;
	}
	private function getHeadTemplate($controller_name, $action)
	{
		$head_template = $_ENV["app"]->root().'views/'.$controller_name.'/'.$action.'.head.php';
		return $head_template;
	}
	private function getLayoutTemplate($controller_obj)
	{
		$layout_template = $controller_obj->layout_template;
		if ($layout_template == "")
		{
			$layout_template = $_ENV["app"]->root().'views/layout.php';
		}
		else
		{
			$layout_template = $_ENV["app"]->root().'views/'.$layout_template.'.php';
		}
		return $layout_template;
	}
	public function file_with_arr($file, $arr)
	{
		if (!file_exists($file)) { throw new Exception('View ['.$file.'] Not Found'); }
		foreach($arr as $key=>$value)
		{
			$$key = $value;
		}
		ob_start();
		require($file);
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}
	public function file_with_obj($file, $obj)
	{
		if (!file_exists($file)) { throw new Exception('View ['.$file.'] Not Found'); }
		$vars = get_object_vars($obj);
		if (count($vars)>0)
		{
			foreach($vars as $key => $value)
			{
				$$key = $value;
			}
		}
		ob_start();
		require($file);
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}
	public function file_with_var($file, $varname, $var)
	{
		if (!file_exists($file)) { throw new Exception('View ['.$file.'] Not Found'); }
		ob_start();
		$$varname = $var;
		require($file);
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}
	public function file($file)
	{
		return $this->file_with_var($file, null, null);
	}
	public function debugInfo($controller_obj, $controller_name, $action)
	{
		$out = '';
		$layout_template = $this->getLayoutTemplate($controller_obj);
		$pos = strpos($layout_template, 'apps');
		$layout_template = substr($layout_template, $pos);
		$out .= "Layout Template: ".$layout_template."\n";
		$head_template = $this->getHeadTemplate($controller_name, $action);
		$pos = strpos($head_template, 'apps');
		$head_template = substr($head_template, $pos);
		$out .= "Head Template: ".$head_template."\n";
		$view_template = $this->getViewTemplate($controller_obj, $controller_name, $action);
		$pos = strpos($view_template, 'apps');
		$view_template = substr($view_template, $pos);
		$out .= "View Tempalte: ".$view_template."\n";
		return $out;
	}
	public static function render_arr($file, $vars)
	{
		$file = $_ENV["app"]->root().'views/'.$file.'.php';
		$render = new PhpRender();
		return $render->file_with_arr($file, $vars);
	}
	public static function render($file, $varname, $var)
	{
		$file = $_ENV["app"]->root().'views/'.$file.'.php';
		$render = new PhpRender();
		return $render->file_with_var($file, $varname, $var);
	}
}

?>
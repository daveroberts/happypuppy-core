<?

class PhpRender implements iRender
{
	public function process($controller_obj, $controller_name, $action)
	{
		if ($action == null || $action == ''){ $action = 'index'; }
		$view_template = $controller_obj->view_template;
		if ($view_template == '')
		{
			$view_template = $_ENV["app"]->root().'views/'.$controller_name.'/'.$action.'.php';
		}
		else
		{
			$view_template = $_ENV["app"]->root().'views/'.$view_template.'.php';
		}
		if (!file_exists($view_template) AND !__DEBUG__){ not_found(); }
		if ($controller_obj->layout)
		{
			$content = $this->file_with_obj($view_template, $controller_obj);
			$head_template = $_ENV["app"]->root().'views/'.$controller_name.'/'.$action.'.head.php';
			$head = "";
			if (file_exists($head_template))
			{
				$head = $this->file_with_obj($head_template, $controller_obj);
			}
			$controller_obj->content = $content;
			$controller_obj->head = $head;
			$layout_template = $controller_obj->layout_template;
			if ($layout_template == "")
			{
				$layout_template = $_ENV["app"]->root().'views/layout.php';
			}
			else
			{
				$layout_template = $_ENV["app"]->root().'views/'.$layout_template.'.php';
			}
			return $this->file_with_obj($layout_template, $controller_obj);
		}
		else
		{
			return $this->file_with_obj($view_template, $controller_obj);
		}
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
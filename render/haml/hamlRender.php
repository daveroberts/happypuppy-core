<?

require_once('pHAML.php');

class HamlRender implements iRender
{
	public function process($controller_obj, $controller_name, $action)
	{
		if ($action == null || $action == ''){ $action = 'index'; }
		$view_template = $controller_obj->view_template;
		if ($view_template == '') { $view_template = $_ENV["app"]->root().'views/'.$controller_name.'/'.$action.'.haml'; }
		if (!file_exists($view_template) AND !__DEBUG__){ not_found(); }
		if ($controller_obj->layout)
		{
			$content = $this->file_with_obj($view_template, $controller_obj);
			print("Content: ".$content); exit();
			$head_template = $_ENV["app"]->root().'views/'.$controller_name.'/'.$action.'.head.haml';
			$head = "";
			if (file_exists($head_template))
			{
				$head = $this->file_with_obj($head_template, $controller_obj);
			}
			$controller_obj->content = $content;
			$controller_obj->head = $head;
			$layout_template = $controller_obj->layout_template;
			if ($layout_template == ""){ $layout_template = $_ENV["app"]->root().'views/layout.haml'; }
			return $this->file_with_obj($layout_template, $controller_obj);
		}
		else
		{
		  return $this->file_with_obj($view_template, $this);
		}
	}
	public function file_with_arr($file, $arr)
	{
		if (!file_exists($file)) { throw new Exception('View ['.$file.'] Not Found'); }
		$pHAML = new pHAML();
		$out = $pHAML->render($file,$arr);
		return $out;
	}
	public function file_with_obj($file, $obj)
	{
		if (!file_exists($file)) { throw new Exception('View ['.$file.'] Not Found'); }
		$vars = get_object_vars($obj);
		$arr = array();
		foreach($vars as $key => $value)
		{
			$arr[$key] = $value;
		}
		return $this->file_with_arr($file, $arr);
	}
	public function file_with_var($file, $varname, $var)
	{
		if (!file_exists($file)) { throw new Exception('View ['.$file.'] Not Found'); }
		ob_start();
		$arr = array();
		$arr[$varname] = $var;
		return $this->file_with_arr($file, $arr);
	}
	public function file($file)
	{
		return $this->file_with_var($file, null, null);
	}
}

?>
<?php
	namespace HappyPuppy;
	class Application
	{
		var $name;
		var $title = '';
		private $debug_app = null;
		private $default_controller = null;
		private $before = null;
		function __construct($name)
		{
			$this->name = $name;
		}
		public function __baseinit()
		{
			// include view helpers and models
			$this->include_dir('views/helpers/*.php');
			$this->include_dir('models/*.php');
		}
		public function isDebugApp()
		{
			if ($this->debug_app != null){ return $this->debug_app; }
			$rc = new \ReflectionClass($this);
			$docstring = $rc->getDocComment();
			$this->debug_app = false;
			foreach(Annotation::parseDocstring($docstring) as $annotation=>$vals)
			{
				if (is_equal_ignore_case($annotation, 'DebugApp'))
				{
					$this->debug_app = true;
				}
			}
			return $this->debug_app;
		}
		public function getDefaultController()
		{
			if ($this->default_controller != null){ return $this->default_controller; }
			$rc = new \ReflectionClass($this);
			$docstring = $rc->getDocComment();
			$this->default_controller = '';
			foreach(Annotation::parseDocstring($docstring) as $annotation=>$vals)
			{
				if (is_equal_ignore_case($annotation, 'DefaultController'))
				{
					$this->default_controller = $vals[0][0];
					break;
				}
			}
			return $this->default_controller;
		}
		public function AddRoutesToList($route_tree)
		{
			if ($handle = opendir($_ENV['docroot'].'apps/'.$this->name.'/controllers'))
			{
				while (false !== ($file = readdir($handle)))
				{
					if ($file == '.' || $file == '..' || substr($file, strlen($file)-14) != "Controller.php"){ continue; }
					$rel_path = 'apps/'.$this->name.'/controllers/'.$file;
					require_once($_ENV['docroot'].$rel_path);
					// Controller.php = 14 letters
					$controller_class_name = $this->name.'\\'.substr($file, 0, strlen($file)-4);
					if (!class_exists($controller_class_name)){ throw new \Exception("No class named $controller_class_name found in $rel_path"); }
					$controller_instance = new $controller_class_name($this, substr($file, 0, strlen($file) - 14));
					// can't call init here.  This should be at runtime only
					//$controller_instance->__init();
					$controller_instance->AddRoutesToList($route_tree);
				}
				closedir($handle);
			}
		}
		public function root()
		{
			return $_ENV["docroot"].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$this->name.DIRECTORY_SEPARATOR;
		}
		public function include_file($file_relative_from_root)
		{
			include_once($this->root().$file_relative_from_root);
		}
		public function require_file($file_relative_from_root)
		{
			require_once($this->root().$file_relative_from_root);
		}
		public function include_dir($dir_relative_from_root)
		{
			include_dir($this->root().$dir_relative_from_root);
		}
	}
?>

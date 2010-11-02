<?php
	namespace HappyPuppy;
	class Application
	{
		var $name;
		var $customPrefix = '';
		var $title = '';
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
		public function AddRoutesToList($route_tree)
		{
			if ($handle = opendir($_ENV['docroot'].'apps/'.$this->name.'/controllers'))
			{
				while (false !== ($file = readdir($handle)))
				{
					if ($file == '.' || $file == '..' || substr($file, strlen($file)-14) != "Controller.php"){ continue; }
					require_once($_ENV['docroot'].'apps/'.$this->name.'/controllers/'.$file);
					// Controller.php = 14 letters
					$controller_class_name = $this->name.'\\'.substr($file, 0, strlen($file)-4);
					$controller_instance = new $controller_class_name($this, substr($file, 0, strlen($file) - 14));
					// can't call init here.  This should be at runtime only
					//$controller_instance->__init();
					$controller_instance->AddRoutesToList($route_tree);
				}
				closedir($handle);
			}
		}
		public function prefix()
		{
			if ($this->customPrefix != ''){ return $this->customPrefix; }
			return $name;
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

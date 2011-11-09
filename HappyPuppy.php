<?php
namespace HappyPuppy;
require_once($_ENV["docroot"]."config/hpconf.php");
require_once("lib/all.php");
require_once("render/all.php");
require_once("SimpleCache.php");
require_once("Router.php");
require_once("Debug.php");

function run()
{
	HappyPuppy::GetInstance()->init();
	try
	{
		HappyPuppy::GetInstance()->dispatch();
	}
	catch (\Exception $e)
	{
		header('HTTP/1.1 500 Internal Server Error');
		if ($_ENV['config']['env'] == Environment::DEV)
		{
			require($_ENV['docroot'].'happypuppy/error/exception.php');
		}
		else
		{
			require($_ENV['docroot'].$_ENV['config']["static_error_page"]);
		}
		exit();
	}
}

class HappyPuppy
{
	static private $instance = NULL;

	function __construct()
	{
		if (isset($_GET['url']))
			$this->url =trim( $_GET['url'], '/');
		else $this->url = '';
	}
	
	function init()
	{
		if ($_ENV['config']['env'] == Environment::DEV)
		{
			$provider = '\HappyPuppy\SqliteCacheProvider';
			//Cache::reportsTo(new $provider);
		}
	}

	public function dispatch()
	{
		return $this->dispatch_to($this->url);
	}

	/* Process requests and dispatch */
	public function dispatch_to($url)
	{
		// serving static content from php is a nightmare.
		// Not all versions of php can determine mime-type
		// picking the wrong mime type for stylesheets can cause the sheet not to load.
		// leaving this to the web server for now
		
		// if we are displaying info, set the URL here
		$url = $this->setInfoPageURL($url);
		
		$route = Router::URLToRoute($url);
		if ($route != null)
		{
			Router::RunRoute($route, $url);
		}
		else
		{
			$this->dispath_404($url);
		}
	}
	public function dispath_404($url)
	{
		if ($_ENV['config']['env'] == Environment::DEV)
		{
			require($_ENV["docroot"]."happypuppy/error/default404dev.php");
		}
		else
		{
			if (!is_empty($_ENV["docroot"].$_ENV['config']["route_not_found_url"]))
			{
				$url = $_ENV["docroot"].$_ENV['config']["route_not_found_url"];
				// no need to set, this is only used in DEV anyway
				//$url = $this->setInfoPageURL($url);
				
				$route = Router::URLToRoute($url);
				if ($route != null)
				{
					Router::RunRoute($route, $url);
				}
				else
				{
					// The route not found url was also not found
					// default to the route not found page
					require($_ENV["docroot"].$_ENV['config']["route_not_found_page"]);
				}
			}
			else
			{
				require($_ENV["docroot"].$_ENV['config']["route_not_found_page"]);
			}
		}
	}
	
	private function setInfoPageURL($url)
	{
		if ($_ENV["config"]["show_debug_info"] &&
			$_ENV["config"]["env"] == Environment::DEV &&
			$_SERVER["REQUEST_METHOD"] == "GET" &&
			!isset($_GET["__hpinfo"]) &&
			!isset($_GET["__hpactual"]))
		{
			$url = "hptools/Service/frame";
		}
		return $url;
	}

	/* Singleton */
	public static function GetInstance()
	{
		if(self::$instance == NULL)
		{
			self::$instance = new HappyPuppy();
		}
		return self::$instance;
	}
}

?>

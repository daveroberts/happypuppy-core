<?
namespace HappyPuppy;
require_once($_ENV["docroot"]."config/hp.php");
require_once("lib/all.php");
require_once("SimpleCache.php");
require_once("Router.php");

function run()
{
	HappyPuppy::getInstance()->init();
	try { HappyPuppy::getInstance()->dispatch(); } catch (Exception $e)
	{
		header('HTTP/1.1 500 Internal Server Error');
		if ($_ENV['config']['env'] == Environment::DEV)
		{
			print($e);
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
		$route = Router::URLToRoute($url);
		if ($route != null)
		{
			Router::RunRoute($route, $url);
		}
		else
		{
			if ($_ENV['config']['env'] == Environment::DEV)
			{
				require($_ENV["docroot"].$_ENV['config']["route_not_found_page_debug"]);
			}
			else
			{
				require($_ENV["docroot"].$_ENV['config']["route_not_found_page"]);
			}
			exit();
		}
	}

	/* Singleton */
	public function getInstance()
	{
		if(self::$instance == NULL)
		{
			self::$instance = new HappyPuppy();
		}
		return self::$instance;
	}
}

?>

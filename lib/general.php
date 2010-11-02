<?

function include_dir($pattern)
{
	ob_start();
	foreach (glob($pattern) as $file)
	{
		include_once($file);
	}
	ob_end_clean();
}

function jslink($text, $nojsAppURL, $onclick, $html_options = array())
{
	$html_options["onclick"] = $onclick.' return false;';
	$a = new \HappyPuppy\HtmlAnchor(rawurl_from_appurl($nojsAppURL), $text, $html_options);
	return $a->toString();
}

function js_delete_confirm($confirm_text, $delete_id, $html_options = array())
{
	$confirm_js = "if (confirm('".$confirm_text."')) { var f = document.createElement('form');".
		"f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'post'; ".
		"var m = document.createElement('input'); m.setAttribute('type', 'hidden'); ".
		"m.setAttribute('name', '_method'); m.setAttribute('value', 'delete'); f.appendChild(m);".
		"var n = document.createElement('input'); n.setAttribute('type', 'hidden'); ".
		"n.setAttribute('name', 'delete_id'); n.setAttribute('value', '".$delete_id."'); f.appendChild(n);".
		" f.action = this.href;f.submit(); };return false;";
	return $confirm_js;
}

function link_to_rawurl($text, $rawurl, $html_options = array())
{
	$a = new \HappyPuppy\HtmlAnchor($rawurl, $text, $html_options);
	return $a->toString();
}

function link_to_action($text, $action, $html_options = array())
{
	return link_to_rawurl($text, rawurl_from_action($action), $html_options );
}

// links to a URL in the current application
function link_to($text, $app_url, $html_options = array())
{
	return link_to_rawurl($text, rawurl_from_appurl($app_url), $html_options);
}

function rawurl_from_appurl($app_url)
{
	$raw_url = '';
	if (strcmp($_ENV["config"]["default_app"], $_ENV["app"]->name) != 0)
	{
		$raw_url .= '/'.$_ENV["app"]->name;
	}
	$raw_url .= $app_url;
	return $raw_url;
}

function rawurl_from_action($action)
{
	return rawurl_from_controller($_ENV["controller"]->name, $action);
}

function rawurl_from_controller($controller, $action)
{
	return rawurl_from_app($_ENV["app"]->name, $controller, $action);
}
function rawurl_from_app($app, $controller, $action)
{
	$url = '/';
	if (strcmp($_ENV["config"]["default_app"], $app) != 0)
	{
		$url .= $_ENV["app"]->name.'/';
	}
	$url .= $controller.'/';
	$url .= $action;
	return $url;
}

function setflash($str){
	$_SESSION["flash"] = $str;
}
function hasflash(){
	return isset($_SESSION["flash"]);
}
function getflash($remove=true){
	$retval = $_SESSION["flash"];
	if ($remove){ unset($_SESSION["flash"]); }
	return $retval;
}

function cycle($first, $second)
{
	static $cycle_even_odd = 0;
	$cycle_even_odd++;
	$cycle_even_odd = $cycle_even_odd % 2;
	if ($cycle_even_odd == 0){ return $first; }
	return $second;
}
?>
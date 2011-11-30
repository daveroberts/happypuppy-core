<?php

function link_to($text, $location, $html_options = array())
{
	return link_to_rawurl($text, rawurl_from_location($location), $html_options);
}
function url_for($location){ return rawurl_from_location($location); }
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

function js($js_file)
{
	$path = $js_file;
	$file_location = $_ENV["docroot"]."content/js/".$js_file;
	if (file_exists($file_location))
	{
		$path = $_ENV["webroot"]."/js/".$js_file;
	}
	$file_location = $_ENV["docroot"]."apps/".$_ENV["app"]->name."/content/js/".$js_file;
	if (file_exists($file_location))
	{
		$path = rawurl_from_location("/".$_ENV["app"]->name."/js/".$js_file);
	}
	$tag = "<script src=\"".$path."\" type=\"text/javascript\"></script>";
	return $tag;
}

function css($css_file)
{
	$link = "<link rel='stylesheet' href='".rawurl_from_location("/css/".$css_file.".css")."' />";
	return $link;
}
function img($src, $alt = '')
{
	$alttag = '';
	if ($alt != ''){ $alttag = 'alt="'.$alt.'"'; }
	$imghtml = '<img src="'.$src.'" '.$alttag.' />';
	return $imghtml;
}
function png($name, $alt="")
{
	if ($alt == ''){ $alt = $name; }
	return img('/images/'.$name.'.png', $alt);
}

// THE FUNCTIONS BELOW ARE GENERALLY FOR HAPPY PUPPY ONLY
// NOTHING BAD WILL HAPPEN IF YOU CALL THEM
// BUT YOU SHOULDN'T EVER HAVE TO

function link_to_rawurl($text, $rawurl, $html_options = array())
{
	$a = new \HappyPuppy\HtmlAnchor($rawurl, $text, $html_options);
	return $a->toString();
}

function link_to_action($text, $action, $html_options = array())
{
	return link_to_rawurl($text, rawurl_from_action($action), $html_options );
}

function link_to_hpurl($text, $hp_url, $html_options = array())
{
	return link_to_rawurl($text, rawurl_from_hpurl($hp_url), $html_options);
}

// links to a URL in the current application
function link_to_appurl($text, $app_url, $html_options = array())
{
	return link_to_rawurl($text, rawurl_from_appurl($app_url), $html_options);
}

function rawurl_from_location($location)
{
	if (strcmp($location, "self") == 0){
		$new_get = $_GET;
		unset($new_get["url"]);
		$vars = "?";
		foreach($new_get as $k=>$v){
			$vars .= $k."=".urlencode($v)."&";
		}
		$vars = substr($vars, 0, strlen($vars) - 1);
		return "/".$_GET["url"].$vars;
	} else if (substr($location, 0, 1) == '/'){
		return rawurl_from_appurl($location);
	} else if(substr($location, 0, 1) == '?'){
		return rawurl_from_action($_ENV["action"].$location);
	} else {
		return rawurl_from_action($location);
	}
}

function rawurl_from_hpurl($hp_url)
{
	$raw_url = $_ENV["webroot"];
	$raw_url .= $hp_url;
	return $raw_url;
}

function rawurl_from_appurl($app_url)
{
	$raw_url = $_ENV["webroot"];
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
	$url = $_ENV["webroot"];
	if (strcmp($_ENV["config"]["default_app"], $app) != 0)
	{
		$url .= '/'.$_ENV["app"]->name;
	}
	$url .= '/'.$controller;
	$url .= '/'.$action;
	return $url;
}

?>

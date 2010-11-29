<?php

function form_start($location, $html_options = array()){
	$url = rawurl_from_location($location);
	if (!array_key_exists("method", $html_options)){ $html_options["method"] = "post"; }
	$out = "<form action='".$url."'";
	foreach($html_options as $option=>$value)
	{
		$out .= " ".$option."='".$value."' ";
	}
	$out .= ">";
	return $out;
}

function form_end(){
	return "</form>";
}

function label($label, $for='', $html_options = array()){
	$l = new \HappyPuppy\HtmlLabel($label, $for, $html_options);
	return $l->toString();
}

function hidden($name, $value, $id = '', $html_options = array()){
	$hid = new \HappyPuppy\HtmlHidden($name, $value, $id, $html_options);
	return $hid->toString();
}

function textbox($name, $default_value = '', $id = '', $htmlOptions = array()){
	$text = new \HappyPuppy\HtmlTextbox($name, $default_value, $id, $htmlOptions);
	return $text->toString();
}

function submit($value, $htmlOptions = array()){
	$htmlOptions["type"] = "submit";
	$htmlOptions["value"] = $value;
	$input = new \HappyPuppy\HtmlElement("input", true, $htmlOptions);
	return $input->toString();
}
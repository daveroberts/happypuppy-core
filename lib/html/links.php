<?

function img($src)
{
	$imghtml = '<img src="'.$src.'" />';
	return $imghtml;
}

function png($name)
{
	return img('/images/'.$name.'.png');
}

?>

<h1>Exception</h1>
<h2><?php echo $e->getMessage(); ?></h2>
<?php
	$trace = $e->getTrace();
	$params = array();
	if (array_key_exists("class", $trace[0]))
	{
		$rm = new ReflectionMethod($trace[0]['class'], $trace[0]['function']);
		foreach($rm->getParameters() as $param){
			$params[] = $param->name;
		}
	}
	else
	{
		$rf = new ReflectionFunction($trace[0]['function']);
		foreach($rf->getParameters() as $param){
			$params[] = $param->name;
		}
	}
?>
<h3>File</h3>
<div>
	<span>File: <?php echo $trace[0]['file'] ?>(<?php echo $trace[0]['line'] ?>)</span>
</div>
<h3>Params</h3>
<div>
	<span>function <?php echo trim($trace[0]['function']) ?>(<?php echo implode(",", array_map(function($v){ return "$".$v; }, $params) ) ?>)</span>
</div>
<?php for($x = 0; $x < count($trace[0]['args']); $x++): ?>
	<?php $arg = $trace[0]['args'][$x]; ?>
	<div class="param">
		<div><?php echo $params[$x] ?></div>
		<pre><?php var_dump($arg) ?></pre>
	</div>
<?php endfor; ?>
<h3>Full Trace</h3>
<div id="trace">
<?php foreach($trace as $line): ?>
<div style="color: #AAAAAA;"><?php echo $line['file']."(".$line['line'].")" ?></div>
<?php endforeach; ?>
</div>


<?php
	namespace HappyPuppy;
	function printSource($file, $target_line, $total_lines)
	{
		print("<table style='border-collapse: collapse;'><tbody>");
		$start_line = $target_line - $total_lines / 2;
		if ($start_line < 1){ $start_line = 1; }
		$end_line = $start_line + $total_lines;
		$line_num = 1;
		$file_handle = fopen($file, "r");
		$odd_style = "background-color: #E0FFE0;";
		$even_style = "background-color: #FFFFFF;";
		while (!feof($file_handle))
		{
			$line = fgets($file_handle);
			if ($line_num > $end_line){ break; }
			$row_style = $odd_style;
			if ($line_num % 2 == 0){ $row_style = $even_style; }
			if ($line_num == $target_line){ $row_style = "border: 1px solid black; background-color: #BBBBBB;"; }
			if ($line_num >= $start_line)
			{
				$code = preg_replace("(&lt;\?php&nbsp;)", "", highlight_string("<?php ".$line, 1));
				print("<tr style='".$row_style."'><td style='color: #333333; font-family: monospace; padding: 0px;'>".$line_num."</td><td style='font-family: monospace;'>".$code."</td></tr>");
			}
			$line_num = $line_num + 1;
		}
		fclose($file_handle);
		print("</tbody></table>");
	}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Exception in Application</title>
		<script type="text/javascript">
			<!--
				function toggle_visibility(id) {
				   var e = document.getElementById(id);
				   if(e.style.display == 'block')
					  e.style.display = 'none';
				   else
					  e.style.display = 'block';
				}
			//-->
		</script>
		<style type="text/css">
			.fakelink:hover { cursor:pointer; border: 1px solid black; }
			.fakelink { border: 1px solid transparent; }
		</style>
	</head>
	<body>
		<h1>Exception</h1>
		<h2 style="white-space: pre;"><?php echo $e->getMessage(); ?></h2>
		<?php
			$trace = $e->getTrace();
			$params = array();
			if (array_key_exists("class", $trace[0]))
			{
				$rm = new \ReflectionMethod($trace[0]['class'], $trace[0]['function']);
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
		<h3><?php echo $trace[0]['file'] ?></h3>
		<div>Line: <?php echo $trace[0]['line'] ?></div>

		<div>
			<?php
				$target_line = $trace[0]['line'];
				$total_lines = 10;
				$file = $trace[0]['file'];
				printSource($file, $target_line, $total_lines);
			?>
		</div>

		<h3>Params - <a href="#" onclick="toggle_visibility('params'); return false;">Show / Hide</a></h3>
		<div id="params" style="display: block;">
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
		</div>

		<h3>Full Trace</h3>
		<div id="trace" style="display: block;">
			<?php $trace_num = 1; ?>
			<?php foreach($trace as $line): ?>
				<div class="fakelink" onclick="toggle_visibility('trace_<?php echo $trace_num ?>'); return false;">
					<?php if (isset($line['line'])): ?>
						Line: <span style="color: #FF0000;"><?php echo $line['line'] ?></span>
					<?php endif; ?>
					<?php if (isset($line['function'])): ?>
						<span style="color: #222299;"><?php echo $line['function'] ?>()</span>
					<?php endif; ?>
					<?php if (isset($line['class'])): ?>
						in Class: <span style="color: #222299;"><?php echo $line['class'] ?></span>
					<?php endif; ?>
				</div>
				<div id="<?php echo 'trace_'.$trace_num ?>" style="display: none; margin-left: 3em; border-left: 1px solid black; padding-left: 1em;">
					<?php if (isset($line['file'])): ?>
						<div>
							File: <span style="color: #777777;"><?php echo $line['file'] ?></span>
						</div>
					<?php endif; ?>
					<?php if (isset($line['file']) && isset($line['line'])): ?>
						<?php printSource($line['file'], $line['line'], 10) ?>
					<?php endif; ?>
				</div>
				<?php $trace_num = $trace_num + 1; ?>
			<?php endforeach; ?>
		</div>
	</body>
</html>

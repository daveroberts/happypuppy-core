<?php
	namespace HappyPuppy;
	// mostly from Recess
	class Annotation
	{
		static function parseDocstring($docstring)
		{
			preg_match_all('%(?:\s|\*)*!(\S+)[^\n\r\S]*(?:(.*?)(?:\*/)|(.*))%', $docstring, $result, PREG_PATTERN_ORDER);
			$annotations = $result[1];
			if(isset($result[2][0]) && $result[2][0] != '') {
				$values = $result[2];
			} else { 
				$values = $result[3];
			}
			$returns = array();
			if(empty($result[1])) return array();
			foreach($annotations as $key => $annotation)
			{
				// Strip Whitespace
				$value = preg_replace('/\s*(\(|:|,|\))[^\n\r\S]*/', '${1}', $values[$key]);
				$value = rtrim($value);
				$parts = array();
				$tok = strtok($value, ",");
				while ($tok !== false)
				{
					$parts[] = $tok;
					$tok = strtok(",");
				}
				$returns[$annotation][] = $parts;
			}
			unset($annotations,$values,$result);
			return $returns;
		}
	}
?>

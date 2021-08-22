<?php declare(strict_types = 1);


function dumpfile($data, $file = 'debug.log') {
	$handle = fopen($file, 'a');
	if (is_array($data)) {
		$to_string = [];
		foreach ($data as $key => $value) {
			$to_string[] = var_export($key, true).'=>'.var_export($value, true);
		}
		fwrite($handle, 'array('.implode(',', $to_string).')'."\n\n");
	}
	else {
		fwrite($handle, var_export($data, true)."\n\n");
	}
	fclose($handle);
}

function pr($data) {
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}

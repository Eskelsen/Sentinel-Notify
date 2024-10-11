<?php

# Engine

function stareAtClock($build,$cond){
	$start = true;
	foreach ($cond as $time => $value) {
		$start = ($start AND (date($time)==$value));
	}
	if ($start) {
		$file = APP . 'build/' . $build . '.php';
		if (!is_file($file)) {
			microlog("Arquivo da build $build inexistente");
			return;
		}
		include $file;
		microlog("Arquivo da build $build executado");
		return;
	}
}

<?php

# Engine

function stareAtClock($build,$cond){
	$start = true;
	if (timeFinished($cond)) {
		return false;
	}
	foreach ($cond as $time => $value) {
		$start = ($start AND timeMatchVerify($time,$value));
	}
	if ($start) {
		$file = APP . 'build/' . $build . '.php';
		if (!is_file($file)) {
			microlog("Arquivo da build $build inexistente");
			return false;
		}
		include $file;
		microlog("Arquivo da build $build executado");
	}
}

function timeMatchVerify($time, $value){
	if (!is_array($value)) {
		return date($time)==$value;
	}
	return in_array(date($time),$value);
}

function timeFinished(&$in){
	if (empty($in['final'])) {
		return false;
	}
	if (time()>=strtotime($in['final']) + 86399) {
		return true;
	}
	unset($in['final']);
	return false;
}

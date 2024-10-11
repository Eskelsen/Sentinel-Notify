<?php

# JSON Fy Functions

function json_pretty($in){
	return json_encode($in, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
}

function jsonRead($file){
	$ctn = is_file($file) ? file_get_contents($file) : false;
	return ($ctn) ? json_decode($ctn, 1) : false;
}

function jsonWrite($file, $data){
	$mid = is_string($data) ? json_decode($data, 1) : $data;
	$ctn = json_pretty($mid);
	return ($ctn) ? file_put_contents($file, $ctn) : false;
}

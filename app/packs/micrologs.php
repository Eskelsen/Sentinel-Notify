<?php

# Micrologs: Logs & Debug Functions

# Microlog Default File
define('MICRO_LOG', WEB . 'micrologs.txt');

function microlog($msg, $file = MICRO_LOG){
	$data = time() . '|' . date('Y-m-d H:i:s') . '|' . $msg . "\r\n";
	file_put_contents($file, $data, FILE_APPEND);
}

function vd($in){
    echo '<pre>';
    var_dump($in);
    echo '</pre>';
}

function vds(){
    echo '<pre>';
    var_dump(func_get_args());
    echo '</pre>';
}

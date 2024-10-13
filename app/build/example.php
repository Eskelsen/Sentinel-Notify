<?php

# Example

include INFRA . 'mail.php';

$email = 'email@email.com';
$title = 'Teste do Sentinel Unotify';
$html  = file_get_contents(TMPLTS . 'example.html');

// $sent = sendMail($email,$title,$html); # Configure antes de descomentar

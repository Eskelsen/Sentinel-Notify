<?php

# Test

include INFRA . 'mail.php';

$title = 'Plataforma VEM';
$template = file_get_contents(TMPLTS . 'vem-events.html');

# Send One
$data['nome'] = 'Daniel';
$data['mensagem'] = 'O sistema hoje está em 100% de sua capacidade, estourando a boca do balão.';
$data['link'] = 'https://projetos.microframeworks.com/progresso/vem-events';
$data['sendername'] = 'Mfwks.com';

$in = [];

foreach ($data as $chave => $valor) {
	$in[] = "[$chave]";
}

$out = array_values($data);
$html = str_replace($in,$out,$template);
$sent = sendMail('eskelsen@yahoo.com',$title,$html,$data);

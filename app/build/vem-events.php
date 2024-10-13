<?php

# Vem Events

include INFRA . 'mail.php';

$title = 'Plataforma VEM';
$template = file_get_contents(TMPLTS . 'vem-events.html');

# Send One
$data['nome'] = 'Daniel';
$data['mensagem'] = 'O sistema hoje está em 100% de sua capacidade, estourando a boca do balão.';
$data['link'] = 'https://projetos.microframeworks.com/progresso/vem-events';

$in = [];

foreach ($data as $chave => $valor) {
	$in[] = "[$chave]";
}

$out = array_values($data);
$html = str_replace($in,$out,$template);
$sent = sendMail('eskelsen@yahoo.com',$title,$html);

# Send Two
$data['nome'] = 'Nat'; // =================== ALTERAR NOME
$data['mensagem'] = 'O sistema hoje está em 100% de sua capacidade, estourando a boca do balão.';
$data['link'] = 'https://projetos.microframeworks.com/progresso/vem-events';

$in = [];

foreach ($data as $chave => $valor) {
	$in[] = "[$chave]";
}

$out = array_values($data);
$html = str_replace($in,$out,$template);
$sent = sendMail('eskelsen@yahoo.com',$title,$html); // =================== ALTERAR E-MAIL

# Send Three
$data['nome'] = 'Rica'; // =================== ALTERAR NOME
$data['mensagem'] = 'O sistema hoje está em 100% de sua capacidade, estourando a boca do balão.';
$data['link'] = 'https://projetos.microframeworks.com/progresso/vem-events';

$in = [];

foreach ($data as $chave => $valor) {
	$in[] = "[$chave]";
}

$out = array_values($data);
$html = str_replace($in,$out,$template);
$sent = sendMail('eskelsen@yahoo.com',$title,$html); // =================== ALTERAR E-MAIL

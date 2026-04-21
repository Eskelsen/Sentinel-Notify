<?php

# Start

include APP . 'bootstrap.php';

$token = $_GET['token'] ?? null;
$token = $token ?: ($argv[1] ?? null);

if (!defined('TOKEN')) {
	microlog('Constante TOKEN ausente em app/env.php.');
	exit('Constante TOKEN ausente em app/env.php.');
}

if (empty($token) OR $token!==TOKEN) {
	microlog('Token ausente ou inválido.');
	exit('Token ausente ou inválido.' . PHP_EOL);
}

runReminders();

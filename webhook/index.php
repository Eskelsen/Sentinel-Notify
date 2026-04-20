<?php

include __DIR__ . '/../axis.php';
include APP . 'bootstrap.php';
include APP . 'webhook.php';

$ip = $_SERVER['REMOTE_HOST'] ?? 'cli_mode';

microlog('webhook index: ' . $ip);

handleTelegramWebhook();

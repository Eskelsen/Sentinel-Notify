<?php

include __DIR__ . '/../axis.php';
include APP . 'bootstrap.php';
include APP . 'webhook.php';

header('Content-Type: application/json; charset=UTF-8');

if (!defined('TOKEN') || !TOKEN) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Constante TOKEN ausente.'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

if (($_GET['token'] ?? '') !== TOKEN) {
	http_response_code(401);
	echo json_encode(['ok' => false, 'message' => 'Token invalido.'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

try {
	$result = telegramSetWebhook();
	echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

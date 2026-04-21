<?php

include_once INFRA . 'openai.php';
include_once INFRA . 'telegram.php';

function webhookJsonResponse($data, $status = 200){
	http_response_code($status);
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	return true;
}

function webhookReadPayload(){
	$raw = file_get_contents('php://input');
    microlog('webhookReadPayload: ' . $raw);
	$data = json_decode((string) $raw, true);
	return is_array($data) ? $data : [];
}

function webhookValidateTelegramSecret(){
	if (!defined('TG_WEBHOOK_SECRET') || TG_WEBHOOK_SECRET === '') {
		return true;
	}

	$header = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
	return hash_equals((string) TG_WEBHOOK_SECRET, (string) $header);
}

function webhookTelegramMessage($payload){
	foreach (['message', 'edited_message', 'channel_post', 'edited_channel_post'] as $key) {
		if (!empty($payload[$key]) && is_array($payload[$key])) {
			return $payload[$key];
		}
	}
	return [];
}

function reminderSlug($value){
	$value = strtolower(trim((string) $value));
	$value = preg_replace('/[^a-z0-9]+/', '-', $value);
	$value = trim((string) $value, '-');
	return $value ?: 'telegram-reminder';
}

function reminderNormalizeField($value){
	if (is_array($value)) {
		$items = [];
		foreach ($value as $item) {
			if (!is_numeric($item)) {
				continue;
			}
			$items[] = (int) $item;
		}
		$items = array_values(array_unique($items));
		sort($items);
		return count($items) === 1 ? $items[0] : $items;
	}
	if (is_numeric($value)) {
		return (int) $value;
	}
	return null;
}

function reminderNormalizePayload($payload, $chatId){
	if (!is_array($payload)) {
		throw new InvalidArgumentException('Payload de reminder invalido.');
	}

	$allowedSchedule = ['final', 'i', 'H', 'd', 'm', 'w', 'Y'];
	$normalized = [
		'name' => reminderSlug($payload['name'] ?? ''),
		'description' => trim((string) ($payload['description'] ?? 'Lembrete criado via Telegram')),
		'enabled' => array_key_exists('enabled', $payload) ? (bool) $payload['enabled'] : true,
		'operations' => []
	];

	foreach ($allowedSchedule as $key) {
		if (!array_key_exists($key, $payload)) {
			continue;
		}
		if ($key === 'final') {
			$final = trim((string) $payload[$key]);
			if ($final !== '') {
				$normalized[$key] = $final;
			}
			continue;
		}
		$value = reminderNormalizeField($payload[$key]);
		if ($value !== null) {
			$normalized[$key] = $value;
		}
	}

	$operations = $payload['operations'] ?? [];
	if (!is_array($operations) || empty($operations[0]) || !is_array($operations[0])) {
		throw new InvalidArgumentException('Reminder sem operacao valida.');
	}

	$operation = $operations[0];
	$message = trim((string) ($operation['message'] ?? ''));
	if ($message === '') {
		throw new InvalidArgumentException('Reminder sem mensagem final.');
	}

	$normalized['operations'][] = [
		'type' => 'telegram',
		'chat_id' => is_numeric($chatId) ? (int) $chatId : (string) $chatId,
		'message' => $message,
		'parse_mode' => false
	];

	return array_filter($normalized);
}

function reminderFileName($name){
	$base = reminderSlug($name);
	$file = date('Ymd-His') . '-' . $base . '.json';
	$path = REMINDERS . $file;
	if (!is_file($path)) {
		return $path;
	}
	return REMINDERS . date('Ymd-His') . '-' . $base . '-' . substr(md5(uniqid((string) mt_rand(), true)), 0, 6) . '.json';
}

function reminderCreateFromTelegramText($text, $chatId){

	$request = 'Pedido do usuario: ' . trim((string) $text);

    microlog($request);

	$parsed = openaiExtractReminder($request);
	$reminder = reminderNormalizePayload($parsed, $chatId);
	$file = reminderFileName($reminder['name']);

	if (!jsonWrite($file, $reminder)) {
		throw new RuntimeException('Nao foi possivel salvar o reminder em disco.');
	}

	return [
		'file' => $file,
		'reminder' => $reminder
	];
}

function handleTelegramWebhook(){
	if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
		return webhookJsonResponse(['ok' => false, 'message' => 'Method not allowed'], 405);
	}

	if (!webhookValidateTelegramSecret()) {
		microlog('Webhook Telegram rejeitado por secret_token invalido.');
		return webhookJsonResponse(['ok' => false, 'message' => 'Unauthorized'], 401);
	}

	$payload = webhookReadPayload();
	if (empty($payload)) {
		return webhookJsonResponse(['ok' => true, 'message' => 'Empty payload ignored']);
	}

	$message = webhookTelegramMessage($payload);
	$text = trim((string) ($message['text'] ?? ''));
	$chatId = $message['chat']['id'] ?? null;

	if ($chatId === null || $text === '') {
		return webhookJsonResponse(['ok' => true, 'message' => 'Update ignored']);
	}

	try {
        
		$created = reminderCreateFromTelegramText($text, $chatId);
		$reply = 'Lembrete salvo com sucesso.';

		tgSendMgs($reply, [
			'chat_id' => $chatId,
			'parse_mode' => false
		]);

		microlog('Webhook Telegram criou reminder ' . basename($created['file']) . '.');
		return webhookJsonResponse(['ok' => true, 'file' => basename($created['file'])]);
	} catch (Throwable $e) {
		microlog('Falha no webhook Telegram: ' . $e->getMessage());
		tgSendMgs('Nao consegui criar o lembrete: ' . $e->getMessage(), [
			'chat_id' => $chatId,
			'parse_mode' => false
		]);
		return webhookJsonResponse(['ok' => true, 'message' => 'Algum problema, mas foi do lado de cá.']);
	}
}

function telegramSetWebhook(){
	if (!defined('TG_TOKEN') || !TG_TOKEN) {
		throw new RuntimeException('Constante TG_TOKEN ausente em app/env.php.');
	}
	if (!defined('TG_WEBHOOK_URL') || !TG_WEBHOOK_URL) {
		throw new RuntimeException('Constante TG_WEBHOOK_URL ausente em app/env.php.');
	}

	$data = [
		'url' => TG_WEBHOOK_URL,
		'allowed_updates' => ['message', 'edited_message', 'channel_post', 'edited_channel_post'],
		'drop_pending_updates' => !empty($_GET['drop_pending_updates'])
	];

	if (defined('TG_WEBHOOK_SECRET') && TG_WEBHOOK_SECRET) {
		$data['secret_token'] = TG_WEBHOOK_SECRET;
	}

	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://api.telegram.org/bot' . TG_TOKEN . '/setWebhook',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
	]);

	$response = curl_exec($curl);
	$error = curl_error($curl);
	$status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
	curl_close($curl);

	if ($error) {
		throw new RuntimeException('Falha ao configurar webhook no Telegram: ' . $error);
	}

	$decoded = json_decode((string) $response, true);
	if (!is_array($decoded)) {
		throw new RuntimeException('Resposta invalida do Telegram ao configurar webhook.');
	}
	if ($status >= 400 || empty($decoded['ok'])) {
		$message = $decoded['description'] ?? ('Erro HTTP ' . $status);
		throw new RuntimeException('Telegram recusou o webhook: ' . $message);
	}

	return $decoded;
}

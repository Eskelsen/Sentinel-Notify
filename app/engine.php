<?php

# Engine

define('REMINDERS', APP . 'reminders/');

function runReminders(){
	$files = glob(REMINDERS . '*.json') ?: [];
	if (empty($files)) {
		microlog('Nenhum reminder JSON encontrado.');
		return false;
	}
	foreach ($files as $file) {
		runReminderFile($file);
	}
	return true;
}

function runReminderFile($file){
	$reminder = jsonRead($file);
	$name = basename($file, '.json');
	if (!is_array($reminder)) {
		microlog("Reminder $name inválido ou vazio.");
		return false;
	}
	if (array_key_exists('enabled', $reminder) && !$reminder['enabled']) {
		microlog("Reminder $name desabilitado.");
		return false;
	}
	$schedule = reminderSchedule($reminder);
	if (!shouldRunReminder($schedule)) {
        microlog("Reminder $name para outro momento.");
		return false;
	}
	$operations = reminderOperations($reminder);
	if (empty($operations)) {
		microlog("Reminder $name sem operações válidas.");
		return false;
	}
	$sent = false;
	foreach ($operations as $operation) {
		$sent = dispatchReminderOperation($name, $operation) || $sent;
	}
	if ($sent) {
		microlog("Reminder $name processado.");
	} else {
        microlog("Reminder $name falhou em seu processamento.");
    }
	return $sent;
}

function reminderSchedule($reminder){
	$ignore = ['name', 'description', 'enabled', 'operations'];
	$schedule = [];
	foreach ($reminder as $key => $value) {
		if (in_array($key, $ignore, true)) {
			continue;
		}
		$schedule[$key] = $value;
	}
	return $schedule;
}

function reminderOperations($reminder){
	if (empty($reminder['operations']) || !is_array($reminder['operations'])) {
		return [];
	}
	return array_values(array_filter($reminder['operations'], 'is_array'));
}

function shouldRunReminder($schedule){
	$start = true;
	if (timeFinished($schedule)) {
		return false;
	}
	foreach ($schedule as $time => $value) {
		$start = ($start && timeMatchVerify($time, $value));
	}
	return $start;
}

function dispatchReminderOperation($name, $operation){
	$type = strtolower(trim($operation['type'] ?? ''));
	if ($type === 'email') {
		return dispatchReminderMail($name, $operation);
	}
	if ($type === 'telegram') {
		return dispatchReminderTelegram($name, $operation);
	}
	microlog("Reminder $name com operação desconhecida: $type");
	return false;
}

function dispatchReminderMail($name, $operation){
	$email = $operation['email'] ?? '';
	$title = $operation['title'] ?? "Reminder $name";
	$message = $operation['message'] ?? ($operation['html'] ?? '');
	if (empty($email) || empty($message)) {
		microlog("Reminder $name com operação de e-mail incompleta.");
		return false;
	}
	include_once INFRA . 'mail.php';
	$html = !empty($operation['is_html']) ? $message : nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
	return sendMail($email, $title, $html, $operation['others'] ?? false);
}

function dispatchReminderTelegram($name, $operation){
	$message = $operation['message'] ?? ($operation['text'] ?? '');
	if (empty($message)) {
		microlog("Reminder $name com operação de Telegram incompleta.");
		return false;
	}
	include_once INFRA . 'telegram.php';
	return (bool) tgSendMgs($message, $operation);
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

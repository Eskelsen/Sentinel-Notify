<?php

function tgSendMgs($msg, $options = []){
	$chatId = $options['chat_id'] ?? (defined('TG_CHAT') ? TG_CHAT : null);
	if (empty($chatId)) {
		error_log(json_encode(['status' => false, 'message' => 'TG_CHAT ausente']));
		return false;
	}

	$url = 'https://api.telegram.org/bot' . TG_TOKEN . '/sendMessage';
	$data = [
		'chat_id' => $chatId,
		'text' 	  => $msg
	];

	if (array_key_exists('parse_mode', $options)) {
		if ($options['parse_mode']) {
			$data['parse_mode'] = $options['parse_mode'];
		}
	} else {
		$data['parse_mode'] = 'HTML';
	}

	$curl = curl_init();
	$data = is_string($data) ? $data : json_encode($data);
	
	$headers = [
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data)
	];
	
    curl_setopt_array($curl, [
        CURLOPT_URL             => $url,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_ENCODING        => '',
        CURLOPT_MAXREDIRS       => 10,
        CURLOPT_TIMEOUT         => 30,
        CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST   => 'POST',
        CURLOPT_POSTFIELDS      => $data,
        CURLOPT_HTTPHEADER      => $headers
    ]);
	
    $response = curl_exec($curl);

    $e = curl_error($curl);
	
    curl_close($curl);
	
	if ($e) {
		error_log(json_encode(['status' => false, 'message' => $e]));
        return;
	}
	
    $data = json_decode($response, 1);

	return $data['result'] ?? false;
}

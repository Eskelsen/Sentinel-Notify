<?php

function tgSendMgs($chat_id, $msg, $tkn){
	$url = 'https://api.telegram.org/bot' . $tkn . '/sendMessage';
	$data = [
		'chat_id' => $chat_id,
		'text' 	  => $msg,
		'parse_mode' => 'HTML'
	];

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

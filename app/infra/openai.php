<?php

function openaiReminderSchema(){
	$multiInt = [
		'anyOf' => [
			['type' => 'integer'],
			[
				'type' => 'array',
				'items' => ['type' => 'integer'],
				'minItems' => 1
			],
			['type' => 'null']
		]
	];

	return [
		'type' => 'object',
		'properties' => [
			'name' => ['type' => 'string'],
			'description' => ['type' => 'string'],
			'enabled' => ['type' => 'boolean'],
			'final' => ['type' => ['string', 'null']],
			'i' => $multiInt,
			'H' => $multiInt,
			'd' => $multiInt,
			'm' => $multiInt,
			'w' => $multiInt,
			'Y' => $multiInt,
			'operations' => [
				'type' => 'array',
				'minItems' => 1,
				'items' => [
					'type' => 'object',
					'properties' => [
						'type' => [
							'type' => 'string',
							'enum' => ['telegram']
						],
						'message' => ['type' => 'string']
					],
					'required' => ['type', 'message'],
					'additionalProperties' => false
				]
			]
		],
		'required' => ['name', 'description', 'enabled', 'final', 'i', 'H', 'd', 'm', 'w', 'Y', 'operations'],
		'additionalProperties' => false
	];
}

function openaiReminderSystemPrompt(){

	$now = date('Y-m-d H:i:s');
	$timezone = date_default_timezone_get();
	$mapLock = is_file(APP . 'map.lock') ? trim(file_get_contents(APP . 'map.lock')) : '';

	return implode("\n\n", [
		'Voce converte pedidos em linguagem natural para arquivos JSON de lembretes do sistema Sentinel Notify.',
		"Data/hora atual do servidor: {$now}.",
		"Timezone do servidor: {$timezone}.",
		'Regras obrigatorias:',
		'- Responda apenas com JSON valido seguindo o schema.',
		'- Se um campo de tempo não existir, não inclua na resposta.',
		'- Respeite os padrões ECMA-404 e UTF-8',
		'- Use parse_mode=false para texto puro e evitar problemas com HTML.',
		'- Para lembrete unico, calcule uma data/horario exatos usando Y, m, d, H, i e final na mesma data.',
		'- O campo final deve ser inclusivo e usar formato YYYY-MM-DD.',
		'- O campo message deve conter apenas a mensagem final que sera enviada no horario do lembrete.',
		'- O campo name deve ser curto e em formato slug simples.',
		'- Nao invente horarios ausentes; se o pedido estiver incompleto, assuma o horario relativo mais direto descrito pelo usuario.',
		'- Se o usuario nao deixar claro o texto do lembrete, seja como um interlocutor que o recorda, por exemplo: "Lembre-se de tal e tal coisa',
		'Referencia de configuracao antiga (map.lock):',
		$mapLock,
		'Exemplos de reminders JSON:',
        '{
        "name": "example-telegram",
        "description": "Exemplo de reminder de Telegram",
        "final": "2026-04-16",
        "i": 30,
        "H": 8,
        "d": [
            1,
            15
        ],
        "operations": [
            {
            "type": "telegram",
            "message": "Lembrete de exemplo enviado pelo Sentinel Notify."
            }
        ]
        }'
	]);
}

function openaiExtractReminder($request){
	if (!defined('OPENAI_API_KEY') || !OPENAI_API_KEY) {
		throw new RuntimeException('Constante OPENAI_API_KEY ausente em app/env.php.');
	}
    microlog('openai in: ' . json_pretty($request));
	$payload = [
		'model' => defined('OPENAI_MODEL') && OPENAI_MODEL ? OPENAI_MODEL : 'gpt-4o-mini',
		'input' => [
			[
				'role' => 'system',
				'content' => openaiReminderSystemPrompt()
			],
			[
				'role' => 'user',
				'content' => $request
			]
		],
		'text' => [
			'format' => [
				'type' => 'json_schema',
				'name' => 'sentinel_reminder',
				'strict' => true,
				'schema' => openaiReminderSchema()
			]
		]
	];

	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://api.openai.com/v1/responses',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_HTTPHEADER => [
			'Authorization: Bearer ' . OPENAI_API_KEY,
			'Content-Type: application/json'
		],
		CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
	]);

	$response = curl_exec($curl);
    microlog('openai out: ' . $response);
	$error = curl_error($curl);
	$status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
	curl_close($curl);

	if ($error) {
		throw new RuntimeException('Falha ao consultar OpenAI: ' . $error);
	}

	$data = json_decode((string) $response, true);
	if (!is_array($data)) {
		throw new RuntimeException('Resposta invalida da OpenAI.');
	}
	if ($status >= 400) {
		$message = $data['error']['message'] ?? 'Erro HTTP ' . $status;
		throw new RuntimeException('OpenAI retornou erro: ' . $message);
	}

	foreach ($data['output'] ?? [] as $output) {
		foreach ($output['content'] ?? [] as $content) {
			if (($content['type'] ?? '') === 'output_text') {
				$text = trim((string) ($content['text'] ?? ''));
				if ($text !== '') {
					$decoded = json_decode($text, true);
					if (is_array($decoded)) {
						return $decoded;
					}
				}
			}
			if (($content['type'] ?? '') === 'refusal') {
				$refusal = trim((string) ($content['refusal'] ?? ''));
				throw new RuntimeException($refusal ?: 'A OpenAI recusou a solicitacao.');
			}
		}
	}

	$text = trim((string) ($data['output_text'] ?? ''));
	if ($text !== '') {
		$decoded = json_decode($text, true);
		if (is_array($decoded)) {
			return $decoded;
		}
	}

	throw new RuntimeException('A OpenAI nao retornou um JSON utilizavel.');
}

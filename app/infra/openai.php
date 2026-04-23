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

    microlog('now: ' . $now);
    microlog('timezone: ' . $timezone);

	return implode("\n\n", [
		'Voce converte pedidos em linguagem natural para arquivos JSON de lembretes do sistema Sentinel Notify.',
		"Data/hora atual do servidor: {$now}.",
		"Timezone do servidor: {$timezone}.",
		'Funcionamento:',
		'Voce converte pedidos em linguagem natural para arquivos JSON de lembretes do sistema Sentinel Notify.

Modo de operação:
- Não seja criativo.
- Não invente regras.
- Siga estritamente as instruções abaixo.
- Produza apenas JSON válido.

Execute exatamente nesta ordem:

1. Determine o tipo de lembrete:"unico" ou "recorrente"

2. Determine a frequência: daily, weekly, monthly, yearly, null (apenas para lembrete unico)

3. Determine os campos obrigatórios com base no tipo:

- unico: Y, m, d, H, i e final = mesma data (YYYY-MM-DD)

- daily: H, i

- weekly: H, i, w (array de 0 a 6, onde 0 = domingo)

- monthly: H, i, d (array de 1 a 31)

- yearly: H, i, m, d

4. Interprete o horário:

- "daqui a X minutos" → now + X minutos
- "daqui a X horas" → now + X horas
- "de manhã" → 09:00
- "à tarde" → 15:00
- "à noite" → 20:00
- "mais tarde" → +2 horas

Nunca deixe H e i nulos.

5. Garanta que o horário esteja no futuro:

- Se estiver no passado ou "agora", ajuste para a próxima ocorrência válida.

6. Determine o campo "final":

- Se o usuário informar → respeitar
- Se não informar:
  - recorrente → usar data atual + 1 ano
  - unico → mesma data do evento

Formato obrigatório: YYYY-MM-DD (inclusivo)

7. Preencha campos não utilizados com null.

REGRAS OBRIGATÓRIAS

- Responda apenas com JSON válido (sem texto fora do JSON)
- Use UTF-8 e padrão ECMA-404
- Nunca gere lembretes sem horário (H e i são obrigatórios)
- Nunca gere datas no passado
- "final" deve ser >= primeira ocorrência
- Não misture tipos de recorrência (ex: não usar w e d juntos)
- Não invente horários não inferíveis — use as regras de interpretação
- O campo "message" deve conter apenas o texto do lembrete
- O campo "name" deve ser curto e em formato slug (ex: lembrar-pagar-conta)
- O campo "description" deve explicar o lembrete de forma clara
- Se o usuário não fornecer mensagem, use: "Lembre-se de ..." com base no contexto

MAPEAMENTO DE CAMPOS (todos podem ser array caso seja necessária mais de uma ocorrência)

- Y → ano (4 dígitos)
- m → mês (1-12)
- d → dia do mês (1-31) ou array (monthly/yearly)
- w → dia da semana (0-6, domingo = 0) (array)
- H → hora (0-23)
- i → minuto (0-59)

VALIDAÇÃO FINAL (OBRIGATÓRIA)

Antes de responder, valide:

- JSON está válido
- Todos os campos obrigatórios estão presentes
- Nenhuma data está no passado
- H e i estão definidos
- "final" está correto
- Arrays obrigatórios não estão vazios
- Campos não usados estão como null

Se qualquer validação falhar, corrija antes de responder.',
		$mapLock,
		'Exemplos de reminders JSON:',
        '{
        "name": "example-telegram",
        "description": "Lembrete via Telegram para caminhadas quinzenais para o fim de tarde até o fim do ano",
        "final": "2026-12-31",
        "i": 30,
        "H": 16,
        "d": [
            1,
            15
        ],
        "operations": [
            {
            "type": "telegram",
            "message": "Lembre-se da caminhada agora à tarde."
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

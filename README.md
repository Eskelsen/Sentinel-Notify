# Sentinel-Notify
Source code-based periodic notification management system

## Instalação

```shell
git clone -b master --single-branch git@github.com:Eskelsen/Sentinel-Notify.git .
```

Um arquivo `app/env.php` deve ser criado a partir do `app/env.lock` e configurado conforme necessário, incluindo o `TOKEN` de acesso:

```shell
cp app/env.lock app/env.php
```

Se você já possui um `app/env.php`, adicione também as novas constantes de Telegram/OpenAI:

```php
define('TOKEN', 'seu_token_de_acesso');
define('TG_CHAT', 'chat_id_padrao_ou_canal');
define('TG_TOKEN', 'telegram_bot_token');
define('TG_WEBHOOK_URL', 'https://seu-dominio.com/webhook/index.php');
define('TG_WEBHOOK_SECRET', 'telegram_webhook_secret');
define('OPENAI_API_KEY', 'openai_api_key');
define('OPENAI_MODEL', 'gpt-4o-mini');
```

É necessário rodar o composer para o serviço de e-mails:
```shell
composer install
```

Os lembretes passam a ser definidos em arquivos JSON dentro de `app/reminders/`.
Os arquivos `example-*.json` servem de modelo e podem ser duplicados para lembretes reais.

## Webhook Telegram

O projeto agora possui um receiver em `webhook/index.php` para receber updates do Telegram e transformar mensagens em reminders JSON com a OpenAI.

Fluxo:

1. O Telegram envia um `POST` para `webhook/index.php`
2. O endpoint valida o `secret_token`
3. A mensagem em linguagem natural vai para a OpenAI
4. O retorno estruturado vira um arquivo em `app/reminders/`
5. O cron existente executa o reminder no horario correto

Para registrar o webhook no Telegram, acesse:

```text
/webhook/setup.php?token=SEU_TOKEN
```

Se quiser descartar mensagens pendentes antigas ao registrar:

```text
/webhook/setup.php?token=SEU_TOKEN&drop_pending_updates=1
```

Exemplo de mensagem suportada:

```text
me recorde daqui 10 min que preciso pegar o queijo na geladeira
```

O reminder salvo inclui `chat_id` dinamico, entao a execucao do cron responde no mesmo chat que originou o pedido.

## Manutenção

Acompanhe os logs em micrologs.txt e errors.txt. Remova esses arquivos periodicamente.

## Sobre

Baseado num downgrade do Microframework 0.5.3 *Viewer* de domingo, 5 de fevereiro de 2023. Blumenau.

## Contato

- Email: dev@microframeworks.com
- Site: https://microframeworks.com

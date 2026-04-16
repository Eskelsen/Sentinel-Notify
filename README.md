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

É necessário rodar o composer para o serviço de e-mails:
```shell
composer install
```

Os lembretes passam a ser definidos em arquivos JSON dentro de `app/reminders/`.
Os arquivos `example-*.json` servem de modelo e podem ser duplicados para lembretes reais.

## Manutenção

Acompanhe os logs em micrologs.txt e errors.txt. Remova esses arquivos periodicamente.

## Sobre

Baseado num downgrade do Microframework 0.5.3 *Viewer* de domingo, 5 de fevereiro de 2023. Blumenau.

## Contato

- Email: dev@microframeworks.com
- Site: https://microframeworks.com

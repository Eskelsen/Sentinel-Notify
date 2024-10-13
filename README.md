# Sentinel-Notify
Source code-based periodic notification management system

## Instalação

```shell
git clone -b master --single-branch git@github.com:Eskelsen/Sentinel-Notify.git .
```

Um arquivo app/map.php deve ser criado a partir do app/map.lock e configurado conforme necessário:

```shell
cp app/map.lock app/map.php
```

É necessário rodar o composer para o serviço de e-mails:
```shell
composer install
```

## Manutenção

Acompanhe os logs em micrologs.txt e errors.txt. Remova esses arquivos periodicamente.

## Sobre

Baseado num downgrade do Microframework 0.5.3 *Viewer* de domingo, 5 de fevereiro de 2023. Blumenau.

## Contato

- Email: dev@microframeworks.com
- Site: https://microframeworks.com

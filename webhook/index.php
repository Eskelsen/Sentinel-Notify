<?php

include __DIR__ . '/../axis.php';
include APP . 'bootstrap.php';
include APP . 'webhook.php';

microlog('Acesso ao webhook');

handleTelegramWebhook();

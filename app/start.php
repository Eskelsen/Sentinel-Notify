<?php

# Start

# Hold headers
ob_start();

# Hub, Routes & Basics
include APP . 'hub.php';
include APP . 'env.php';
include PACKS . 'jsonfy.php';

# Engine
include APP . 'engine.php';

if (!defined('TOKEN')) {
	microlog('Constante TOKEN ausente em app/env.php.');
	exit('Constante TOKEN ausente em app/env.php.');
}

if (empty($_GET['token']) OR $_GET['token']!==TOKEN) {
	microlog('Token ausente ou inválido.');
	exit('Token ausente ou inválido.');
}

# Run Reminders
runReminders();

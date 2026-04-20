<?php

# Start

include APP . 'bootstrap.php';

if (!defined('TOKEN')) {
	microlog('Constante TOKEN ausente em app/env.php ou app/map.php.');
	exit('Constante TOKEN ausente em app/env.php ou app/map.php.');
}

if (empty($_GET['token']) OR $_GET['token']!==TOKEN) {
	microlog('Token ausente ou inválido.');
	exit('Token ausente ou inválido.');
}

# Run Reminders
runReminders();

<?php

# Start

# Hold headers
ob_start();

# Hub, Routes & Basics
include APP . 'hub.php';

# Map Tasks
include APP . 'map.php';

# Engine
include APP . 'engine.php';

if (empty($_GET['token']) OR $_GET['token']!==TOKEN) {
	microlog('Token ausente ou inválido.');
	exit('Token ausente ou inválido.');
}

# Stare at Clock
foreach ($map as $build => $cond) {
	stareAtClock($build,$cond);
}

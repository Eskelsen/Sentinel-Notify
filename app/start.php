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

if (empty($_GET['token']) OR $_GET['token']!=='271ac816e25a3b657b1fbb3f752c7374bc67f364') {
	exit('Sem o token.');
}

vd(date('Y-m-d H:i:s'));

# Stare at Clock
foreach ($map as $build => $cond) {
	stareAtClock($build,$cond);
}

<?php

# Bootstrap compartilhado entre cron e webhook

if (!defined('SENTINEL_BOOTSTRAPPED')) {
	define('SENTINEL_BOOTSTRAPPED', true);

	# Hold headers
	ob_start();

	# Hub, configs e helpers básicos
	include_once APP . 'hub.php';
	if (is_file(APP . 'env.php')) {
		include_once APP . 'env.php';
	}
	if (is_file(APP . 'map.php')) {
		include_once APP . 'map.php';
	}
	include_once PACKS . 'jsonfy.php';

	# Engine principal
	include_once APP . 'engine.php';
}

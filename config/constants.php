<?php

if (!defined('APP_ROOT')) {
	define('APP_ROOT', dirname(__DIR__));
}

if (!defined('STORAGE_PATH')) {
	define('STORAGE_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'storage');
}

if (!defined('LOG_PATH')) {
	define('LOG_PATH', STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log');
}

if (!defined('VIEW_PATH')) {
	define('VIEW_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views');
}

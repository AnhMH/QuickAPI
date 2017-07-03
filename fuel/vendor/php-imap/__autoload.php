<?php 
namespace PhpImap;
spl_autoload_register(function ($class) {
	if (strpos($class, __NAMESPACE__) === 0) {
		/** @noinspection PhpIncludeInspection */
        //echo __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php'; exit;
		require_once(__DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
	}
});
<?php

	require_once CORE_PATH.'constants.php';
	require_once CORE_PATH.'common.php';
	require_once CORE_LIBRARY_PATH.'Loader.php';
	
	Loader::register();
	
	Loader::functions('string');
	Loader::functions('url');
	
	
	set_error_handler('error_handler');
	set_exception_handler('exception_handler');
	
	Config::init();
	
	//checking environment
	if(version_compare(phpversion(), TNH_REQUIRED_PHP_MIN_VERSION, '<')){
		show_error('Your PHP Version <b>'.phpversion().'</b> is less than <b>'.TNH_REQUIRED_PHP_MIN_VERSION.'</b>, please install a new version or update your PHP to the latest.', 'Error environment');	
	}
	else if(version_compare(phpversion(), TNH_REQUIRED_PHP_MAX_VERSION, '>')){
		show_error('Your PHP Version <b>'.phpversion().'</b> is greather than <b>'.TNH_REQUIRED_PHP_MAX_VERSION.'</b> please install a PHP version that is compatible.', 'Error environment');	
	}
	
	//routing
	$router = new Router();
	$router->dispatch();
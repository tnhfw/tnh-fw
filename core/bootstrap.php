<?php
	defined('ROOT_PATH') || exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework created using the concept of codeigniter with bootstrap twitter
	 *
	 * This content is released under the GNU GPL License (GPL)
	 *
	 * Copyright (C) 2017 Tony NGUEREZA
	 *
	 * This program is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU General Public License
	 * as published by the Free Software Foundation; either version 3
	 * of the License, or (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program; if not, write to the Free Software
	 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
	*/

	/**
	 *  @file bootstrap.php
	 *  
	 *  Contains the loading process: loading of constants, functions and libraries essential 
	 *  to the good functioning of the application, the loading of the configurations,
	 *  verification of the environment and the routing of the request.
	 *  
	 *  @package	core	
	 *  @author	Tony NGUEREZA
	 *  @copyright	Copyright (c) 2017
	 *  @license	https://opensource.org/licenses/gpl-3.0.html GNU GPL License (GPL)
	 *  @link	http://www.iacademy.cf
	 *  @version 1.0.0
	 *  @filesource
	 */
	
	if(!class_exists('Log')){
        //here the Log class is not yet loaded
        //load it manually
        require_once CORE_LIBRARY_PATH . 'Log.php';
    }
    /**
     * instance of the Log class
     */
    $logger = new Log();

    $logger->setLogger('ApplicationBootstrap');

	/**
	*  inclusion of global constants of the environment that contain : name of the framework,
	*  version, build date, version of PHP required, etc.
	*/
	require_once CORE_PATH . 'constants.php';	
	
	/**
	 *  include file containing useful methods: show_error, 
	 *  exception_handler, error_handler, get_instance, etc.
	 */
	require_once CORE_PATH . 'common.php';
	
	/**
	* Loading "string" helper that contains most of the character 
	* string processing functions : attributes_to_string, get_random_string, etc.
	*/
	require_once CORE_FUNCTIONS_PATH . 'function_string.php';
	
	/**
	* Helper loader "url" which contains most of the URL 
	* processing functions: is_https, is_url, etc.
	*/
	require_once CORE_FUNCTIONS_PATH . 'function_url.php';

	/**
	 *  Definition of the PHP error message handling function
	 */
	set_error_handler('error_handler');

	/*
	* Definition of the PHP error exception handling function
	*/
	set_exception_handler('exception_handler');

	/*
	* Load configurations using the 
	* static method "init" of the Config class.
	* here the Loader class is not instancied so just use require_once
	*/
	require_once CORE_LIBRARY_PATH . 'Config.php';
	Config::init();

	/*
	* Load modules using the 
	* static method "init" of the Module class.
	* here the Loader class is not instancied so just use require_once
	*/
	require_once CORE_LIBRARY_PATH . 'Module.php';
	Module::init();

	$logger->debug('Loading modules configuration ...');
	$cfg = Module::getModulesConfig();
	if($cfg && is_array($cfg)){
		Config::setAll($cfg);
		$logger->info('Configurations for all modules loaded successfully');
	}
	else{
		$logger->info('No configuration found for all modules skip.');
	}

	$logger->debug('Loading Loader library ...');
	/**
	 *  include file containing the class for library loads, 
	 *  functions, models, configuration file, controller
	 */
	require_once CORE_LIBRARY_PATH . 'Loader.php';
	$logger->info('Loader library loaded successfully');

	$logger->debug('Registering PHP autoload function to load the PHP classes automatically');
	/**
	 *  Registration of automatic function of loading resources.  
	 */
	Loader::register();
	$logger->info('PHP autoload function registered successfully');

	$logger->debug('Checking the IP whitelist access...'); 
	Security::checkWhiteListIpAccess();
	
	$logger->info('The application configuration are listed below: ' . stringfy_vars(Config::getAll()));

	$logger->debug('Checking PHP environment ...');	
	/*
	* Verification of the PHP environment: minimum and maximum version
	*/
	if (version_compare(phpversion(), TNH_REQUIRED_PHP_MIN_VERSION, '<')){
		show_error('Your PHP Version ['.phpversion().'] is less than ['.TNH_REQUIRED_PHP_MIN_VERSION.'], please install a new version or update your PHP to the latest.', 'PHP Error environment');	
	}else if(version_compare(phpversion(), TNH_REQUIRED_PHP_MAX_VERSION, '>')){
		show_error('Your PHP Version ['.phpversion().'] is greather than ['.TNH_REQUIRED_PHP_MAX_VERSION.'] please install a PHP version that is compatible.', 'PHP Error environment');	
	}

	$logger->info('PHP environment [' .phpversion(). '] is OK, application can work without any issue');
	

	$logger->info('Everything is OK load Router library and dispatch the request to the corresponding controller');
	/*
	* Routing
	* instantiation of the "Router" class and user request routing processing.
	*/
	$router = new Router();
	$router->dispatch();
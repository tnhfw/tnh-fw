<?php
	defined('ROOT_PATH') || exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework using HMVC architecture
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
	 * The Benchmark class
	 */
	$BENCHMARK =& class_loader('Benchmark');
	
	 /**
     * instance of the Log class
     */
    $LOGGER =& class_loader('Log');

    $LOGGER->setLogger('ApplicationBootstrap');

    $LOGGER->debug('Checking PHP version ...');	
	/*
	* Verification of the PHP environment: minimum and maximum version
	*/
	if (version_compare(phpversion(), TNH_REQUIRED_PHP_MIN_VERSION, '<')){
		show_error('Your PHP Version ['.phpversion().'] is less than ['.TNH_REQUIRED_PHP_MIN_VERSION.'], please install a new version or update your PHP to the latest.', 'PHP Error environment');	
	}
	else if(version_compare(phpversion(), TNH_REQUIRED_PHP_MAX_VERSION, '>')){
		show_error('Your PHP Version ['.phpversion().'] is greather than ['.TNH_REQUIRED_PHP_MAX_VERSION.'] please install a PHP version that is compatible.', 'PHP Error environment');	
	}
	$LOGGER->info('PHP version [' .phpversion(). '] is OK [REQUIRED MINIMUM: ' .TNH_REQUIRED_PHP_MIN_VERSION. ', REQUIRED MAXIMUM: ' .TNH_REQUIRED_PHP_MAX_VERSION. '], application can work without any issue');

	/**
	 *  Definition of the PHP error message handling function
	 */
	set_error_handler('php_error_handler');

	/*
	* Definition of the PHP error exception handling function
	*/
	set_exception_handler('php_exception_handler');

	/**
	 * function handler for shutdown
	 */
	register_shutdown_function('php_shudown_handler');
	
	//if user have some composer package
	$LOGGER->debug('Check for composer autoload');
	if(file_exists(VENDOR_PATH . 'autoload.php')){
		$LOGGER->info('The composer autoload file exists include it');
		require_once VENDOR_PATH . 'autoload.php';
	}
	else{
		$LOGGER->info('The composer autoload file does not exist');
	}
	
	$LOGGER->debug('Begin to load the required resources');

	/**
	* Event 
	*/
	require_once CORE_LIBRARY_PATH . 'Event.php';

	/**
	 * Load the event dispatcher
	 * @var EventDispatcher
	 */
	$DISPATCHER =& class_loader('EventDispatcher');

	$BENCHMARK->mark('CONFIG_INIT_START');
	/*
	* Load configurations using the 
	* static method "init" of the Config class.
	*/
	$CONFIG =& class_loader('Config');	
	$CONFIG->init();
	$BENCHMARK->mark('CONFIG_INIT_END');

	$BENCHMARK->mark('MODULE_INIT_START');
	/*
	* Load modules using the 
	* static method "init" of the Module class.
	*/
	$MODULE =& class_loader('Module');
	$MODULE->init();
	
	$LOGGER->debug('Loading modules configuration ...');
	$cfg = Module::getModulesConfig();
	if($cfg && is_array($cfg)){
		Config::setAll($cfg);
		$LOGGER->info('Configurations for all modules loaded successfully');
	}
	else{
		$LOGGER->info('No configuration found for all modules skipping.');
	}
	$BENCHMARK->mark('MODULE_INIT_END');

	$LOGGER->debug('Loading Base Controller ...');
	/**
	 *  include file containing the Base Controller class 
	 */
	require_once CORE_LIBRARY_PATH . 'Controller.php';
	$LOGGER->info('Base Controller loaded successfully');

	/*
	  Register controller autoload function
	*/
	 spl_autoload_register('autoload_controller');

	if(get_config('cache_enable', false)){
		/**
		 * Cache interface
		 */
		require_once CORE_LIBRARY_PATH . 'CacheInterface.php';
		$CACHE =& class_loader(get_config('cache_handler'));
	}
	
	/*
		Loading Security class
	*/
	$SECURITY =& class_loader('Security');
	$SECURITY->checkWhiteListIpAccess();
	
	$LOGGER->info('Everything is OK load Router library and dispatch the request to the corresponding controller');
	/*
	* Routing
	* instantiation of the "Router" class and user request routing processing.
	*/
	$ROUTER = & class_loader('Router');
	$ROUTER->run();
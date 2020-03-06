<?php
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
	
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);

	/**
	* the directory separator, under windows it is \ and unix, linux /
	*/
	define('DS', DIRECTORY_SEPARATOR);

	/**
	* The root directory of the application.
	*
	* you can place this directory outside of your web directory, for example "/home/your_app", etc.
	*/
	define('ROOT_PATH', dirname(dirname(realpath(__FILE__))) . DS);
    
	//tests dir path
	define('TESTS_PATH', dirname(realpath(__FILE__)) . DS);

	/*
    * NOTE: As the tests is running in cli mode the assets path need to be set to absolute path
    * due to file_exists() will not work with relative path
	*/
	define('ASSETS_PATH', TESTS_PATH . 'assets/');
    
	/**
	* The path to the directory of your cache files.
	*
	* This feature is available currently for database and views.
	*/
	define('CACHE_PATH', ROOT_PATH . 'cache' . DS);

	/**
	* Custom application path for tests 
	*/
	define('APPS_PATH', TESTS_PATH .'app' . DS);

	/**
	* The path to the controller directory of your application.
	*
	* If you already know the MVC architecture you know what a controller means; 
	* it is he who makes the business logic of your application in general.
	*/
	define('APPS_CONTROLLER_PATH', APPS_PATH . 'controllers' . DS);

	/**
	* The path to the directory of your model classes of your application. 
	*
	* If you already know the MVC architecture you know what a model means; 
	* it's the one who interacts with the database, in one word persistent data from your application.
	*/
	define('APPS_MODEL_PATH', APPS_PATH . 'models' . DS);

	/**
	* The path to the directory of your views.
	*
	* If you already know the MVC architecture you know what a view means, 
	* a view is just a user interface (html page, form, etc.) that is to say 
	* everything displayed in the browser interface, etc.
	*/
	define('APPS_VIEWS_PATH', APPS_PATH . 'views' . DS);

	/**
	* The path to the configuration directory.
	*
	* That contains most of the configuration files for your 
	* application (database, class loading file, functions, etc.)
	*/
	define('CONFIG_PATH', APPS_PATH . 'config' . DS);

	/** 
	* The core directory
	*
	* It is recommended to put this folder out of the web directory of your server and 
	* you should not change its content because in case of update you could lose the modified files.
	*/
	define('CORE_PATH', ROOT_PATH . 'core' . DS);
	
	/**
	* The path to the directory of core classes that used by the system.
	*
	* It contains PHP classes that are used by the framework internally.
	*/
	define('CORE_CLASSES_PATH', CORE_PATH . 'classes' . DS);
	
	/**
	* The path to the directory of core classes for the cache used by the system.
	*
	* It contains PHP classes for the cache drivers.
	*/
	define('CORE_CLASSES_CACHE_PATH', CORE_CLASSES_PATH . 'cache' . DS);
	
    /**
	* The path to the directory of core classes for the database used by the system.
	*
	* It contains PHP classes for the database library, drivers, etc.
	*/
	define('CORE_CLASSES_DATABASE_PATH', CORE_CLASSES_PATH . 'database' . DS);
    
	/**
	* The path to the directory of core classes for the model used by the system.
	*
	* It contains PHP classes for the models.
	*/
	define('CORE_CLASSES_MODEL_PATH', CORE_CLASSES_PATH . 'model' . DS);

	/**
	* The path to the directory of functions or helper systems.
	*
	* It contains PHP functions that perform a particular task: character string processing, URL, etc.
	*/
	define('CORE_FUNCTIONS_PATH', CORE_PATH . 'functions' . DS);

	/**
	* The path to the core directory of languages files. 
	*
	*/
	define('CORE_LANG_PATH', CORE_PATH . 'lang' . DS);

	/**
	* The path to the system library directory.
	*
	* Which contains the libraries most often used in your web application, as for the 
	* core directory it is advisable to put it out of the root directory of your application.
	*/
	define('CORE_LIBRARY_PATH', CORE_PATH . 'libraries' . DS);

	/**
	* The path to the system view directory.
	*
	* That contains the views used for the system, such as error messages, and so on.
	*/
	define('CORE_VIEWS_PATH', CORE_PATH . 'views' . DS);
	
	/**
	* The path to the directory of your PHP personal functions or helper.
	*
	* It contains your PHP functions that perform a particular task: utilities, etc.
	* Note: Do not put your personal functions or helpers in the system functions directory, 
	* because if you update the system you may lose them.
	*/
	define('FUNCTIONS_PATH', APPS_PATH . 'functions' . DS);

	/**
	* The path to the app directory of personal language. 
	*
	* This feature is not yet available. 
	* You can help us do this if you are nice or wish to see the developed framework.
	*/
	define('APP_LANG_PATH', APPS_PATH . 'lang' . DS);

	/**
	* The path to the directory of your personal libraries
	*
	* It contains your PHP classes, package, etc.
	* Note: you should not put your personal libraries in the system library directory, 
	* because it is recalled in case of updating the system you might have surprises.
	*/
	define('LIBRARY_PATH', APPS_PATH . 'libraries' . DS);

	/**
	* The path to the directory that contains the log files.
	*
	* Note: This directory must be available in writing and if possible must have as owner the user who launches your web server, 
	* under unix or linux most often with the apache web server it is "www-data" or "httpd" even "nobody" for more
	* details see the documentation of your web server.
	* Example for Unix or linux with apache web server:
	* # chmod -R 700 /path/to/your/logs/directory/
	* # chown -R www-data:www-data /path/to/your/logs/directory/
	*/
	define('LOGS_PATH', APPS_PATH . 'logs' . DS);

	/**
	* The path to the modules directory. 
	*
	* It contains your modules used files (config, controllers, libraries, etc.) that is to say which contains your files of the modules, 
	* in HMVC architecture (hierichical, controllers, models, views).
	*/
	define('MODULE_PATH', APPS_PATH . 'modules' . DS);

	/**
	* The path to the directory of sources external to your application.
	*
	* If you have already used "composer" you know what that means.
	*/
	define('VENDOR_PATH', ROOT_PATH . 'vendor' . DS);

	/**
	* The front controller of your application.
	*
	* "index.php" it is through this file that all the requests come, there is a possibility to hidden it in the url of 
	* your application by using the rewrite module URL of your web server .
	* For example, under apache web server, there is a configuration example file that is located at the root 
	* of your framework folder : "htaccess.txt" rename it to ".htaccess".
	*/
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
	
	/**
	 * Check if user run the application under CLI
	 */
	define('IS_CLI', stripos('cli', php_sapi_name()) !== false);

	/**
	* The environment of your application (production, test, development). 
	*
	* if your application is still in development you use the value "development" 
	* so you will have the display of the error messages, etc. 
	* Once you finish the development of your application that is to put it online 
	* you change this value to "production" or "testing", in this case there will be deactivation of error messages, 
	* the loading of the system, will be fast.
	*/
	define('ENVIRONMENT', 'testing');
	
	
	//Fix to allow test as if application is running in CLI mode $_SESSION global variable is not available
	$_SESSION = array();
	
	//check for composer autoload file if exists include it
	if (file_exists(VENDOR_PATH . 'autoload.php')){
		require_once VENDOR_PATH . 'autoload.php';
		
		//define the class alias for vstream
		class_alias('org\bovigo\vfs\vfsStream', 'vfsStream');
		class_alias('org\bovigo\vfs\vfsStreamDirectory', 'vfsStreamDirectory');
		class_alias('org\bovigo\vfs\vfsStreamWrapper', 'vfsStreamWrapper');
		
	}

	//require autoloader for test
	require_once  'include/autoloader.php';
	
	

	//grap from core/common.php functions and mock some functions for tests
	require_once  'include/common.php';
	
	//Global testcase class
	require_once  'include/TnhTestCase.php';
	
	/**
	* Setting of the PHP error message handling function
	*/
	set_error_handler('php_error_handler');

	/**
	* Setting of the PHP error exception handling function
	*/
	set_exception_handler('php_exception_handler');

	/**
	 * Setting of the PHP shutdown handling function
	 */
	register_shutdown_function('php_shudown_handler');
	
	/**
	* Register the tests autoload
	*/
	spl_autoload_register('tests_autoload');
    
    
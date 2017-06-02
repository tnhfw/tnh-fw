<?php
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


//the directory separator
define('DS', DIRECTORY_SEPARATOR);

//the root path
define('ROOT_PATH', dirname(realpath(__FILE__)).DS);

//the core path
define('CORE_PATH', ROOT_PATH.'core'.DS);

//the core libraries path
define('CORE_LIBRARY_PATH', CORE_PATH.'libraries'.DS);

//the core views path
define('CORE_VIEWS_PATH', CORE_PATH.'views'.DS);

//the config path
define('CONFIG_PATH', ROOT_PATH.'config'.DS);

//the assets path
define('ASSETS_PATH', 'assets/');

//the libraries path
define('LIBRARY_PATH', ROOT_PATH.'libraries'.DS);

//the functions path
define('FUNCTIONS_PATH', ROOT_PATH.'functions'.DS);

//the core functions path
define('CORE_FUNCTIONS_PATH', CORE_PATH.'functions'.DS);


//the vendor path
define('VENDOR_PATH', ROOT_PATH.'vendor'.DS);

//the classes path
define('APPS_PATH', ROOT_PATH.'classes'.DS);

//the controller path
define('APPS_CONTROLLER_PATH', APPS_PATH.'controllers'.DS);

//the lang path
define('LANG_PATH', CORE_PATH.'lang'.DS);

//the model path
define('APPS_MODEL_PATH', APPS_PATH.'models'.DS);

//the views path
define('APPS_VIEWS_PATH', APPS_PATH.'views'.DS);

//the cache path
define('CACHE_PATH', ROOT_PATH.'cache'.DS);

//the logs path
define('LOGS_PATH', ROOT_PATH.'logs'.DS);

//this file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

//environment must be development, testing or production
define('ENVIRONMENT', 'development');



switch (ENVIRONMENT){
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>=')){
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;
	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1);
}
//let's go
require_once CORE_PATH.'bootstrap.php';
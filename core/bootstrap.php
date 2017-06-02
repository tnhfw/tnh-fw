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
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

	/**
	 *  @file constants.php
	 *    
	 *  This file contains the declaration of most of the constants used in the system, 
	 *  for example: the version, the name of the framework, etc.
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
	 *  The framework name
	 */
	define('TNH_NAME', 'TNH Framework');

	/**
	 *  The version of the framework in X.Y.Z format (Major, minor and bugs). 
	 *  If there is the presence of the word "dev", it means that 
	 *  it is a version under development.
	 */
	define('TNH_VERSION', '1.0.0-dev');

	/**
	 *  The date of publication or release of the framework
	 */
	define('TNH_BUILD_DATE', '05/02/2017');

	/**
	 *  The author of the framework, the person who developed the framework.
	 */
	define('TNH_AUTHOR', 'Tony NGUEREZA');

	/**
	 *  Email address of the author of the framework.
	 */
	define('TNH_AUTHOR_EMAIL', 'nguerezatony@gmail.com');

	/**
	 *  The minimum PHP version required to use the framework. 
	 *  If the version of PHP installed is lower, then the application will not work.
	 *  Note: we use the PHP version_compare function to compare the required version with 
	 *  the version installed on your system.
	 */
	define('TNH_REQUIRED_PHP_MIN_VERSION', '5.3');

	/**
	 *  The maximum version of PHP required to use the framework. 
	 *  If the version of PHP installed is higher than the required one, then the application will not work.
	 */
	define('TNH_REQUIRED_PHP_MAX_VERSION', '7.0.0');
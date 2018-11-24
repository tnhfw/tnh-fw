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
	* This file contains the main configuration of your application
	* web address, front-end controller, error logging level, 
	* number of data to be displayed per page, session parameters, etc.
	*/
	
	/*+---------------------------------------------------------------+
	* Basic configuration section
	+------------------------------------------------------------------+
	*/
	
	/**
	* The web address of your application.
	*
	* The address of your application or website terminated by a slash.
	* You can use a domain name or IP address, for example:
	*
	* $config['base_url'] = 'http://www.mysite.com';
	* or
	* $config['base_url'] = 'http://198.15.25.12';
	*
	* If this value is empty, we try to determine it automatically by using 
	* the server variables "SERVER_ADDR" or "SCRIPT_NAME",
	* we recommend that you specify this value for a server in production this may reduce the performance of your application.
	*/
	$config['base_url'] = '';
	

	/**
	* The front controller
	*
	* This represents the name of the file called by the application during the loading 
	* process generally the file "index.php". 
	* If your webserver supports the url rewrite module, then you can leave this value empty. 
	* You will find a sample file to hide this file in the url inside the root folder of your 
	* application "htaccess.txt" for the apache web server just rename it to ".htaccess"
	* 
	* Without the rewrite module url enabled, leave this value to "index.php", in this case your urls look like:
	*
	* http://www.yoursite.com/index.php/controller/method
	* 
	* otherwise if the module is available and activated you can put this value empty and your urls look like:
	*
	* http://www.yoursite.com/controller/method
	*
	*/
	$config['front_controller'] = 'index.php';
	
	/**
	 *  Url suffix
	 */
	$config['url_suffix'] = '';
	
	/**
	 *  site charset
	 */
	$config['charset'] = 'UTF-8';
	
	/**
	* The pagination.
	*
	* Represents the number of data to display per page.
	* Note: this value must be strictly greater than zero (0)
	*/
	$config['pagination_per_page'] = 10;
	

	/*+---------------------------------------------------------------+
	* Language configuration section
	+------------------------------------------------------------------+
	*/
	/**
	 * list of available supported language
	 * array(
	 * 		'lang_key' => 'human readable'
	 * )
	 */
	$config['languages'] = array('en' => 'english');


	/**
	 * the default language to use if can not find the client language
	 * need match with the array key of the supported languages
	 */
	$config['default_language'] = 'en'; //en = english, fr = french

	/**
	 * the name of cookie used to store the client langauge
	 */
	$config['language_cookie_name'] = 'cookie_lang';


	/*+---------------------------------------------------------------+
	* Logs configuration section
	+------------------------------------------------------------------+
	*/
	
	/**
	* The path to log directory
	* 
	* The path that the log data will be saved ending with de "/" or "\", leave empty if you
	* want use the default configuration
	* warning : if set, this directory must exist and will be writable and owned by the web server
	* else the default value will be used i.e the constant LOG_PATH
	* for security raison this directory must be outside of the document root of your
	* website.
	*/	
	$config['log_save_path'] = '';
	
	/** 
	* The log level
	*
	* -1 = do not save log
	* 0 = all logs must be saved
	* 1 = enable log for success only
	* 2 = enable log for info only
	* 3 = enable log for warning only
	* 4 = enable log for error only
	* 5 = enable log for debug only
	*/
	$config['log_level'] = 0;
	
	
	/*+---------------------------------------------------------------+
	* Session configuration section
	+------------------------------------------------------------------+
	*/
	
	/**
	* The session name 
	*
	* By default is PHPSESSID. this must be alpha-numerical characters
	*/
	$config['session_name'] = 'PHPSESSID';
	
	/**
	* Session save path
	*
	* The path that the session data will be saved, leave empty if you
	* want use the default configuration in the php.ini
	* warning : if set, this directory must exist and will be writable and owned by the web server
	* for security raison this directory must be outside of the document root of your
	* website.
	* Note: if the session handler is "database" the session_save_path is the model name to use
	*/
	$config['session_save_path'] = '';

	/**
	* Session handler
	*
	* The session handler that we will use to manage the session.
	* currently the possible values are "files", "database".
	*/
	$config['session_handler'] = 'files';
	
	/**
	* Session secret
	*
	* This is used to hash the session data if the config "session_handler" is set to "database"
	* warning : do not change this value until you already set
	* for security raison use the very complicated value include $%)@^&^\''\'\'
	* NOTE: this value is an base64 so you need use the tool that generate it, like
	*  PHP function base64_encode()
	*/
	$config['session_secret'] = '';

	/**
	 * number of second that consider the session already expire
	 */
	$config['session_inactivity_time'] = 600; //in second

	/**
	* Session cookie lifetime
	*
	* The cookie lifetime that the session will be dropped in seconds, leave 0 if you want
	* the cookie expire after the browser is closed
	*/
	$config['session_cookie_lifetime'] = 0;
	
	/**
	* Session cookie path
	*
	* The path to your website that the cookie is available "/" means all path is available
	* example : /mysubdirectory => available in http://www.mysite.com/mysubdirectory
	*/
	$config['session_cookie_path'] = '/';
	
	/** 
	* Session cookie domain
	*
	* The domain of your website that the cookie is available if you want the cookie is available
	* in all your subdomain use this dot before the domain name for example ".mysite.com".
	* leave empty if you want use the default configuration
	*/
	$config['session_cookie_domain'] = '';
	
	/**
	* Session cookie secure
	* 
	* If your website use SSL i.e https, you set "true" for this configuration, so the cookie
	* is available only if the website use the secure connection else set this value to "false"
	*/
	$config['session_cookie_secure'] = false;
	
	/**
	* Session cookie httponly
	*
	* If you would like the cookie is available only in HTTP mode, then set this value to "true" 
	* else set this value to "false"
	*/
	$config['session_cookie_httponly'] = false;


	/*+---------------------------------------------------------------+
	* CSRF configuration section
	+------------------------------------------------------------------+
	*/
	
	/**
	 * CSRF status
	 *
	 * if you would to use the CSRF (that we recommand you), set this key to true
	 */
	$config['csrf_enable'] = true;

	/**
	 * CSRF key
	 *
	 * the key used to store the csrf data
	 */
	$config['csrf_key'] = 'csrf_key';

	/**
	 * CSRF expire
	 *
	 * expire time in seconds of the CSRF data
	 */
	$config['csrf_expire'] = 120;
	
<?php
	/*+---------------------------------------------------------------+
	* Basic configuration section
	+------------------------------------------------------------------+
	*/
	
	/* your app base url
	* The address of your application or website, terminated by a slash. 
	* If this value is empty then we try to determine it automatically using the server variables (SERVER_ADDR, SCRIPT_NAME), 
	* we recommend you specify this value for a server in production.
	*/
	$config['base_url'] = '';
	

	/* The front controller
	* The front controller. This represents the name of the file called by the application 
	* during the loading process generally the file "index.php". 
	* If your webserver supports the url rewrite module, then you can leave this value empty. 
	* You will find a sample file to hide this file in the url inside the root folder of your 
	* application (htaccess.txt) for the apache web server just rename it to .htaccess 	
	/* the front controller 
	*/
	$config['front_controller'] = 'index.php';
	
	
	/* The pagination
	* Represents the number of data to display per page.
	* Note: this value must be strictly greater than zero (0)
	*/
	$config['pagination_per_page'] = 10;
	
	
	/*+---------------------------------------------------------------+
	* Logs configuration section
	+------------------------------------------------------------------+
	*/
	
	/* the path to log directory
	* the path that the log data will be saved ending with de "/" or "\", leave empty if you
	* want use the default configuration
	* warning : if set, this directory must exist and will be writable and owned by the web server
	* else the default value will be used i.e the constant LOG_PATH
	* for security raison this directory must be outside of the document root of your
	* website.
	*/	
	$config['log_save_path'] = '';
	
	/* the log level
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
	
	/* the session name 
	* by default is PHPSESSID. this must be alpha-numerical characters
	*/
	$config['session_name'] = 'PHPSESSID';
	
	/* session save path
	* the path that the session data will be saved, leave empty if you
	* want use the default configuration in the php.ini
	* warning : if set, this directory must exist and will be writable and owned by the web server
	* for security raison this directory must be outside of the document root of your
	* website.
	*/
	$config['session_save_path'] = '';
	
	/* session cookie lifetime
	* the cookie lifetime that the session will be dropped in seconds, leave 0 if you want
	* the cookie expire after the browser is closed
	*/
	$config['session_cookie_lifetime'] = 0;
	
	/* session cookie path
	* the path to your website that the cookie is available "/" means all path is available
	* example : /mysubdirectory => available in http://www.mysite.com/mysubdirectory
	*/
	$config['session_cookie_path'] = '/';
	
	/* session cookie domain
	* the domain of your website that the cookie is available if you want the cookie is available
	* in all your subdomain use this dot before the domain name for example ".mysite.com".
	* leave empty if you want use the default configuration
	*/
	$config['session_cookie_domain'] = '';
	
	/* session cookie secure
	* if your website use SSL i.e https, you set "true" for this configuration, so the cookie
	* is available only if the website use the secure connection else set this value to "false"
	*/
	$config['session_cookie_secure'] = false;
	
	/* session cookie httponly
	* if you would like the cookie is available only in HTTP mode, then set this value to "true" 
	* else set this value to "false"
	*/
	$config['session_cookie_httponly'] = false;
	
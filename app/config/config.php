<?php
    defined('ROOT_PATH') || exit('Access denied');
    /**
     * TNH Framework
     *
     * A simple PHP framework using HMVC architecture
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2017 TNH Framework
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in all
     * copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
     * SOFTWARE.
     */
	
    /**
     * This file contains the main configuration of your application
     * web address, front controller, error logging, session parameters, CSRF, Cache, 
     * Whitelist IP access, etc.
     */
	
    /*+----------------------------------------------------------------+
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
     * Compress the output before send to browser
     *
     * Enables Gzip output compression for faster page loads.  When enabled,
     * the Response class will test whether your server supports Gzip.
     * Even if it does, however, not all browsers support compression
     * so enable only if you are reasonably sure your visitors can handle it.
     *
     * This is only used if "zlib.output_compression" is turned off in your php configuration.
     * Please do not use it together with httpd-level output compression.
     *
     * IMPORTANT NOTE:  If you are getting a blank page when compression is enabled it
     * means you are prematurely outputting something to your browser. It could
     * even be a line of whitespace at the end of one of your scripts. For
     * compression to work, nothing can be sent before the output buffer is called
     * by the Response class.  Do not 'echo' any values with compression enabled.
     */
    $config['compress_output'] = false;

    /*+----------------------------------------------------------------+
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
     * the name of cookie used to store the client language
     */
    $config['language_cookie_name'] = 'cookie_lang';


    /*+----------------------------------------------------------------+
	* Logs configuration section
	+------------------------------------------------------------------+
	*/
	
    /** 
     * The log level
     *
     * The valid level are: OFF, NONE, FATAL, ERROR, WARNING, WARN, INFO, DEBUG, ALL
     *
     * 'OFF' or 'NONE' = do not save log
     * 'FATAL' = enable log for fatal level and above (FATAL)
     * 'ERROR' = enable log for error level and above (ERROR, FATAL)
     * 'WARNING' or WARN = enable log for warning level and above (WARNING, ERROR, FATAL)
     * 'INFO' = enable log for info level and above (INFO, WARNING, ERROR, FATAL)
     * 'DEBUG' = enable log for debug level and above (DEBUG, INFO, WARNING, ERROR, FATAL)
     * 'ALL' = enable log for all level
     *
     * The default value is NONE if the config value is: null, '', 0, false
     * 
     * Note: in production environment it's recommand to set the log level to 'WARNING' if not in small
     * of time the log file size will increase very fast and will cost the application performance
     * and also the filesystem usage of your server.
     */
    $config['log_level'] = 'NONE';


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
     * The logger name to use for the log
     * 
     * If this config is set so means only log message with this or these logger(s) will be saved
     *
     * Example:
     * $config['log_logger_name'] = array('MY_LOGGER1', 'MY_LOGGER2'); //only log message with MY_LOGGER1 or MY_LOGGER2 will be saved in file.
     */	
    $config['log_logger_name'] = array();

    /**
    * The logger name custom level to use for the log
    * 
    * If this config is set so means the logger level will be used to overwrite 
    * the default log level configuration above. 
    *
    * Example:
    * $config['log_logger_name_level'] = array('MY_LOGGER1' => 'WARNING'); 
    * So if $config['log_level'] = 'ERROR' but all log messages with "MY_LOGGER1" as logger name
    *  will be saved for WARNING message and above
    *  Note: You can also use an regular expression for the logger name.
    *  Example:
    *  $config['log_logger_name_level'] = array('^Class::Con(.*)' => 'info');
    *  So all logger name like "Class::Config", "Class::Cookie", etc. will be match
    */  
    $config['log_logger_name_level'] = array();
	
	
    /*+----------------------------------------------------------------+
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
     * Session handler
     *
     * The session handler that we will use to manage the session.
     * currently the possible values are "files", "database".
     */
    $config['session_handler'] = 'files';
	
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
	

    /*+----------------------------------------------------------------+
	* CSRF configuration section
	+------------------------------------------------------------------+
	*/
	
    /**
     * CSRF status
     *
     * if you would to use the CSRF (that we recommand you), set this key to true
     */
    $config['csrf_enable'] = false;

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
	
	
    /*+----------------------------------------------------------------+
	* Cache configuration section
	+------------------------------------------------------------------+
	*/
	
    /**
     * Cache status
     *
     * If you would to use the cache functionnality set this value to true
     */
    $config['cache_enable'] = false;
	
    /**
     * Cache Time To Live
     *
     * expire time in seconds of the cache data
     */
    $config['cache_ttl'] = 120; //in second

    /**
     * Cache handler class
     *
     * The cache handler class inside (CORE_CLASSES_CACHE_PATH, LIBRARY_PATH) directories that implements 
     * the interface "CacheInterface" that we will use to manage the cache.
     * currently the possible values are "FileCache", "ApcCache".
     */
    $config['cache_handler'] = 'FileCache';
	
	
    /*+----------------------------------------------------------------+
	* White list IP access configuration section
	+------------------------------------------------------------------+
	*/
	
    /**
     * White list ip status
     *
     * if you would to use the white list ip access, set this key to true
     */
    $config['white_list_ip_enable'] = false;
	
    /**
     * White listed ip addresses
     *
     * add the allowed ip address list to access this application.
     * You can use the wildcard address
     * @example: '18.90.09.*', '10.*.*.*', '*'
     * 
     */
    $config['white_list_ip_addresses'] = array('127.0.0.1', '::1');

    

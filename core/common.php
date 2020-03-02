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
     *  @file common.php
     *  
     *  Contains most of the commons functions used by the system
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
     * This function is the class loader helper is used if the library "Loader" not yet loaded
     * he load the class once
     * @param  string $class  the class name to be loaded
     * @param  string $dir    the directory where to find the class
     * @param  mixed $params the parameter to pass as argument to the constructor of the class
     * @codeCoverageIgnore
     * 
     * @return object         the instance of the loaded class
     */
    function & class_loader($class, $dir = 'libraries', $params = null){
        //put the first letter of class to upper case 
        $class = ucfirst($class);
        static $classes = array();
        if (isset($classes[$class]) /*hack for duplicate log Logger name*/ && $class != 'Log') {
            return $classes[$class];
        }
        $found = false;
        foreach (array(APPS_PATH, CORE_PATH) as $path) {
            $file = $path . $dir . DS . $class . '.php';
            if (file_exists($file)) {
                if (class_exists($class, false) === false) {
                    require_once $file;
                }
                //already found
                $found = true;
                break;
            }
        }
        if (!$found) {
            //can't use show_error() at this time because some dependencies not yet loaded
            set_http_status_header(503);
            echo 'Cannot find the class [' . $class . ']';
            die();
        }
		
        /*
		   TODO use the best method to get the Log instance
		 */
        if ($class == 'Log') {
            //can't use the instruction like "return new Log()" 
            //because we need return the reference instance of the loaded class.
            $log = new Log();
            return $log;
        }
        //track of loaded classes
        class_loaded($class);
		
        //record the class instance
        $classes[$class] = isset($params) ? new $class($params) : new $class();
		
        return $classes[$class];
    }

    /**
     * This function is the helper to record the loaded classes
     * @param  string $class the loaded class name
     * @codeCoverageIgnore
     * 
     * @return array        the list of the loaded classes
     */
    function & class_loaded($class = null){
        static $list = array();
        if ($class !== null) {
            $list[strtolower($class)] = $class;
        }
        return $list;
    }

    /**
     * This function is used to load the configurations in the case the "Config" library not yet loaded
     * @param  array  $overwrite_values if need overwrite the existing configuration
     * @codeCoverageIgnore
     * 
     * @return array                   the configurations values
     */
    function & load_configurations(array $overwrite_values = array()){
        static $config;
        if (empty($config)) {
            $file = CONFIG_PATH . 'config.php';
            $found = false;
            if (file_exists($file)) {
                require_once $file;
                $found = true;
            }
            if (!$found) {
                set_http_status_header(503);
                echo 'Unable to find the configuration file [' . $file . ']';
                die();
            }
        }
        foreach ($overwrite_values as $key => $value) {
            $config[$key] = $value;
        }
        return $config;
    }

    /**
     * This function is the helper to get the config value in case the "Config" library not yet loaded
     * @param  string $key     the config item to get the vale
     * @param  mixed $default the default value to return if can't find the config item in the configuration
     * @test
     * 
     * @return mixed          the config value
     */
    function get_config($key, $default = null) {
        static $cfg;
        if (empty($cfg)) {
            $cfg[0] = & load_configurations();
            if(! is_array($cfg[0])){
                $cfg[0] = array();
            }
        }
        return array_key_exists($key, $cfg[0]) ? $cfg[0][$key] : $default;
    }

    /**
     * This function is a helper to logging message
     * @param  string $level   the log level "ERROR", "DEBUG", "INFO", etc.
     * @param  string $message the log message to be saved
     * @param  string $logger  the logger to use if is set
     * 
     * @codeCoverageIgnore
     */
    function save_to_log($level, $message, $logger = null) {
        $log = & class_loader('Log', 'classes');
        if ($logger) {
            $log->setLogger($logger);
        }
        $log->writeLog($message, $level);
    }

    /**
     * Set the HTTP status header
     * @param integer $code the HTTP status code
     * @param string  $text the HTTP status text
     * 
     * @codeCoverageIgnore
     */
    function set_http_status_header($code = 200, $text = null) {
        if (empty($text)) {
            $http_status = array(
                                100 => 'Continue',
                                101 => 'Switching Protocols',
                                200 => 'OK',
                                201 => 'Created',
                                202 => 'Accepted',
                                203 => 'Non-Authoritative Information',
                                204 => 'No Content',
                                205 => 'Reset Content',
                                206 => 'Partial Content',
                                300 => 'Multiple Choices',
                                301 => 'Moved Permanently',
                                302 => 'Found',
                                303 => 'See Other',
                                304 => 'Not Modified',
                                305 => 'Use Proxy',
                                307 => 'Temporary Redirect',
                                400 => 'Bad Request',
                                401 => 'Unauthorized',
                                402 => 'Payment Required',
                                403 => 'Forbidden',
                                404 => 'Not Found',
                                405 => 'Method Not Allowed',
                                406 => 'Not Acceptable',
                                407 => 'Proxy Authentication Required',
                                408 => 'Request Timeout',
                                409 => 'Conflict',
                                410 => 'Gone',
                                411 => 'Length Required',
                                412 => 'Precondition Failed',
                                413 => 'Request Entity Too Large',
                                414 => 'Request-URI Too Long',
                                415 => 'Unsupported Media Type',
                                416 => 'Requested Range Not Satisfiable',
                                417 => 'Expectation Failed',
                                418 => 'I\'m a teapot',
                                500 => 'Internal Server Error',
                                501 => 'Not Implemented',
                                502 => 'Bad Gateway',
                                503 => 'Service Unavailable',
                                504 => 'Gateway Timeout',
                                505 => 'HTTP Version Not Supported',
                            );
            if (isset($http_status[$code])) {
                $text = $http_status[$code];
            } else {
                show_error('No HTTP status text found for your code please check it.');
            }
        }
		
        if (strpos(php_sapi_name(), 'cgi') === 0) {
            header('Status: ' . $code . ' ' . $text, TRUE);
        } else {
            $proto = 'HTTP/1.1';
            if (isset($_SERVER['SERVER_PROTOCOL'])) {
                $proto = $_SERVER['SERVER_PROTOCOL'];
            }
            header($proto . ' ' . $code . ' ' . $text, TRUE, $code);
        }
    }

    /**
     *  This function displays an error message to the user and ends the execution of the script.
     *  
     *  @param string $msg the message to display
     *  @param string $title the message title: "error", "info", "warning", etc.
     *  @param boolean $logging either to save error in log
     *  
     *  @codeCoverageIgnore
     */
    function show_error($msg, $title = 'error', $logging = true) {
        $title = strtoupper($title);
        $data = array();
        $data['error'] = $msg;
        $data['title'] = $title;
        if ($logging) {
            save_to_log('error', '[' . $title . '] ' . strip_tags($msg), 'GLOBAL::ERROR');
        }
        $response = & class_loader('Response', 'classes');
        $response->sendError($data);
        die();
    }

    /**
     *  Check whether the protocol used is "https" or not
     *  That is, the web server is configured to use a secure connection.
     *  @codeCoverageIgnore
     *  
     *  @return boolean true if the web server uses the https protocol, false if not.
     */
    function is_https() {
        /*
		* some servers pass the "HTTPS" parameter in the server variable,
		* if is the case, check if the value is "on", "true", "1".
		*/
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        if (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }
        return false;
    }
	
    /**
     *  This function is used to check the URL format of the given string argument. 
     *  The address is valid if the protocol is http, https, ftp, etc.
     *
     *  @param string $url the URL address to check
     *  @test
     *  
     *  @return boolean true if is a valid URL address or false.
     */
    function is_url($url) {
        return preg_match('/^(http|https|ftp):\/\/(.*)/', $url) == 1;
    }
	
    /**
     *  Function defined to load controller
     *  
     *  @param string $controllerClass the controller class name to be loaded
     *  @codeCoverageIgnore
     */
    function autoload_controller($controllerClass) {
        if (file_exists($path = APPS_CONTROLLER_PATH . $controllerClass . '.php')) {
            require_once $path;
        }
    }
	
    /**
     *  Function defined for handling PHP exception error message, 
     *  it displays an error message using the function "show_error"
     *  
     *  @param object $ex instance of the "Exception" class or a derived class
     *  @codeCoverageIgnore
     *  
     *  @return boolean
     */
    function php_exception_handler($ex) {
        if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors'))) {
            show_error('An exception is occured in file ' . $ex->getFile() . ' at line ' . $ex->getLine() . ' raison : ' . $ex->getMessage(), 'PHP Exception #' . $ex->getCode());
        } else {
            save_to_log('error', 'An exception is occured in file ' . $ex->getFile() . ' at line ' . $ex->getLine() . ' raison : ' . $ex->getMessage(), 'PHP Exception');
        }
        return true;
    }
	
    /**
     *  Function defined for PHP error message handling
     *  			
     *  @param int $errno the type of error for example: E_USER_ERROR, E_USER_WARNING, etc.
     *  @param string $errstr the error message
     *  @param string $errfile the file where the error occurred
     *  @param int $errline the line number where the error occurred
     *  @codeCoverageIgnore
     *  
     *  @return boolean	
     */
    function php_error_handler($errno, $errstr, $errfile, $errline) {
        $isError = (((E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $errno) === $errno);
        if ($isError) {
            set_http_status_header(500);
        }
        if (!(error_reporting() & $errno)) {
            save_to_log('error', 'An error is occurred in the file ' . $errfile . ' at line ' . $errline . ' raison : ' . $errstr, 'PHP ERROR');
            return;
        }
        if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors'))) {
            $errorType = 'error';
            switch ($errno) {
                case E_USER_WARNING:
                    $errorType = 'warning';
                    break;
                case E_USER_NOTICE:
                    $errorType = 'notice';
                    break;
            }
            show_error('An error is occurred in the file <b>' . $errfile . '</b> at line <b>' . $errline . '</b> raison : ' . $errstr, 'PHP ' . $errorType);
        }
        if ($isError) {
            die();
        }
        return true;
    }

    /**
     * This function is used to run in shutdown situation of the script
     * @codeCoverageIgnore
     */
    function php_shudown_handler() {
        $lastError = error_get_last();
        if (isset($lastError) &&
            ($lastError['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))) {
            php_error_handler($lastError['type'], $lastError['message'], $lastError['file'], $lastError['line']);
        }
    }


    /**
     *  Convert array attributes to string
     *
     *  This function converts an associative array into HTML attributes.
     *  For example :
     *  $a = array('name' => 'Foo', 'type' => 'text'); => produces the following string:
     *  name = "Foo" type = "text"
     *
     *  @param array $attributes associative array to convert to a string attribute.
     *   
     *  @return string string of the HTML attribute.
     */
    function attributes_to_string(array $attributes) {
        $str = ' ';
        //we check that the array passed as an argument is not empty.
        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                $key = trim(htmlspecialchars($key));
                $value = trim(htmlspecialchars($value));
                /*
				* To predict the case where the string to convert contains the character "
				* we check if this is the case we add a slash to solve this problem.
				* For example:
				* 	$attr = array('placeholder' => 'I am a "puple"')
				* 	$str = attributes_to_string($attr); => placeholder = "I am a \"puple\""
				 */
                if ($value && strpos('"', $value) !== false) {
                    $value = addslashes($value);
                }
                $str .= $key . ' = "' . $value . '" ';
            }
        }
        //remove the space after using rtrim()
        return rtrim($str);
    }


    /**
     * Function to stringfy PHP variable, useful in debug situation
     *
     * @param mixed $var the variable to stringfy
     * @codeCoverageIgnore
     *
     * @return string the stringfy value
     */
    function stringfy_vars($var) {
        return print_r($var, true);
    }

    /**
     * Clean the user input
     * @param  mixed $str the value to clean
     * @test
     * 
     * @return mixed   the sanitize value
     */
    function clean_input($str) {
        if (is_array($str)) {
            $str = array_map('clean_input', $str);
        } else if (is_object($str)) {
            $obj = $str;
            foreach ($str as $var => $value) {
                $obj->$var = clean_input($value);
            }
            $str = $obj;
        } else {
            $str = htmlspecialchars(strip_tags($str), ENT_QUOTES, 'UTF-8');
        }
        return $str;
    }
	
    /**
     * This function is used to hidden some part of the given string. Helpful if you need hide some confidential 
     * Information like credit card number, password, etc.
     *
     * @param  string $str the string you want to hide some part
     * @param  int $startCount the length of non hidden for the beginning char
     * @param  int $endCount the length of non hidden for the ending char
     * @param  string $hiddenChar the char used to hide the given string
     * @test
     * 
     * @return string the string with the hidden part.
     */
    function string_hidden($str, $startCount = 0, $endCount = 0, $hiddenChar = '*') {
        //get the string length
        $len = strlen($str);
        //if str is empty
        if ($len <= 0) {
            return str_repeat($hiddenChar, 6);
        }
        //if the length is less than startCount and endCount
        //or the startCount and endCount length is 0
        //or startCount is negative or endCount is negative
        //return the full string hidden
		
        if ((($startCount + $endCount) > $len) || ($startCount == 0 && $endCount == 0) || ($startCount < 0 || $endCount < 0)) {
            return str_repeat($hiddenChar, $len);
        }
        //the start non hidden string
        $startNonHiddenStr = substr($str, 0, $startCount);
        //the end non hidden string
        $endNonHiddenStr = null;
        if ($endCount > 0) {
            $endNonHiddenStr = substr($str, - $endCount);
        }
        //the hidden string
        $hiddenStr = str_repeat($hiddenChar, $len - ($startCount + $endCount));
		
        return $startNonHiddenStr . $hiddenStr . $endNonHiddenStr;
    }
	
    /**
     * This function is used to set the initial session config regarding the configuration
     * @codeCoverageIgnore
     */
    function set_session_config() {
        //$_SESSION is not available on cli mode 
        if (!IS_CLI) {
            $logger = & class_loader('Log', 'classes');
            $logger->setLogger('PHPSession');
            //set session params
            $sessionHandler = get_config('session_handler', 'files'); //the default is to store in the files
            $sessionName = get_config('session_name');
            if ($sessionName) {
                session_name($sessionName);
            }
            $logger->info('Session handler: ' . $sessionHandler);
            $logger->info('Session name: ' . $sessionName);

            if ($sessionHandler == 'files') {
                $sessionSavePath = get_config('session_save_path');
                if ($sessionSavePath) {
                    if (!is_dir($sessionSavePath)) {
                        mkdir($sessionSavePath, 1773);
                    }
                    session_save_path($sessionSavePath);
                    $logger->info('Session save path: ' . $sessionSavePath);
                }
            } else if ($sessionHandler == 'database') {
                //load database session handle library
                //Database Session handler Model
                require_once CORE_CLASSES_MODEL_PATH . 'DBSessionHandlerModel.php';

                $DBS = & class_loader('DBSessionHandler', 'classes');
                session_set_save_handler($DBS, true);
                $logger->info('session save path: ' . get_config('session_save_path'));
            } else {
                show_error('Invalid session handler configuration');
            }
            $lifetime = get_config('session_cookie_lifetime', 0);
            $path = get_config('session_cookie_path', '/');
            $domain = get_config('session_cookie_domain', '');
            $secure = get_config('session_cookie_secure', false);
            if (is_https()) {
                $secure = true;
            }
            session_set_cookie_params(
                $lifetime,
                $path,
                $domain,
                $secure,
                $httponly = true /*for security for access to cookie via javascript or XSS attack*/
            );
            //to prevent attack of Session Fixation 
            //thank to https://www.phparch.com/2018/01/php-sessions-in-depth/
            ini_set('session.use_strict_mode ', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_trans_sid ', 0);
			
            $logger->info('Session lifetime: ' . $lifetime);
            $logger->info('Session cookie path: ' . $path);
            $logger->info('Session domain: ' . $domain);
            $logger->info('Session is secure: ' . ($secure ? 'TRUE' : 'FALSE'));
			
            if ((function_exists('session_status') && session_status() !== PHP_SESSION_ACTIVE) || !session_id()) {
                $logger->info('Session not yet start, start it now');
                session_start();
            }
        }
    }
	
    /**
     * This function is very useful, it allows to recover the instance of the global controller.
     * Note this function always returns the address of the super instance.
     * For example :
     * $obj = & get_instance();
     * 
     * @codeCoverageIgnore
     *  
     * @return object the instance of the "Controller" class
     */
    function & get_instance(){
        return Controller::get_instance();
    }

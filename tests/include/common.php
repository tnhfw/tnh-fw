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

	function & class_loader($class, $dir = 'libraries', $params = null){
		//put the first letter of class to upper case 
		$class = ucfirst($class);
		static $classes = array();
		if(isset($classes[$class]) /*hack for duplicate log Logger name*/ && $class != 'Log'){
			return $classes[$class];
		}
		$found = false;
		foreach (array(APPS_PATH, CORE_PATH) as $path) {
			$file = $path . $dir . DS . $class . '.php';
			if(file_exists($file)){
				if(class_exists($class, false) === false){
					require_once $file;
				}
                //already found
				$found = true;
				break;
			}
		}
		if(! $found){
			//can't use show_error() at this time because some dependencies not yet loaded
			set_http_status_header(503);
			echo 'Cannot find the class [' . $class . ']';
			die();
		}
		
		/*
		   TODO use the best method to get the Log instance
		 */
		if($class == 'Log'){
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


	function & class_loaded($class = null){
		static $list = array();
		if($class != null){
			$list[strtolower($class)] = $class;
		}
		return $list;
	}

	function & load_configurations(array $overwrite_values = array()){
		static $config;
		if(empty($config)){
			$file = CONFIG_PATH . 'config.php';
            if (file_exists($file)) {
                require_once $file;
            }		
			foreach ($overwrite_values as $key => $value) {
				$config[$key] = $value;
			}
		}
		return $config;
	}

	/**
	*  @test
	*/
	function get_config($key, $default = null){
		static $cfg;
		if (empty($cfg)) {
            $cfg[0] = & load_configurations();
            if(! is_array($cfg[0])){
                $cfg[0] = array();
            }
        }
		return array_key_exists($key, $cfg[0]) ? $cfg[0][$key] : $default;
	}

	function save_to_log($level, $message, $logger = null){
		echo 'save_to_log('.$level . ',' . $message . ',' . $logger . ")\n";
	}

	
	function set_http_status_header($code = 200, $text = null){
		return true;
	}

	
	function show_error($msg, $title = 'error', $logging = true){
		//show only and continue to help track of some error occured
		//echo 'show_error(' . $msg . ', ' . $title . ', ' . ($logging ? 'Y' : 'N') . ")\n";
        return true;
	}
    
    function fw_exception_handler($ex){
		//show only and continue to help track of some error occured
		//echo 'fw_exception_handler('.$ex->getMessage().', '.$ex->getFile().', '.$ex->getLine() . ")\n";
        return true;
	}
	
	
	function fw_error_handler($errno , $errstr, $errfile , $errline){
		//show only and continue to help track of some error occured
        //echo 'fw_error_handler('.$errno .', ' . $errstr.', ' . $errfile.', '.$errline . ")\n";
        return true;
	}

	function fw_shudown_handler(){
		return true;
	}


	function is_https(){
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
	*  @test
	*/
	function is_url($url){
		return preg_match('/^(http|https|ftp):\/\/(.*)/', $url);
	}

	/**
	*  @test
	*/
	function attributes_to_string(array $attributes){
		$str = ' ';
		//we check that the array passed as an argument is not empty.
		if(! empty($attributes)){
			foreach($attributes as $key => $value){
				$key = trim(htmlspecialchars($key));
				$value = trim(htmlspecialchars($value));
				/*
				* To predict the case where the string to convert contains the character "
				* we check if this is the case we add a slash to solve this problem.
				* For example:
				* 	$attr = array('placeholder' => 'I am a "puple"')
				* 	$str = attributes_to_string($attr); => placeholder = "I am a \"puple\""
				 */
				if($value && strpos('"', $value) !== false){
					$value = addslashes($value);
				}
				$str .= $key.' = "'.$value.'" ';
			}
		}
		//remove the space after using rtrim()
		return rtrim($str);
	}

	function stringfy_vars($var){
		return print_r($var, true);
	}

	/**
	*  @test
	*/
	function clean_input($str){
		if(is_array($str)){
			$str = array_map('clean_input', $str);
		}
		else if(is_object($str)){
			$obj = $str;
			foreach ($str as $var => $value) {
				$obj->$var = clean_input($value);
			}
			$str = $obj;
		}
		else{
			$str = htmlspecialchars(strip_tags($str), ENT_QUOTES, 'UTF-8');
		}
		return $str;
	}
	
	/**
	*  @test
	*/
	function string_hidden($str, $startCount = 0, $endCount = 0, $hiddenChar = '*'){
		//get the string length
		$len = strlen($str);
		//if str is empty
		if($len <= 0){
			return str_repeat($hiddenChar, 6);
		}
		//if the length is less than startCount and endCount
		//or the startCount and endCount length is 0
		//or startCount is negative or endCount is negative
		//return the full string hidden
		
		if((($startCount + $endCount) > $len) || ($startCount == 0 && $endCount == 0) || ($startCount < 0 || $endCount < 0)){
			return str_repeat($hiddenChar, $len);
		}
		//the start non hidden string
		$startNonHiddenStr = substr($str, 0, $startCount);
		//the end non hidden string
		$endNonHiddenStr = null;
		if($endCount > 0){
			$endNonHiddenStr = substr($str, - $endCount);
		}
		//the hidden string
		$hiddenStr = str_repeat($hiddenChar, $len - ($startCount + $endCount));
		
		return $startNonHiddenStr . $hiddenStr . $endNonHiddenStr;
	}
	
	function set_session_config(){
		return true;
	}
	
	function & get_instance(){
		if(! Controller::get_instance()){
			$c = new Controller();
			return $c;
		}
		return Controller::get_instance();
	}

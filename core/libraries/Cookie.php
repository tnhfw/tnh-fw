<?php
	class Cookie{

		public function __construct(){
			
		}


		static function get($item, $default = null){
			return isset($_COOKIE[$item])?($_COOKIE[$item]):$default;
		}

		static function set($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false){
			if(headers_sent()){
				show_error('There exists a cookie that we wanted to create that we couldn\'t 
							create because headers was already sent. Make sure to do the first 
							before outputing anything.');
			}
			$timestamp = $expire;
			if($expire){
				$timestamp = time() + $expire;
			}
			setcookie($name, $value, $timestamp, $path, $domain, $secure, $httponly);
		}


		static function clear($name){
			static::set($name, '');
		}

		static function exists($item){
			return isset($_COOKIE[$item]);
		}

	}

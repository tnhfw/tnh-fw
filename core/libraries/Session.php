<?php
	class Session{

		const SESSION_FLASH_KEY = 'session_flash';

		public function __construct(){
			$session_name = Config::get('session_name');
			if($session_name){
				session_name($session_name);
			}
			$session_save_path = Config::get('session_save_path');
			if($session_save_path){
				session_save_path($session_save_path);
			}
			$lifetime = Config::get('session_cookie_lifetime', 0);
			$path = Config::get('session_cookie_path', '/');
			$domain = Config::get('session_cookie_domain', '');
			$secure = Config::get('session_cookie_secure', false);
			$httponly = Config::get('session_cookie_httponly', false);
			session_set_cookie_params(
				$lifetime,
				$path,
				$domain,
				$secure,
				$httponly
			);
			if((function_exists('session_status') && session_status() !== PHP_SESSION_ACTIVE) || !session_id()){
				session_start();
			}
		}


		static function get($item, $default = null){
			return isset($_SESSION[$item])?($_SESSION[$item]):$default;
		}

		static function set($item, $value){
			$_SESSION[$item] = $value;
		}

		static function getFlash($item, $default = null){
			$key = self::SESSION_FLASH_KEY.'_'.$item;
			$return = isset($_SESSION[$key])?
			($_SESSION[$key]):$default;
			if(isset($_SESSION[$key])){
				unset($_SESSION[$key]);
			}
			return $return;
		}

		static function hasFlash($item){
			$key = self::SESSION_FLASH_KEY.'_'.$item;
			return isset($_SESSION[$key]);
		}

		static function setFlash($item, $value){
			$key = self::SESSION_FLASH_KEY.'_'.$item;
			$_SESSION[$key] = $value;
		}

		static function clear($item){
			unset($_SESSION[$item]);
		}

		static function exists($item){
			return isset($_SESSION[$item]);
		}

		static function clearAll(){
			session_unset();
			session_destroy();
		}

	}

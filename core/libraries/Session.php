<?php
	class Session{

		const SESSION_FLASH_KEY = 'session_flash';

		public function __construct(){
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

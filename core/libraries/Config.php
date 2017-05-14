<?php

	class Config{
		private static $config = array();

		static function init(){
			if(file_exists(CONFIG_PATH.'config.php')){
				require_once CONFIG_PATH.'config.php';
				if(!empty($config) && is_array($config)){
					static::$config = $config;
				}
				else{
					show_error('No configuration found in config.php');
				}
			}
			else{
				show_error('Unable to find the configuration file');
			}

			if(!static::$config['base_url'] || !is_url(static::$config['base_url'])){
				$base_url = null;
				if (isset($_SERVER['SERVER_ADDR'])){
					if (strpos($_SERVER['SERVER_ADDR'], ':') !== FALSE){
						$base_url = '['.$_SERVER['SERVER_ADDR'].']';
					}
					else{
						$base_url = $_SERVER['SERVER_ADDR'];
					}

					$base_url = (is_https() ? 'https' : 'http').'://'.$base_url
						.substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
				}
				else{
					$base_url = 'http://localhost/';
				}
				self::set('base_url', $base_url);
			}
			else{
				static::$config['base_url'] = rtrim(static::$config['base_url'], '/').'/';
			}

		}

		static function get($item, $default = null){
			return isset(static::$config[$item])?(static::$config[$item]):$default;
		}

		static function set($item, $value){
			static::$config[$item] = $value;
		}

		static function getAll(){
			return static::$config;
		}

		static function setAll(array $config = array()){
			static::$config = array_merge(static::$config, $config);
		}
	}

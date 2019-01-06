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
	 * TODO: use the best way to include the Log class
	 */
	if(!class_exists('Log')){
		//here the Log class is not yet loaded
		//load it manually, normally the class Config is loaded before
		require_once CORE_LIBRARY_PATH . 'Log.php';
	}

	class Config{
		private static $config = array();

		private static $logger;


		private static function getLogger(){
			if(static::$logger == null){
				static::$logger = new Log();
				static::$logger->setLogger('Library::Config');
			}
			return static::$logger;
		}

		static function init(){
			$logger = static::getLogger();
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
				$logger->warning('Application base URL is not set or invalid, please set application base URL to increase the application loading time');
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
					$logger->warning('can not determine the application base URL automatically, use localhost as default');
					$base_url = 'http://localhost/';
				}
				self::set('base_url', $base_url);
			}
			static::$config['base_url'] = rtrim(static::$config['base_url'], '/').'/';
			$logger->info('Configuration initialized successfully');
		}

		static function get($item, $default = null){
			$logger = static::getLogger();
			if(isset(static::$config[$item])){
				return static::$config[$item];
			}
			$logger->warning('cannot find config item ['.$item.'] using the default value ['.$default.']');
			return $default;
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

		static function delete($item){
			$logger = static::getLogger();
			if(isset(static::$config[$item])){
				$logger->info('delete config item ['.$item.' => '.static::$config[$item].']');
				unset(static::$config[$item]);
			}
			else{
				$logger->warning('config item ['.$item.'] to be deleted does not exists');
			}
		}

		static function load($config){
			Loader::config($config);
		}
	}

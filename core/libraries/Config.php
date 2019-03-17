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

	class Config{
		/**
		 * The list of loaded configuration
		 * @var array
		 */
		private static $config = array();

		/**
		 * The logger instance
		 * @var Log
		 */
		private static $logger;

		/**
		 * The signleton of the logger
		 * @return Object the Log instance
		 */
		private static function getLogger(){
			if(static::$logger == null){
				static::$logger[0] =& class_loader('Log');
				static::$logger[0]->setLogger('Library::Config');
			}
			return static::$logger[0];
		}

		/**
		 * Initialize the configuration by loading all the configuration from config file
		 */
		public static function init(){
			$logger = static::getLogger();
			$logger->debug('Initialization of the configuration');
			static::$config = & load_configurations();
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
					$logger->warning('Can not determine the application base URL automatically, use http://localhost as default');
					$base_url = 'http://localhost/';
				}
				self::set('base_url', $base_url);
			}
			static::$config['base_url'] = rtrim(static::$config['base_url'], '/').'/';
			if(ENVIRONMENT == 'production' && (strtolower(static::$config['log_level']) == 'debug' || strtolower(static::$config['log_level']) == 'info' || strtolower(static::$config['log_level']) == 'all')){
				$logger->warning('You are in production environment, please set log level to WARNING, ERROR, FATAL to increase the application performance');
			}
			$logger->info('Configuration initialized successfully');
			$logger->info('The application configuration are listed below: ' . stringfy_vars(static::$config));
		}

		/**
		 * Get the configuration item value
		 * @param  string $item    the configuration item name to get
		 * @param  mixed $default the default value to use if can not find the config item in the list
		 * @return mixed          the config value if exist or the default value
		 */
		public static function get($item, $default = null){
			$logger = static::getLogger();
			if(isset(static::$config[$item])){
				return static::$config[$item];
			}
			$logger->warning('Cannot find config item ['.$item.'] using the default value ['.$default.']');
			return $default;
		}

		/**
		 * Set the configuration item value
		 * @param string $item  the config item name to set
		 * @param mixed $value the config item value
		 */
		public static function set($item, $value){
			static::$config[$item] = $value;
		}

		/**
		 * Get all the configuration values
		 * @return array the config values
		 */
		public static function getAll(){
			return static::$config;
		}

		/**
		 * Set the configuration values bu merged with the existing configuration
		 * @param array $config the config values to add in the configuration list
		 */
		public static function setAll(array $config = array()){
			static::$config = array_merge(static::$config, $config);
		}

		/**
		 * Delete the configuration item in the list
		 * @param  string $item the config item name to be deleted
		 */
		public static function delete($item){
			$logger = static::getLogger();
			if(isset(static::$config[$item])){
				$logger->info('Delete config item ['.$item.']');
				unset(static::$config[$item]);
			}
			else{
				$logger->warning('Config item ['.$item.'] to be deleted does not exists');
			}
		}

		/**
		 * Load the configuration file. This an alias with the Loader::config
		 * @param  string $config the config name to be loaded
		 */
		public static function load($config){
			Loader::config($config);
		}
	}

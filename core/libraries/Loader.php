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

	class Loader{
		public static $loaded = array();
		private static $logger;


		private static function getLogger(){
			if(static::$logger == null){
				static::$logger = new Log();
				static::$logger->setLogger('Library::Loader');
			}
			return static::$logger;
		}

		public function __construct(){
		}

		static function register(){
			spl_autoload_register(array('Loader', 'autoload'));
		}

		static function isLoaded($name, $type){
			return !empty(static::$loaded[$type][$name]);
		}

		static function isLoadedLibrary($name){
			return static::isLoaded($name, 'libraries');
		}

		static function isLoadedFunction($name){
			return static::isLoaded($name, 'functions');
		}


		static function isLoadedController($name){
			return static::isLoaded($name, 'controllers');
		}

		static function isLoadedModel($name){
			return static::isLoaded($name, 'models');
		}

		static function isLoadedConfig($name){
			return static::isLoaded($name, 'config');
		}

		static function isLoadedClass($name){
			return static::isLoaded($name, 'classes');
		}

		static function autoload($class){
			$logger = static::getLogger();
			$search_dir = array(CORE_PATH, CORE_LIBRARY_PATH, LIBRARY_PATH, APPS_CONTROLLER_PATH);
			$file = $class.'.php';
			$logger->debug('Loading class [' . $class . '] ...');
			if(static::isLoadedClass($class)){
				$logger->info('class ' . $class . ' already loaded no need to load it again, cost in performance');
				return;
			}
			foreach($search_dir as $dir){
				if(file_exists($dir.$file)){
					if(!class_exists($class)){
						require_once $dir.$file;
					}
					if(class_exists($class)){
						static::$loaded['classes'][$class] = $dir.$file;
						$logger->info('class: [' . $class . '] ' . $dir.$file . ' loaded successfully.');
					}
					//is already found no need to continue
					break;
				}
			}

		}

		static function controller($class, $graceful = true){
			$logger = static::getLogger();
			$class = str_ireplace('.php', '', $class);
			$class = ucfirst($class);
			$file = $class.'.php';
			$logger->debug('Loading controller [' . $class . '] ...');
			if(static::isLoadedController($class)){
				$logger->info('controller [' . $class . '] already loaded no need to load it again, cost in performance');
				return;
			}
			if(file_exists(APPS_CONTROLLER_PATH.$file)){
				require_once APPS_CONTROLLER_PATH.$file;
				if(!class_exists($class)){
					show_error('The file '.$file.' exists but does not contain the class '.$class);
				}
				$logger->info('controller [' . $class . '] ' . APPS_CONTROLLER_PATH.$file . ' loaded successfully.');
				static::$loaded['controllers'][$class] = APPS_CONTROLLER_PATH.$file;
			}
			else if($graceful){
				$logger->error('cannot find controller ' . $class);
				return false;
			}
			else{
				show_error('Unable to find controller class '.$class);
			}
		}

		static function model($class, $instance = null){
			$logger = static::getLogger();
			$class = str_ireplace('.php', '', $class);
			$class = ucfirst($class);
			$file = $class.'.php';
			$logger->debug('Loading model [' . $class . '] ...');
			if(static::isLoadedModel($class)){
				$logger->info('model [' . $class . '] already loaded no need to load it again, cost in performance');
				return;
			}
			if(!$instance){
				$instance = $class;
			}
			if(file_exists(APPS_MODEL_PATH.$file)){
				require_once APPS_MODEL_PATH.$file;
				if(class_exists($class)){
					$c = new $class();
					$instance = strtolower($instance);
					$obj = & get_instance();
					$obj->{$instance} = $c;
				}
				else{
					show_error('The file '.$file.' exists but does not contain the class '.$class);
				}
			}
			else{
				show_error('Unable to find model class '.$class);
			}
			static::$loaded['models'][$class] = APPS_MODEL_PATH.$file;
			$logger->info('model [' . $class . '] ' . APPS_MODEL_PATH.$file . ' loaded successfully.');
		}

		static function library($class, $instance = null){
			$logger = static::getLogger();
			$search_dir = array(LIBRARY_PATH, CORE_LIBRARY_PATH);
			$found = false;
			$class = str_ireplace('.php', '', $class);
			$class = ucfirst($class);
			$file = $class.'.php';
			$logger->debug('Loading library [' . $class . '] ...');
			if(static::isLoadedLibrary($class)){
				$logger->info('library [' . $class . '] already loaded no need to load it again, cost in performance');
				return;
			}
			if(!$instance){
				$instance = $class;
			}
			$instance = strtolower($instance);
			foreach($search_dir as $dir){
				if(file_exists($dir.$file)){
					require_once $dir.$file;
					if(class_exists($class)){
						$c = new $class();
						$obj = & get_instance();
						$obj->{$instance} = $c;
						static::$loaded['libraries'][$class] = $dir.$file;
						$logger->info('library [' . $class . '] ' . $dir.$file . ' loaded successfully.');
					}
					else{
						show_error('The file '.$file.' exists but does not contain the class '.$class);
					}
					$found = true;
					//is already found not to continue
					break;
				}
			}
			if(!$found){
				show_error('Unable to find library class '.$class);
			}
		}

		static function functions($function){
			$logger = static::getLogger();
			$search_dir = array(FUNCTIONS_PATH, CORE_FUNCTIONS_PATH);
			$found = false;
			$function = str_ireplace('.php', '', $function);
			$function = str_ireplace('function_', '', $function);
			$file = 'function_'.$function.'.php';
			$logger->debug('Loading helper [' . $function . '] ...');
			if(static::isLoadedFunction($function)){
				$logger->info('helper [' . $function . '] already loaded no need to load it again, cost in performance');
				return;
			}
			foreach($search_dir as $dir){
				if(file_exists($dir.$file)){
					require_once $dir.$file;
					static::$loaded['functions'][$function] = $dir.$file;
					$logger->info('helper [' . $function . '] ' . $dir.$file . ' loaded successfully.');
					$found = true;
					//is already found not to continue
					break;
				}
			}
			if(!$found){
				show_error('Unable to find function file '.$file);
			}
		}

		static function config($filename){
			$logger = static::getLogger();
			$filename = str_ireplace('.php', '', $filename);
			$filename = str_ireplace('config_', '', $filename);
			$file = 'config_'.$filename.'.php';
			$logger->debug('Loading configuration [' . $file . '] ...');
			if(static::isLoadedConfig($filename)){
				$logger->info('configuration [' . $filename . '] already loaded no need to load it again, cost in performance');
				return;
			}
			if(file_exists(CONFIG_PATH.$file)){
				require_once CONFIG_PATH.$file;
				if(!empty($config) && is_array($config)){
					Config::setAll($config);
				}
				else{
					show_error('No configuration found in '.$file);
				}
			}
			else{
				show_error('Unable to find config file '.$file);
			}
			static::$loaded['config'][$filename] = CONFIG_PATH.$file;
			$logger->info('configuration [' . $filename . '] ' . CONFIG_PATH.$file . ' loaded successfully.');
		}
	}

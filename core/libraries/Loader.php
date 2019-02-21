<?php
	defined('ROOT_PATH') || exit('Access denied');
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
						$logger->info('Class: [' . $class . '] ' . $dir.$file . ' loaded successfully.');
					}
					//is already found no need to continue
					break;
				}
			}

		}

		static function controller($class, $path = null, $graceful = true){
			$logger = static::getLogger();
			$class = str_ireplace('.php', '', $class);
			$class = ucfirst($class);
			$classFile = $class.'.php';
			$logger->debug('Loading controller [' . $class . '] ...');
			if(static::isLoadedController($class)){
				$logger->info('Controller [' . $class . '] already loaded no need to load it again, cost in performance');
				return;
			}
			$classFilePath = APPS_CONTROLLER_PATH . $path . $classFile;
			//first check if this controller is in the module
			$logger->debug('Trying to find controller [' . $class . '] from module list ...');
			$moduleControllerFilePath = Module::findControllerFullPath($class, $path);
			if($moduleControllerFilePath){
				$logger->info('Found controller [' . $class . '] from modules, the file path is [' .$moduleControllerFilePath. '] we will used it');
				$classFilePath = $moduleControllerFilePath;
			}
			else{
				$logger->info('Cannot find controller [' . $class . '] from modules using the default location');
			}

			if(file_exists($classFilePath)){
				require_once $classFilePath;
				if(!class_exists($class)){
					show_error('The file '.$classFilePath.' exists but does not contain the class '.$class);
				}
				$logger->info('Controller [' . $class . '] ' .  $classFilePath . ' loaded successfully.');
				static::$loaded['controllers'][$class] =  $classFilePath;
			}
			else if($graceful){
				$logger->error('Cannot find controller [' . $class . ']');
				return false;
			}
			else{
				show_error('Unable to find controller class [' . $class . ']');
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
			$classFilePath = APPS_MODEL_PATH . $file;
			//first check if this model is in the module
			$logger->debug('Trying to find model [' . $class . '] from module list ...');
			$moduleModelFilePath = Module::findModelFullPath($class);
			if($moduleModelFilePath){
				$logger->info('Found model [' . $class . '] from modules, the file path is [' .$moduleModelFilePath. '] we will used it');
				$classFilePath = $moduleModelFilePath;
			}
			else{
				$logger->info('Cannot find model [' . $class . '] from modules using the default location');
			}

			if(file_exists($classFilePath)){
				require_once $classFilePath;
				if(class_exists($class)){
					$c = new $class();
					$instance = strtolower($instance);
					$obj = & get_instance();
					$obj->{$instance} = $c;
				}
				else{
					show_error('The file '.$classFilePath.' exists but does not contain the class '.$class);
				}
			}
			else{
				show_error('Unable to find model class '.$class);
			}
			static::$loaded['models'][$class] = $classFilePath;
			$logger->info('model [' . $class . '] ' . $classFilePath . ' loaded successfully.');
		}

		static function library($class, $instance = null){
			$logger = static::getLogger();
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
			$libraryFilePath = null;
			//first check if this library is in the module
			$logger->debug('Trying to find library [' . $class . '] from module list ...');
			$moduleLibraryPath = Module::findLibraryFullPath($class);
			if($moduleLibraryPath){
				$logger->info('Found library [' . $class . '] from modules, the file path is [' .$moduleLibraryPath. '] we will used it');
				$libraryFilePath = $moduleLibraryPath;
			}
			else{
				$logger->info('Cannot find library [' . $class . '] from modules using the default location');
			}
			if(! $libraryFilePath){
				$search_dir = array(LIBRARY_PATH, CORE_LIBRARY_PATH);
				foreach($search_dir as $dir){
					$filePath = $dir . $file;
					if(file_exists($filePath)){
						$libraryFilePath = $filePath;
						//is already found not to continue
						break;
					}
				}
			}
			if($libraryFilePath){
				require_once $libraryFilePath;
				if(class_exists($class)){
					$c = new $class();
					$obj = & get_instance();
					$obj->{$instance} = $c;
					static::$loaded['libraries'][$class] = $libraryFilePath;
					$logger->info('Library [' . $class . '] ' . $libraryFilePath . ' loaded successfully.');
				}
				else{
					show_error('The file '.$libraryFilePath.' exists but does not contain the class '.$class);
				}
			}
			else{
				show_error('Unable to find library class '.$class);
			}
		}

		static function functions($function){
			$logger = static::getLogger();
			$function = str_ireplace('.php', '', $function);
			$function = str_ireplace('function_', '', $function);
			$file = 'function_'.$function.'.php';
			$logger->debug('Loading helper [' . $function . '] ...');
			if(static::isLoadedFunction($function)){
				$logger->info('helper [' . $function . '] already loaded no need to load it again, cost in performance');
				return;
			}
			$functionFilePath = null;
			//first check if this helper is in the module
			$logger->debug('Trying to find helper [' . $function . '] from module list ...');
			$moduleFunctionPath = Module::findFunctionFullPath($function);
			if($moduleFunctionPath){
				$logger->info('Found helper [' . $function . '] from modules, the file path is [' .$moduleFunctionPath. '] we will used it');
				$functionFilePath = $moduleFunctionPath;
			}
			else{
				$logger->info('Cannot find helper [' . $function . '] from modules using the default location');
			}
			if(! $functionFilePath){
				$search_dir = array(FUNCTIONS_PATH, CORE_FUNCTIONS_PATH);
				foreach($search_dir as $dir){
					$filePath = $dir . $file;
					if(file_exists($filePath)){
						$functionFilePath = $filePath;
						//is already found not to continue
						break;
					}
				}
			}
			if($functionFilePath){
				require_once $functionFilePath;
				static::$loaded['functions'][$function] = $functionFilePath;
				$logger->info('Helper [' . $function . '] ' . $functionFilePath . ' loaded successfully.');
			}
			else{
				show_error('Unable to find helper file '.$file);
			}
		}

		static function config($filename){
			$logger = static::getLogger();
			$filename = str_ireplace('.php', '', $filename);
			$filename = str_ireplace('config_', '', $filename);
			$file = 'config_'.$filename.'.php';
			$path = CONFIG_PATH . $file;
			$logger->debug('Loading configuration [' . $path . '] ...');
			if(static::isLoadedConfig($filename)){
				$logger->info('configuration [' . $path . '] already loaded no need to load it again, cost in performance');
				return;
			}
			if(file_exists($path)){
				require_once $path;
				if(!empty($config) && is_array($config)){
					Config::setAll($config);
				}
				else{
					show_error('No configuration found in ['. $path . ']');
				}
			}
			else{
				show_error('Unable to find config file ['. $path . ']');
			}
			static::$loaded['config'][$filename] = $path;
			$logger->info('configuration [' . $path . '] loaded successfully.');
			$logger->info('The custom application configuration loaded are listed below: ' . stringfy_vars($config));
			unset($config);
		}
	}

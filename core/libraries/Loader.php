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
	class Loader{
		/**
		 * List of loaded resources
		 * @var array
		 */
		public static $loaded = array();
		
		/**
		 * The logger instance
		 * @var Log
		 */
		private static $logger;


		public function __construct(){
			//add the resources already loaded during application bootstrap
			//in the list to prevent duplicate or loading the resources again.
			static::$loaded = class_loaded();
			$autoloads = array();
			//loading of the resources in autoload.php configuration file
			if(file_exists(CONFIG_PATH . 'autoload.php')){
				require_once CONFIG_PATH . 'autoload.php';
				if(!empty($autoload) && is_array($autoload)){
					$autoloads = $autoload;
					unset($autoload);
				}
				else{
					show_error('No autoload configuration found in autoload.php');
				}
			}
			//loading autoload configuration for module
			$modulesAutoloads = Module::getModulesAutoloadConfig();
			if($modulesAutoloads && is_array($modulesAutoloads)){
				//libraries autoload
				if(!empty($modulesAutoloads['libraries']) && is_array($modulesAutoloads['libraries'])){
					$autoloads['libraries'] = array_merge($autoloads['libraries'], $modulesAutoloads['libraries']);
				}
				//config autoload
				if(!empty($modulesAutoloads['config']) && is_array($modulesAutoloads['config'])){
					$autoloads['config'] = array_merge($autoloads['config'], $modulesAutoloads['config']);
				}
				//models autoload
				if(!empty($modulesAutoloads['models']) && is_array($modulesAutoloads['models'])){
					$autoloads['models'] = array_merge($autoloads['models'], $modulesAutoloads['models']);
				}
				//functions autoload
				if(!empty($modulesAutoloads['functions']) && is_array($modulesAutoloads['functions'])){
					$autoloads['functions'] = array_merge($autoloads['functions'], $modulesAutoloads['functions']);
				}
				//languages autoload
				if(!empty($modulesAutoloads['languages']) && is_array($modulesAutoloads['languages'])){
					$autoloads['languages'] = array_merge($autoloads['languages'], $modulesAutoloads['languages']);
				}
			}
			//libraries autoload
			if(!empty($autoloads['libraries']) && is_array($autoloads['libraries'])){
				foreach($autoloads['libraries'] as $library){
					Loader::library($library);
				}
			}
			//config autoload
			if(!empty($autoloads['config']) && is_array($autoloads['config'])){
				foreach($autoloads['config'] as $c){
					Loader::config($c);
				}
			}
			//before load models check if database library is loaded and then load model library
			//if Database is loaded load the required library
			if(isset(static::$loaded['database'])){
				//Model
				require_once CORE_LIBRARY_PATH . 'Model.php';
				//track of loaded class
				class_loaded('Model');
			}
			//models autoload
			if(!empty($autoloads['models']) && is_array($autoloads['models'])){
				foreach($autoloads['models'] as $model){
					Loader::model($model);
				}
			}
			//functions autoload
			if(!empty($autoloads['functions']) && is_array($autoloads['functions'])){
				foreach($autoloads['functions'] as $function){
					Loader::functions($function);
				}
			}
			//languages autoload
			if(!empty($autoloads['languages']) && is_array($autoloads['languages'])){
				foreach($autoloads['languages'] as $language){
					Loader::lang($language);
				}
			}
		}

		/**
		 * Get the logger singleton instance
		 * @return Log the logger instance
		 */
		private static function getLogger(){
			if(static::$logger == null){
				static::$logger[0] =& class_loader('Log');
				static::$logger[0]->setLogger('Library::Loader');
			}
			return static::$logger[0];
		}

		/**
		 * Load the model class
		 * @param  string $class    the class name to be loaded
		 * @param  string $instance the name of the instance to use in super object
		 * @return void
		 */
		public static function model($class, $instance = null){
			$logger = static::getLogger();
			$class = str_ireplace('.php', '', $class);
			$file = ucfirst($class).'.php';
			$logger->debug('Loading model [' . $class . '] ...');
			if(isset(static::$loaded[strtolower($class)])){
				$logger->info('Model [' . $class . '] already loaded no need to load it again, cost in performance');
				return;
			}
			if(!$instance){
				$instance = $class;
			}
			$classFilePath = APPS_MODEL_PATH . $file;
			//first check if this model is in the module
			$logger->debug('Checking model [' . $class . '] from module list ...');
			$mod = null;
			$obj = & get_instance();
			//check if the request class contains module name
			if(strpos($class, '/') !== false){
				$path = explode('/', $class);
				if(isset($path[0]) && in_array($path[0], Module::getModuleList())){
					$mod = $path[0];
					$class = ucfirst($path[1]);
				}
			}
			else{
				$class = ucfirst($class);
			}

			if(! $mod && !empty($obj->module_name)){
				$mod = $obj->module_name;
			}
			$moduleModelFilePath = Module::findModelFullPath($class, $mod);
			if($moduleModelFilePath){
				$logger->info('Found model [' . $class . '] from module [' .$mod. '], the file path is [' .$moduleModelFilePath. '] we will used it');
				$classFilePath = $moduleModelFilePath;
			}
			else{
				$logger->info('Cannot find model [' . $class . '] from modules using the default location');
			}
			$logger->info('The model file path to be loaded is [' . $classFilePath . ']');
			if(file_exists($classFilePath)){
				require_once $classFilePath;
				if(class_exists($class)){
					$c = new $class();
					$instance = strtolower($instance);
					$obj = & get_instance();
					$obj->{$instance} = $c;
					static::$loaded[strtolower($class)] = $class;
					$logger->info('Model [' . $class . '] --> ' . $classFilePath . ' loaded successfully.');
				}
				else{
					show_error('The file '.$classFilePath.' exists but does not contain the class ['. $class . ']');
				}
			}
			else{
				show_error('Unable to find the model [' . $class . ']');
			}
		}


		/**
		 * Load the library class
		 * @param  string $class    the library class name to be loaded
		 * @param  string $instance the instance name to use in super object
		 * @param mixed $params the arguments to pass to the constructor
		 * @return void
		 */
		public static function library($class, $instance = null, $params = null){
			$logger = static::getLogger();
			$class = str_ireplace('.php', '', $class);
			$file = ucfirst($class) .'.php';
			$logger->debug('Loading library [' . $class . '] ...');
			if(isset(static::$loaded[strtolower($class)])){
				$logger->info('Library [' . $class . '] already loaded no need to load it again, cost in performance');
				return;
			}
			if(!$instance){
				$instance = $class;
			}
			$instance = strtolower($instance);
			$libraryFilePath = null;
			$isSystem = false;
			$logger->debug('Check if this is a system library ...');
			if(file_exists(CORE_LIBRARY_PATH . $file)){
				$isSystem = true;
				$libraryFilePath = CORE_LIBRARY_PATH . $file;
				$class = ucfirst($class);
				$logger->info('This library is a system library');
			}
			else{
				$logger->info('This library is not a system library');	
				//first check if this library is in the module
				$logger->debug('Checking library [' . $class . '] from module list ...');
				$mod = null;
				$obj = & get_instance();
				//check if the request class contains module name
				if(strpos($class, '/') !== false){
					$path = explode('/', $class);
					if(isset($path[0]) && in_array($path[0], Module::getModuleList())){
						$mod = $path[0];
						$class = ucfirst($path[1]);
					}
				}
				else{
					$class = ucfirst($class);
				}
				if(! $mod && !empty($obj->module_name)){
					$mod = $obj->module_name;
				}
				$moduleLibraryPath = Module::findLibraryFullPath($class, $mod);
				if($moduleLibraryPath){
					$logger->info('Found library [' . $class . '] from module [' .$mod. '], the file path is [' .$moduleLibraryPath. '] we will used it');
					$libraryFilePath = $moduleLibraryPath;
				}
				else{
					$logger->info('Cannot find library [' . $class . '] from modules using the default location');
				}
			}
			if(! $libraryFilePath){
				$search_dir = array(LIBRARY_PATH);
				foreach($search_dir as $dir){
					$filePath = $dir . $file;
					if(file_exists($filePath)){
						$libraryFilePath = $filePath;
						//is already found not to continue
						break;
					}
				}
			}
			$logger->info('The library file path to be loaded is [' . $libraryFilePath . ']');
			if($libraryFilePath){
				require_once $libraryFilePath;
				if(class_exists($class)){
					$c = $params ? new $class($params) : new $class();
					$obj = & get_instance();
					$obj->{$instance} = $c;
					static::$loaded[strtolower($class)] = $class;
					$logger->info('Library [' . $class . '] --> ' . $libraryFilePath . ' loaded successfully.');
				}
				else{
					show_error('The file '.$libraryFilePath.' exists but does not contain the class '.$class);
				}
			}
			else{
				show_error('Unable to find library class [' . $class . ']');
			}
		}

		/**
		 * Load the helper
		 * @param  string $function the helper name to be loaded
		 * @return void
		 */
		public static function functions($function){
			$logger = static::getLogger();
			$function = str_ireplace('.php', '', $function);
			$function = str_ireplace('function_', '', $function);
			$file = 'function_'.$function.'.php';
			$logger->debug('Loading helper [' . $function . '] ...');
			if(isset(static::$loaded['function_' . $function])){
				$logger->info('Helper [' . $function . '] already loaded no need to load it again, cost in performance');
				return;
			}
			$functionFilePath = null;
			//first check if this helper is in the module
			$logger->debug('Checking helper [' . $function . '] from module list ...');
			$mod = null;
			$obj = & get_instance();
			//check if the request class contains module name
			if(strpos($function, '/') !== false){
				$path = explode('/', $function);
				if(isset($path[0]) && in_array($path[0], Module::getModuleList())){
					$mod = $path[0];
					$function = 'function_' . $path[1] . '.php';
					$file = $path[0] . DS . 'function_'.$function.'.php';
				}
			}
			if(! $mod && !empty($obj->module_name)){
				$mod = $obj->module_name;
			}
			$moduleFunctionPath = Module::findFunctionFullPath($function, $mod);
			if($moduleFunctionPath){
				$logger->info('Found helper [' . $function . '] from module [' .$mod. '], the file path is [' .$moduleFunctionPath. '] we will used it');
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
			$logger->info('The helper file path to be loaded is [' . $functionFilePath . ']');
			if($functionFilePath){
				require_once $functionFilePath;
				static::$loaded['function_' . $function] = $functionFilePath;
				$logger->info('Helper [' . $function . '] --> ' . $functionFilePath . ' loaded successfully.');
			}
			else{
				show_error('Unable to find helper file [' . $file . ']');
			}
		}

		/**
		 * Load the configuration file
		 * @param  string $filename the configuration filename located at CONFIG_PATH
		 * @return void
		 */
		public static function config($filename){
			$logger = static::getLogger();
			$filename = str_ireplace('.php', '', $filename);
			$filename = str_ireplace('config_', '', $filename);
			$file = 'config_'.$filename.'.php';
			$path = CONFIG_PATH . $file;
			$logger->debug('Loading configuration [' . $path . '] ...');
			if(isset(static::$loaded['config_' . $filename])){
				$logger->info('Configuration [' . $path . '] already loaded no need to load it again, cost in performance');
				return;
			}
			$logger->info('The config file path to be loaded is [' . $path . ']');
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
			static::$loaded['config_' . $filename] = $path;
			$logger->info('configuration [' . $path . '] loaded successfully.');
			$logger->info('The custom application configuration loaded are listed below: ' . stringfy_vars($config));
			unset($config);
		}


		/**
		 * Load the language
		 * @param  string $language the language name to be loaded
		 * @return void
		 */
		public static function lang($language){
			$logger = static::getLogger();
			$language = str_ireplace('.php', '', $language);
			$language = str_ireplace('lang_', '', $language);
			$file = 'lang_'.$language.'.php';
			$logger->debug('Loading language [' . $language . '] ...');
			if(isset(static::$loaded['lang_' . $language])){
				$logger->info('Language [' . $language . '] already loaded no need to load it again, cost in performance');
				return;
			}
			//determine the current language
			$appLang = get_config('default_language');
			//if the language exists in the cookie use it
			$cfgKey = get_config('language_cookie_name');
			$cLang = Cookie::get($cfgKey);
			if($cLang){
				$appLang = $cLang;
			}
			$languageFilePath = null;
			//first check if this language is in the module
			$logger->debug('Checking language [' . $language . '] from module list ...');
			$mod = null;
			$obj = & get_instance();
			//check if the request class contains module name
			if(strpos($language, '/') !== false){
				$path = explode('/', $language);
				if(isset($path[0]) && in_array($path[0], Module::getModuleList())){
					$mod = $path[0];
					$language = 'lang_' . $path[1] . '.php';
					$file = $path[0] . DS . 'lang_'.$language.'.php';
				}
			}
			if(! $mod && !empty($obj->module_name)){
				$mod = $obj->module_name;
			}
			$moduleLanguagePath = Module::findLanguageFullPath($language, $mod, $appLang);
			if($moduleLanguagePath){
				$logger->info('Found language [' . $language . '] from module [' .$mod. '], the file path is [' .$moduleLanguagePath. '] we will used it');
				$languageFilePath = $moduleLanguagePath;
			}
			else{
				$logger->info('Cannot find language [' . $language . '] from modules using the default location');
			}
			if(! $languageFilePath){
				$search_dir = array(APP_LANG_PATH, CORE_LANG_PATH);
				foreach($search_dir as $dir){
					$filePath = $dir . $appLang . DS . $file;
					if(file_exists($filePath)){
						$languageFilePath = $filePath;
						//is already found not to continue
						break;
					}
				}
			}
			$logger->info('The language file path to be loaded is [' . $languageFilePath . ']');
			if($languageFilePath){
				require_once $languageFilePath;
				if(!empty($lang) && is_array($lang)){
					$logger->info('Language file  [' .$languageFilePath. '] contains the valide languages keys add them to language list');
					//Note: may be here the class 'Lang' not yet loaded
					$langObj =& class_loader('Lang');
					$langObj->addLangMessages($lang);
					//free the memory
					unset($lang);
				}
				else{
					show_error('No language messages found in [' . $languageFilePath . ']');
				}
				static::$loaded['lang_' . $language] = $languageFilePath;
				$logger->info('Language [' . $language . '] --> ' . $languageFilePath . ' loaded successfully.');
			}
			else{
				show_error('Unable to find language file [' . $file . ']');
			}
		}
	}

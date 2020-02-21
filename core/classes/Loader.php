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
			
			//Load resources from autoload configuration
			$this->loadResourcesFromAutoloadConfig();
		}

		/**
		 * Get the logger singleton instance
		 * @return Log the logger instance
		 */
		private static function getLogger(){
			if(self::$logger == null){
				self::$logger[0] =& class_loader('Log', 'classes');
				self::$logger[0]->setLogger('Library::Loader');
			}
			return self::$logger[0];
		}

		/**
		 * Load the model class
		 *
		 * @param  string $class    the class name to be loaded
		 * @param  string $instance the name of the instance to use in super object
		 *
		 * @return void
		 */
		public static function model($class, $instance = null){
			$logger = static::getLogger();
			$class = str_ireplace('.php', '', $class);
			$class = trim($class, '/\\');
			$file = ucfirst($class).'.php';
			$logger->debug('Loading model [' . $class . '] ...');
			if(! $instance){
				//for module
				if(strpos($class, '/') !== false){
					$path = explode('/', $class);
					if(isset($path[1])){
						$instance = strtolower($path[1]);
					}
				}
				else{
					$instance = strtolower($class);
				}
			}
			if(isset(static::$loaded[$instance])){
				$logger->info('Model [' . $class . '] already loaded no need to load it again, cost in performance');
				return;
			}
			$classFilePath = APPS_MODEL_PATH . $file;
			//first check if this model is in the module
			$logger->debug('Checking model [' . $class . '] from module list ...');
			$searchModuleName = null;
			$obj = & get_instance();
			//check if the request class contains module name
			if(strpos($class, '/') !== false){
				$path = explode('/', $class);
				if(isset($path[0]) && in_array($path[0], Module::getModuleList())){
					$searchModuleName = $path[0];
					$class = ucfirst($path[1]);
				}
			}
			else{
				$class = ucfirst($class);
			}

			if(! $searchModuleName && !empty($obj->moduleName)){
				$searchModuleName = $obj->moduleName;
			}
			$moduleModelFilePath = Module::findModelFullPath($class, $searchModuleName);
			if($moduleModelFilePath){
				$logger->info('Found model [' . $class . '] from module [' .$searchModuleName. '], the file path is [' .$moduleModelFilePath. '] we will used it');
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
					$obj = & get_instance();
					$obj->{$instance} = $c;
					static::$loaded[$instance] = $class;
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
		 *
		 * @param  string $class    the library class name to be loaded
		 * @param  string $instance the instance name to use in super object
		 * @param mixed $params the arguments to pass to the constructor
		 *
		 * @return void
		 */
		public static function library($class, $instance = null, array $params = array()){
			$logger = static::getLogger();
			$class = str_ireplace('.php', '', $class);
			$class = trim($class, '/\\');
			$file = ucfirst($class) .'.php';
			$logger->debug('Loading library [' . $class . '] ...');
			if(! $instance){
				//for module
				if(strpos($class, '/') !== false){
					$path = explode('/', $class);
					if(isset($path[1])){
						$instance = strtolower($path[1]);
					}
				}
				else{
					$instance = strtolower($class);
				}
			}
			if(isset(static::$loaded[$instance])){
				$logger->info('Library [' . $class . '] already loaded no need to load it again, cost in performance');
				return;
			}
			$obj = & get_instance();
			//TODO for Database library
			if(strtolower($class) == 'database'){
				$logger->info('This is the Database library ...');
				$dbInstance = & class_loader('Database', 'classes', $params);
				$obj->{$instance} = $dbInstance;
				static::$loaded[$instance] = $class;
				$logger->info('Library Database loaded successfully.');
				return;
			}
			$libraryFilePath = null;
			$logger->debug('Check if this is a system library ...');
			if(file_exists(CORE_LIBRARY_PATH . $file)){
				$libraryFilePath = CORE_LIBRARY_PATH . $file;
				$class = ucfirst($class);
				$logger->info('This library is a system library');
			}
			else{
				$logger->info('This library is not a system library');	
				//first check if this library is in the module
				$logger->debug('Checking library [' . $class . '] from module list ...');
				$searchModuleName = null;
				//check if the request class contains module name
				if(strpos($class, '/') !== false){
					$path = explode('/', $class);
					if(isset($path[0]) && in_array($path[0], Module::getModuleList())){
						$searchModuleName = $path[0];
						$class = ucfirst($path[1]);
					}
				}
				else{
					$class = ucfirst($class);
				}
				if(! $searchModuleName && !empty($obj->moduleName)){
					$searchModuleName = $obj->moduleName;
				}
				$moduleLibraryPath = Module::findLibraryFullPath($class, $searchModuleName);
				if($moduleLibraryPath){
					$logger->info('Found library [' . $class . '] from module [' .$searchModuleName. '], the file path is [' .$moduleLibraryPath. '] we will used it');
					$libraryFilePath = $moduleLibraryPath;
				}
				else{
					$logger->info('Cannot find library [' . $class . '] from modules using the default location');
				}
			}
			if(! $libraryFilePath){
				$searchDir = array(LIBRARY_PATH);
				foreach($searchDir as $dir){
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
					static::$loaded[$instance] = $class;
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
		 *
		 * @param  string $function the helper name to be loaded
		 *
		 * @return void
		 */
		public static function functions($function){
			$logger = static::getLogger();
			$function = str_ireplace('.php', '', $function);
			$function = trim($function, '/\\');
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
			$searchModuleName = null;
			$obj = & get_instance();
			//check if the request class contains module name
			if(strpos($function, '/') !== false){
				$path = explode('/', $function);
				if(isset($path[0]) && in_array($path[0], Module::getModuleList())){
					$searchModuleName = $path[0];
					$function = 'function_' . $path[1] . '.php';
					$file = $path[0] . DS . 'function_'.$function.'.php';
				}
			}
			if(! $searchModuleName && !empty($obj->moduleName)){
				$searchModuleName = $obj->moduleName;
			}
			$moduleFunctionPath = Module::findFunctionFullPath($function, $searchModuleName);
			if($moduleFunctionPath){
				$logger->info('Found helper [' . $function . '] from module [' .$searchModuleName. '], the file path is [' .$moduleFunctionPath. '] we will used it');
				$functionFilePath = $moduleFunctionPath;
			}
			else{
				$logger->info('Cannot find helper [' . $function . '] from modules using the default location');
			}
			if(! $functionFilePath){
				$searchDir = array(FUNCTIONS_PATH, CORE_FUNCTIONS_PATH);
				foreach($searchDir as $dir){
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
		 *
		 * @param  string $filename the configuration filename located at CONFIG_PATH or MODULE_PATH/config
		 *
		 * @return void
		 */
		public static function config($filename){
			$logger = static::getLogger();
			$filename = str_ireplace('.php', '', $filename);
			$filename = trim($filename, '/\\');
			$filename = str_ireplace('config_', '', $filename);
			$file = 'config_'.$filename.'.php';
			$logger->debug('Loading configuration [' . $filename . '] ...');
			if(isset(static::$loaded['config_' . $filename])){
				$logger->info('Configuration [' . $file . '] already loaded no need to load it again, cost in performance');
				return;
			}
			$configFilePath = CONFIG_PATH . $file;
			//first check if this config is in the module
			$logger->debug('Checking config [' . $filename . '] from module list ...');
			$searchModuleName = null;
			$obj = & get_instance();
			//check if the request class contains module name
			if(strpos($filename, '/') !== false){
				$path = explode('/', $filename);
				if(isset($path[0]) && in_array($path[0], Module::getModuleList())){
					$searchModuleName = $path[0];
					$filename = $path[1] . '.php';
				}
			}
			if(! $searchModuleName && !empty($obj->moduleName)){
				$searchModuleName = $obj->moduleName;
			}
			$moduleConfigPath = Module::findConfigFullPath($filename, $searchModuleName);
			if($moduleConfigPath){
				$logger->info('Found config [' . $filename . '] from module [' .$searchModuleName. '], the file path is [' .$moduleConfigPath. '] we will used it');
				$configFilePath = $moduleConfigPath;
			}
			else{
				$logger->info('Cannot find config [' . $filename . '] from modules using the default location');
			}
			$logger->info('The config file path to be loaded is [' . $configFilePath . ']');
			if(file_exists($configFilePath)){
				$config = array();
				require_once $configFilePath;
				if(! empty($config) && is_array($config)){
					Config::setAll($config);
				}
			}
			else{
				show_error('Unable to find config file ['. $configFilePath . ']');
			}
			static::$loaded['config_' . $filename] = $configFilePath;
			$logger->info('Configuration [' . $configFilePath . '] loaded successfully.');
			$logger->info('The custom application configuration loaded are listed below: ' . stringfy_vars($config));
			unset($config);
		}


		/**
		 * Load the language
		 *
		 * @param  string $language the language name to be loaded
		 *
		 * @return void
		 */
		public static function lang($language){
			$logger = static::getLogger();
			$language = str_ireplace('.php', '', $language);
			$language = trim($language, '/\\');
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
			$objCookie = & class_loader('Cookie');
			$cookieLang = $objCookie->get($cfgKey);
			if($cookieLang){
				$appLang = $cookieLang;
			}
			$languageFilePath = null;
			//first check if this language is in the module
			$logger->debug('Checking language [' . $language . '] from module list ...');
			$searchModuleName = null;
			$obj = & get_instance();
			//check if the request class contains module name
			if(strpos($language, '/') !== false){
				$path = explode('/', $language);
				if(isset($path[0]) && in_array($path[0], Module::getModuleList())){
					$searchModuleName = $path[0];
					$language = 'lang_' . $path[1] . '.php';
					$file = $path[0] . DS .$language;
				}
			}
			if(! $searchModuleName && !empty($obj->moduleName)){
				$searchModuleName = $obj->moduleName;
			}
			$moduleLanguagePath = Module::findLanguageFullPath($language, $searchModuleName, $appLang);
			if($moduleLanguagePath){
				$logger->info('Found language [' . $language . '] from module [' .$searchModuleName. '], the file path is [' .$moduleLanguagePath. '] we will used it');
				$languageFilePath = $moduleLanguagePath;
			}
			else{
				$logger->info('Cannot find language [' . $language . '] from modules using the default location');
			}
			if(! $languageFilePath){
				$searchDir = array(APP_LANG_PATH, CORE_LANG_PATH);
				foreach($searchDir as $dir){
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
				$lang = array();
				require_once $languageFilePath;
				if(! empty($lang) && is_array($lang)){
					$logger->info('Language file  [' .$languageFilePath. '] contains the valid languages keys add them to language list');
					//Note: may be here the class 'Lang' not yet loaded
					$langObj =& class_loader('Lang', 'classes');
					$langObj->addLangMessages($lang);
					//free the memory
					unset($lang);
				}
				static::$loaded['lang_' . $language] = $languageFilePath;
				$logger->info('Language [' . $language . '] --> ' . $languageFilePath . ' loaded successfully.');
			}
			else{
				show_error('Unable to find language file [' . $file . ']');
			}
		}


		private function getResourcesFromAutoloadConfig(){
			$autoloads = array();
			$autoloads['config']    = array();
			$autoloads['languages'] = array();
			$autoloads['libraries'] = array();
			$autoloads['models']    = array();
			$autoloads['functions'] = array();
			//loading of the resources in autoload.php configuration file
			if(file_exists(CONFIG_PATH . 'autoload.php')){
				$autoload = array();
				require_once CONFIG_PATH . 'autoload.php';
				if(! empty($autoload) && is_array($autoload)){
					$autoloads = array_merge($autoloads, $autoload);
					unset($autoload);
				}
			}
			//loading autoload configuration for modules
			$modulesAutoloads = Module::getModulesAutoloadConfig();
			if(! empty($modulesAutoloads) && is_array($modulesAutoloads)){
				$autoloads = array_merge_recursive($autoloads, $modulesAutoloads);
			}
			return $autoloads;
		}

		private function loadResourcesFromAutoloadConfig(){
			$autoloads = array();
			$autoloads['config']    = array();
			$autoloads['languages'] = array();
			$autoloads['libraries'] = array();
			$autoloads['models']    = array();
			$autoloads['functions'] = array();

			$list = $this->getResourcesFromAutoloadConfig();

			$autoloads = array_merge($autoloads, $list);
			//config autoload
			foreach($autoloads['config'] as $c){
				$this->config($c);
			}
			
			//languages autoload
			foreach($autoloads['languages'] as $language){
				$this->lang($language);
			}
			
			//libraries autoload
			foreach($autoloads['libraries'] as $library){
				$this->library($library);
			}

			//models autoload
			if(! empty($autoloads['models']) && is_array($autoloads['models'])){
				foreach($autoloads['models'] as $model){
					$this->model($model);
				}
			}
			
			foreach($autoloads['functions'] as $function){
				$this->functions($function);
			}
		}
	}

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


	class Module{
		/**
		 * list of loaded module
		 * @var array
		 */
		private static $list = array();

		/**
		 * logger instance
		 * @var Log
		 */
		private static $logger;

		private static function getLogger(){
			if(static::$logger == null){
				static::$logger = new Log();
				static::$logger->setLogger('Library::Module');
			}
			return static::$logger;
		}

		public static function init(){
			$logger = static::getLogger();
			$logger->debug('Check if the application contains the modules ...');
			$list = Config::get('modules', array());
			if($list){
				$logger->info('The application contains modules below [' .implode(',', $list). '] add them to the list');
				foreach ($list as $module) {
					if($module && preg_match('/^([a-z0-9-_]+)$/i', $module) && is_dir(MODULE_PATH . $module)){
						static::$list[] = $module;
					}
					else{
						show_error('The module [' .$module. '] does not exist or is not valid may be the value is empty, contains an invalid directory name or the directory does not exists.');
					}
				}
			}
			else{
				$logger->info('The application contains no module skip');
			}
		}

		public static function getModulesConfig(){
			if(! static::hasModule()){
				return false;
			}
			else{
				$configs = array();
				foreach (static::$list as $module) {
					$file = MODULE_PATH . $module . DS . 'config' . DS . 'config.php';
					if(file_exists($file)){
						require_once $file;
						if(!empty($config) && is_array($config)){
							$configs = array_merge($configs, $config);
							unset($config);
						}
						else{
							show_error('No configuration found in config.php for module [' .$module. ']');
						}
					}
				}
				return $configs;
			}
			return false;
		}

		public static function getModulesRoutes(){
			if(! static::hasModule()){
				return false;
			}
			else{
				$routes = array();
				foreach (static::$list as $module) {
					$file = MODULE_PATH . $module . DS . 'config' . DS . 'routes.php';
					if(file_exists($file)){
						require_once $file;
						if(!empty($route) && is_array($route)){
							$routes = array_merge($routes, $route);
							unset($route);
						}
						else{
							show_error('No routing configuration found in [' .$file. '] for module [' .$module. ']');
						}
					}
				}
				return $routes;
			}
			return false;
		}

		public static function getModulesLanguages(){
			if(! static::hasModule()){
				return false;
			}
			else{
				$languages = array();
				//determine the current language
				$appLang = Config::get('default_language');
				//if the language exists in the cookie use it
				$cfgKey = Config::get('language_cookie_name');
				$cLang = Cookie::get($cfgKey);
				if($cLang){
					$appLang = $cLang;
				}
				foreach (static::$list as $module) {
					$file = MODULE_PATH . $module . DS . 'lang' . DS . $appLang . '.php';
					if(file_exists($file)){
						require_once $file;
						if(!empty($lang) && is_array($lang)){
							$languages = array_merge($languages, $lang);
							unset($lang);
						}
						else{
							show_error('No language found in [' .$file. '] for module [' .$module. ']');
						}
					}
				}
				return $languages;
			}
			return false;
		}


		/**
		 * Check if in module list can have this controller
		 * @param  string $class the controller class
		 * @param  string $path  the path where to find controller
		 * @return boolean|string  false or null if no module have this controller, path the full path of the controller
		 */
		static function findControllerFullPath($class, $path = null){
			$logger = static::getLogger();
			if(! static::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			$class = str_ireplace('.php', '', $class);
			$class = ucfirst($class);
			$classFile = $class.'.php';
			$moduleList = Module::getModuleList();
			if($moduleList){
				foreach ($moduleList as $module) {
					$logger->debug('Trying to find controller [' . $class . '] in module [' .$module. '] ...');
					$filePath = MODULE_PATH . $module . DS . 'controllers' . DS . $path . $classFile;
					if(file_exists($filePath)){
						$logger->info('Found controller [' . $class . '] in module [' .$module. '], the file path is [' .$filePath. ']');
						return $filePath;
					}
				}
			}
			return false;
		}

		/**
		 * Check if in module list can have this model
		 * @param  string $class the model class
		 * @return boolean|string  false or null if no module have this model, return the full path of this model
		 */
		static function findModelFullPath($class){
			$logger = static::getLogger();
			if(! static::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			$class = str_ireplace('.php', '', $class);
			$class = ucfirst($class);
			$classFile = $class.'.php';
			$moduleList = Module::getModuleList();
			if($moduleList){
				foreach ($moduleList as $module) {
					$logger->debug('Trying to find model [' . $class . '] in module [' .$module. '] ...');
					$filePath = MODULE_PATH . $module . DS . 'models' . DS . $classFile;
					if(file_exists($filePath)){
						$logger->info('Found model [' . $class . '] in module [' .$module. '], the file path is [' .$filePath. ']');
						return $filePath;
					}
				}
			}
			return false;
		}

		/**
		 * Check if in module list can have this helper
		 * @param  string $helper the helper name
		 * @return boolean|string  false or null if no module have this helper,  return the full path of this helper
		 */
		static function findFunctionFullPath($helper){
			$logger = static::getLogger();
			if(! static::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			$helper = str_ireplace('.php', '', $helper);
			$helper = str_ireplace('function_', '', $helper);
			$file = 'function_'.$helper.'.php';
			$moduleList = Module::getModuleList();
			if($moduleList){
				foreach ($moduleList as $module) {
					$logger->debug('Trying to find helper [' . $helper . '] in module [' .$module. '] ...');
					$filePath = MODULE_PATH . $module . DS . 'functions' . DS . $file;
					if(file_exists($filePath)){
						$logger->info('Found helper [' . $helper . '] in module [' .$module. '], the file path is [' .$filePath. ']');
						return $filePath;
					}
				}
			}
			return false;
		}

		/**
		 * Check if in module list can have this library
		 * @param  string $class the library name
		 * @return boolean|string  false or null if no module have this library,  return the full path of this library
		 */
		static function findLibraryFullPath($class){
			$logger = static::getLogger();
			if(! static::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			$class = str_ireplace('.php', '', $class);
			$file = $class.'.php';
			$moduleList = Module::getModuleList();
			if($moduleList){
				foreach ($moduleList as $module) {
					$logger->debug('Trying to find library [' . $class . '] in module [' .$module. '] ...');
					$filePath = MODULE_PATH . $module . DS . 'libraries' . DS . $file;
					if(file_exists($filePath)){
						$logger->info('Found library [' . $class . '] in module [' .$module. '], the file path is [' .$filePath. ']');
						return $filePath;
					}
				}
			}
			return false;
		}


		/**
		 * Check if in module list can have this view
		 * @param  string $view the view path
		 * @param string $module the module name to check
		 * @return boolean|string  false or null if no module have this view, path the full path of the view
		 */
		static function findViewFullPath($view, $module){
			$logger = static::getLogger();
			if(! static::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			if(! in_array($module, static::getModuleList())){
				$logger->info('Invalid module [' .$module. '] skiping.');
				return false;
			}
			$view = str_ireplace('.php', '', $view);
			$view = trim($view, '/\\');
			$viewFile = $view . '.php';
			$logger->debug('Trying to find view [' . $view . '] in module [' .$module. '] ...');
			$filePath = MODULE_PATH . $module . DS . 'views' . DS . $viewFile;
			if(file_exists($filePath)){
				$logger->info('Found view [' . $view . '] in module [' .$module. '], the file path is [' .$filePath. ']');
				return $filePath;
			}
			return false;
		}

		/**
		 * Check if the controller is in module
		 * @param  string $class the controller class
		 * @param  string $path  the path where to find controller
		 * @return boolean|string  false or null if the controller is in module, the module name of the controller
		 */
		static function findModuleForController($class, $path = null){
			if(! static::hasModule()){
				return false;
			}
			$moduleList = Module::getModuleList();
			if($moduleList){
				$class = str_ireplace('.php', '', $class);
				$class = ucfirst($class);
				$classFile = $class.'.php';
				foreach ($moduleList as $module) {
					$filePath = MODULE_PATH . $module . DS . 'controllers' . DS . $path . $classFile;
					if(file_exists($filePath)){
						return $module;
					}
				}
			}
			return false;
		}

		public static function getModuleList(){
			return static::$list;
		}

		public static function hasModule(){
			return !empty(static::$list);
		}

	}
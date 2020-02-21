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

		/**
		 * The signleton of the logger
		 * @return Object the Log instance
		 */
		private static function getLogger(){
			if(self::$logger == null){
				self::$logger[0] =& class_loader('Log', 'classes');
				self::$logger[0]->setLogger('Library::Module');
			}
			return self::$logger[0];
		}

		/**
		 * Initialise the module list by scanning the directory MODULE_PATH
		 */
		public function init(){
			$logger = self::getLogger();
			$logger->debug('Check if the application contains the modules ...');
			$moduleDir = opendir(MODULE_PATH);
			if(is_resource($moduleDir)){
				while(($module = readdir($moduleDir)) !== false){
					if(preg_match('/^([a-z0-9-_]+)$/i', $module) && is_dir(MODULE_PATH . $module)){
						self::$list[] = $module;
					}
					else{
						$logger->info('Skipping [' .$module. '], may be this is not a directory or does not exists or is invalid name');
					}
				}
				closedir($moduleDir);
			}
			ksort(self::$list);
			
			if(self::hasModule()){
				$logger->info('The application contains the module below [' . implode(', ', self::getModuleList()) . ']');
			}
			else{
				$logger->info('The application contains no module skipping');
			}
		}
		
		/**
		 * Get the list of the custom autoload configuration from module if exists
		 * @return array|boolean the autoload configurations list or false if no module contains the autoload configuration values
		 */
		public static function getModulesAutoloadConfig(){
			$logger = self::getLogger();
			if(! self::hasModule()){
				$logger->info('No module was loaded skipping.');
				return false;
			}
			$autoloads = array();
			$autoloads['libraries'] = array();
			$autoloads['config']    = array();
			$autoloads['models']    = array();
			$autoloads['functions'] = array();
			$autoloads['languages'] = array();
			
			foreach (self::$list as $module) {
				$file = MODULE_PATH . $module . DS . 'config' . DS . 'autoload.php';
				if(file_exists($file)){
					$autoload = array();
					require_once $file;
					if(! empty($autoload) && is_array($autoload)){
						$autoloads = array_merge_recursive($autoloads, $autoload);
						unset($autoload);
					}
				}
			}
			return $autoloads;
		}

		/**
		 * Get the list of the custom routes configuration from module if exists
		 * @return array|boolean the routes list or false if no module contains the routes configuration
		 */
		public static function getModulesRoutes(){
			$logger = self::getLogger();
			if(! self::hasModule()){
				$logger->info('No module was loaded skipping.');
				return false;
			}
			$routes = array();
			foreach (self::$list as $module) {
				$file = MODULE_PATH . $module . DS . 'config' . DS . 'routes.php';
				if(file_exists($file)){
					require_once $file;
					if(! empty($route) && is_array($route)){
						$routes = array_merge($routes, $route);
						unset($route);
					}
					else{
						show_error('No routing configuration found in [' .$file. '] for module [' . $module . ']');
					}
				}
			}
			return $routes;
		}


		/**
		 * Check if in module list can have this controller
		 * @param  string $class the controller class
		 * @param  string $module  the module name
		 * @return boolean|string  false or null if no module have this controller, path the full path of the controller
		 */
		public static function findControllerFullPath($class, $module = null){
			$logger = self::getLogger();
			if(! self::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			$class = str_ireplace('.php', '', $class);
			$class = ucfirst($class);
			$classFile = $class.'.php';
			$logger->debug('Checking the controller [' . $class . '] in module [' .$module. '] ...');
			$filePath = MODULE_PATH . $module . DS . 'controllers' . DS . $classFile;
			if(file_exists($filePath)){
				$logger->info('Found controller [' . $class . '] in module [' .$module. '], the file path is [' .$filePath. ']');
				return $filePath;
			}
			else{
				$logger->info('Controller [' . $class . '] does not exist in the module [' .$module. ']');
				return false;
			}
		}

		/**
		 * Check if in module list can have this model
		 * @param  string $class the model class
		 * @param string $module the module name
		 * @return boolean|string  false or null if no module have this model, return the full path of this model
		 */
		public static function findModelFullPath($class, $module = null){
			$logger = self::getLogger();
			if(! self::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			$class = str_ireplace('.php', '', $class);
			$class = ucfirst($class);
			$classFile = $class.'.php';
			$logger->debug('Checking model [' . $class . '] in module [' .$module. '] ...');
			$filePath = MODULE_PATH . $module . DS . 'models' . DS . $classFile;
			if(file_exists($filePath)){
				$logger->info('Found model [' . $class . '] in module [' .$module. '], the file path is [' .$filePath. ']');
				return $filePath;
			}
			else{
				$logger->info('Model [' . $class . '] does not exist in the module [' .$module. ']');
				return false;
			}
		}
		
		/**
		 * Check if in module list can have this config
		 * @param  string $configuration the config name
		 * @param string $module the module name
		 * @return boolean|string  false or null if no module have this configuration,  return the full path of this configuration
		 */
		public static function findConfigFullPath($configuration, $module = null){
			$logger = self::getLogger();
			if(! self::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			$configuration = str_ireplace('.php', '', $configuration);
			$file = $configuration.'.php';
			$logger->debug('Checking configuration [' . $configuration . '] in module [' .$module. '] ...');
			$filePath = MODULE_PATH . $module . DS . 'config' . DS . $file;
			if(file_exists($filePath)){
				$logger->info('Found configuration [' . $configuration . '] in module [' .$module. '], the file path is [' .$filePath. ']');
				return $filePath;
			}
			else{
				$logger->info('Configuration [' . $configuration . '] does not exist in the module [' .$module. ']');
				return false;
			}
		}

		/**
		 * Check if in module list can have this helper
		 * @param  string $helper the helper name
		 * @param string $module the module name
		 * @return boolean|string  false or null if no module have this helper,  return the full path of this helper
		 */
		public static function findFunctionFullPath($helper, $module = null){
			$logger = self::getLogger();
			if(! self::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			$helper = str_ireplace('.php', '', $helper);
			$helper = str_ireplace('function_', '', $helper);
			$file = 'function_'.$helper.'.php';
			$logger->debug('Checking helper [' . $helper . '] in module [' .$module. '] ...');
			$filePath = MODULE_PATH . $module . DS . 'functions' . DS . $file;
			if(file_exists($filePath)){
				$logger->info('Found helper [' . $helper . '] in module [' .$module. '], the file path is [' .$filePath. ']');
				return $filePath;
			}
			else{
				$logger->info('Helper [' . $helper . '] does not exist in the module [' .$module. ']');
				return false;
			}
		}


		/**
		 * Check if in module list can have this library
		 * @param  string $class the library name
		 * @param string $module the module name
		 * @return boolean|string  false or null if no module have this library,  return the full path of this library
		 */
		public static function findLibraryFullPath($class, $module = null){
			$logger = self::getLogger();
			if(! self::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			$class = str_ireplace('.php', '', $class);
			$file = $class.'.php';
			$logger->debug('Checking library [' . $class . '] in module [' .$module. '] ...');
			$filePath = MODULE_PATH . $module . DS . 'libraries' . DS . $file;
			if(file_exists($filePath)){
				$logger->info('Found library [' . $class . '] in module [' .$module. '], the file path is [' .$filePath. ']');
				return $filePath;
			}
			else{
				$logger->info('Library [' . $class . '] does not exist in the module [' .$module. ']');
				return false;
			}
		}


		/**
		 * Check if in module list can have this view
		 * @param  string $view the view path
		 * @param string $module the module name to check
		 * @return boolean|string  false or null if no module have this view, path the full path of the view
		 */
		public static function findViewFullPath($view, $module = null){
			$logger = self::getLogger();
			if(! self::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			$view = str_ireplace('.php', '', $view);
			$view = trim($view, '/\\');
			$view = str_ireplace('/', DS, $view);
			$viewFile = $view . '.php';
			$logger->debug('Checking view [' . $view . '] in module [' .$module. '] ...');
			$filePath = MODULE_PATH . $module . DS . 'views' . DS . $viewFile;
			if(file_exists($filePath)){
				$logger->info('Found view [' . $view . '] in module [' .$module. '], the file path is [' .$filePath. ']');
				return $filePath;
			}
			else{
				$logger->info('View [' . $view . '] does not exist in the module [' .$module. ']');
				return false;
			}
		}

		/**
		 * Check if in module list can have this language
		 * @param  string $language the language name
		 * @param string $module the module name
		 * @param string $appLang the application language like 'en', 'fr'
		 * @return boolean|string  false or null if no module have this language,  return the full path of this language
		 */
		public static function findLanguageFullPath($language, $module = null, $appLang){
			$logger = self::getLogger();
			if(! self::hasModule()){
				$logger->info('No module was loaded skiping.');
				return false;
			}
			$language = str_ireplace('.php', '', $language);
			$language = str_ireplace('lang_', '', $language);
			$file = 'lang_'.$language.'.php';
			$logger->debug('Checking language [' . $language . '] in module [' .$module. '] ...');
			$filePath = MODULE_PATH . $module . DS . 'lang' . DS . $appLang . DS . $file;
			if(file_exists($filePath)){
				$logger->info('Found language [' . $language . '] in module [' .$module. '], the file path is [' .$filePath. ']');
				return $filePath;
			}
			else{
				$logger->info('Language [' . $language . '] does not exist in the module [' .$module. ']');
				return false;
			}
		}

		/**
		 * Get the list of module loaded
		 * @return array the module list
		 */
		public static function getModuleList(){
			return self::$list;
		}

		/**
		 * Check if the application has an module
		 * @return boolean
		 */
		public static function hasModule(){
			return !empty(self::$list);
		}

	}

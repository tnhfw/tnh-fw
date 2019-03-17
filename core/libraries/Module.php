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
			if(static::$logger == null){
				static::$logger[0] =& class_loader('Log');
				static::$logger[0]->setLogger('Library::Module');
			}
			return static::$logger[0];
		}

		/**
		 * Initialise the module list by scanning the directory MODULE_PATH
		 */
		public function init(){
			$logger = static::getLogger();
			$logger->debug('Check if the application contains the modules ...');
			$module_dir = opendir(MODULE_PATH);
			while(($module = readdir($module_dir)) !== false){
				if($module != '.' && $module != '..'  && preg_match('/^([a-z0-9-_]+)$/i', $module) && is_dir(MODULE_PATH . $module)){
					static::$list[] = $module;
				}
				else{
					$logger->info('Skipping [' .$module. '], may be this is not a directory or does not exists or is invalid');
				}
			}
			closedir($module_dir);
			ksort(static::$list);
			if(static::hasModule()){
				$logger->info('The application contains the module below [' .implode(', ', static::getModuleList()). ']');
			}
			else{
				$logger->info('The application contains no module skipping');
			}
		}

		/**
		 * Get the list of the custom configuration from module if exists
		 * @return array|boolean the configurations list or false if no module contains the configuration values
		 */
		public static function getModulesConfig(){
			$logger = static::getLogger();
			if(! static::hasModule()){
				$logger->info('No module was loaded skipping.');
				return false;
			}
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
		
		/**
		 * Get the list of the custom autoload configuration from module if exists
		 * @return array|boolean the autoload configurations list or false if no module contains the autoload configuration values
		 */
		public static function getModulesAutoloadConfig(){
			$logger = static::getLogger();
			if(! static::hasModule()){
				$logger->info('No module was loaded skipping.');
				return false;
			}
			$autoloads['libraries'] = array();
			$autoloads['config'] = array();
			$autoloads['models'] = array();
			$autoloads['functions'] = array();
			$autoloads['languages'] = array();
			foreach (static::$list as $module) {
				$file = MODULE_PATH . $module . DS . 'config' . DS . 'autoload.php';
				if(file_exists($file)){
					require_once $file;
					if(!empty($autoload) && is_array($autoload)){
						//libraries autoload
						if(!empty($autoload['libraries']) && is_array($autoload['libraries'])){
							$autoloads['libraries'] = array_merge($autoloads['libraries'], $autoload['libraries']);
						}
						//config autoload
						if(!empty($autoload['config']) && is_array($autoload['config'])){
							$autoloads['config'] = array_merge($autoloads['config'], $autoload['config']);
						}
						//models autoload
						if(!empty($autoload['models']) && is_array($autoload['models'])){
							$autoloads['models'] = array_merge($autoloads['models'], $autoload['models']);
						}
						//functions autoload
						if(!empty($autoload['functions']) && is_array($autoload['functions'])){
							$autoloads['functions'] = array_merge($autoloads['functions'], $autoload['functions']);
						}
						//languages autoload
						if(!empty($autoload['languages']) && is_array($autoload['languages'])){
							$autoloads['languages'] = array_merge($autoloads['languages'], $autoload['languages']);
						}
						unset($autoload);
					}
					else{
						show_error('No autoload configuration found in autoload.php for module [' .$module. ']');
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
			$logger = static::getLogger();
			if(! static::hasModule()){
				$logger->info('No module was loaded skipping.');
				return false;
			}
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


		/**
		 * Check if in module list can have this controller
		 * @param  string $class the controller class
		 * @param  string $module  the module name
		 * @return boolean|string  false or null if no module have this controller, path the full path of the controller
		 */
		public static function findControllerFullPath($class, $module = null){
			$logger = static::getLogger();
			if(! static::hasModule()){
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
			return false;
		}

		/**
		 * Check if in module list can have this model
		 * @param  string $class the model class
		 * @param string $module the module name
		 * @return boolean|string  false or null if no module have this model, return the full path of this model
		 */
		public static function findModelFullPath($class, $module = null){
			$logger = static::getLogger();
			if(! static::hasModule()){
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
			return false;
		}

		/**
		 * Check if in module list can have this helper
		 * @param  string $helper the helper name
		 * @param string $module the module name
		 * @return boolean|string  false or null if no module have this helper,  return the full path of this helper
		 */
		public static function findFunctionFullPath($helper, $module = null){
			$logger = static::getLogger();
			if(! static::hasModule()){
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
			return false;
		}


		/**
		 * Check if in module list can have this library
		 * @param  string $class the library name
		 * @param string $module the module name
		 * @return boolean|string  false or null if no module have this library,  return the full path of this library
		 */
		public static function findLibraryFullPath($class, $module = null){
			$logger = static::getLogger();
			if(! static::hasModule()){
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
			return false;
		}


		/**
		 * Check if in module list can have this view
		 * @param  string $view the view path
		 * @param string $module the module name to check
		 * @return boolean|string  false or null if no module have this view, path the full path of the view
		 */
		public static function findViewFullPath($view, $module = null){
			$logger = static::getLogger();
			if(! static::hasModule()){
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
			return false;
		}

		/**
		 * Check if in module list can have this language
		 * @param  string $language the language name
		 * @param string $module the module name
		 * @param string $appLang the application language like 'en', 'fr'
		 * @return boolean|string  false or null if no module have this language,  return the full path of this language
		 */
		public static function findLanguageFullPath($language, $module = null, $appLang){
			$logger = static::getLogger();
			if(! static::hasModule()){
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
			return false;
		}

		/**
		 * Get the list of module loaded
		 * @return array the module list
		 */
		public static function getModuleList(){
			return static::$list;
		}

		/**
		 * Check if the application has an module
		 * @return boolean
		 */
		public static function hasModule(){
			return !empty(static::$list);
		}

	}
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
   
    class Module extends BaseStaticClass {
		
        /**
         * list of loaded module
         * @var array
         */
        private static $list = array();

        /**
         * Initialise the module list by scanning the directory MODULE_PATH
         */
        public function init() {
            $logger = self::getLogger();
            $logger->debug('Check if the application contains the modules ...');
            $dirList = glob(MODULE_PATH . '*', GLOB_ONLYDIR);
            if ($dirList !== false) {
               self::$list = array_map('basename', $dirList);
            }
            if (!empty(self::$list)) {
                $logger->info('The application contains the module below [' . implode(', ', self::getModuleList()) . ']');
            }
        }
		

        /**
         * Add new module in the list
         * @param string $name the name of the module
         *
         * @return object the current instance
         */
        public function add($name) {
            $logger = self::getLogger();
            if (in_array($name, self::$list)) {
               $logger->info('The module [' .$name. '] already added skipping.');
               return $this;
            }
            self::$list[] = $name;
            return $this;
        }

        /**
         * Remove the module from list
         * @param  string   $name the module name
         */
        public static function remove($name) {
            $logger = self::getLogger();
            $logger->debug('Removing of the module [' . $name . '] ...');
            if (false !== $index = array_search($name, self::$list, true)) {
                $logger->info('Found the module at index [' . $index . '] remove it');
                unset(self::$list[$index]);
            } else {
                $logger->info('Cannot found this module in the list');
            }
        }
        
        /**
         * Remove all the module. 
         */
        public static function removeAll() {
            $logger = self::getLogger();
            $logger->debug('Removing of all module ...');
            self::$list = array();
        }

         /**
         * Get the list of module loaded
         * @return array the module list
         */
        public static function getModuleList() {
            return self::$list;
        }

        /**
         * Check if the application has an module
         * @return boolean
         */
        public static function hasModule() {
            return !empty(self::$list);
        }
		
        /**
         * Get the list of the custom autoload configuration from module if exists
         * @return array|boolean the autoload configurations list or false if no module contains the autoload configuration values
         */
        public static function getModulesAutoloadConfig() {
            $logger = self::getLogger();
            if (empty(self::$list)) {
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
                if (file_exists($file)) {
                    $autoload = array();
                    require_once $file;
                    if (!empty($autoload) && is_array($autoload)) {
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
        public static function getModulesRoutesConfig() {
            $logger = self::getLogger();
            if (empty(self::$list)) {
                $logger->info('No module was loaded skipping.');
                return false;
            }
            $routes = array();
            foreach (self::$list as $module) {
                $file = MODULE_PATH . $module . DS . 'config' . DS . 'routes.php';
                if (file_exists($file)) {
                    $route = array();
                    require_once $file;
                    if (!empty($route) && is_array($route)) {
                        $routes = array_merge($routes, $route);
                        unset($route);
                    }
                }
            }
            return $routes;
        }


        /**
         * Check if in module list can have this controller
         * @see Module::findClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this controller, path the full path of the controller
         */
        public static function findControllerFullPath($class, $module = null) {
            return self::findClassInModuleFullFilePath($class, $module, 'controllers');
        }

        /**
         * Check if in module list can have this model
         * @see Module::findClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this model, return the full path of this model
         */
        public static function findModelFullPath($class, $module = null) {
            return self::findClassInModuleFullFilePath($class, $module, 'models');
        }

        /**
         * Check if in module list can have this library
         * @see Module::findClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this library,  return the full path of this library
         */
        public static function findLibraryFullPath($class, $module = null) {
            return self::findClassInModuleFullFilePath($class, $module, 'libraries');
        }

		
        /**
         * Check if in module list can have this config
         * @see  Module::findNonClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this configuration,  return the full path of this configuration
         */
        public static function findConfigFullPath($configuration, $module = null) {
            return self::findNonClassInModuleFullFilePath($configuration, $module, 'config');
        }

        /**
         * Check if in module list can have this helper
         * @see  Module::findNonClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this helper,  return the full path of this helper
         */
        public static function findFunctionFullPath($helper, $module = null) {
            return self::findNonClassInModuleFullFilePath($helper, $module, 'functions');
        }

        /**
         * Check if in module list can have this view
         * @see  Module::findNonClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this view, path the full path of the view
         */
        public static function findViewFullPath($view, $module = null) {
            return self::findNonClassInModuleFullFilePath($view, $module, 'views');
        }

        /**
         * Check if in module list can have this language
         * @see  Module::findNonClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this language,  return the full path of this language
         */
        public static function findLanguageFullPath($language, $appLang, $module = null) {
            return self::findNonClassInModuleFullFilePath($language, $module, 'lang', $appLang);
        }

        /**
         * Check if in module list can have the model, controller, library
         * @param  string $class the class name of library, model, controller
         * @param string $module the module name
         * @param string $type the name of the type "controllers", "libraries", "models"
         * @return boolean|string  false or null if no module 
         * have this class, return the full path of the class
         */
        protected static function findClassInModuleFullFilePath($class, $module, $type) {
            $logger = self::getLogger();
            $class = str_ireplace('.php', '', $class);
            $class = ucfirst($class);
            $classFile = $class . '.php';
            $logger->debug('Checking the class [' . $class . '] in module [' . $module . '] for [' . $type . '] ...');
            $filePath = MODULE_PATH . $module . DS . $type . DS . $classFile;
            if (file_exists($filePath)) {
                $logger->info('Found class [' . $class . '] in module [' . $module . '] for [' . $type . '] the file path is [' . $filePath . ']');
                return $filePath;
            }
            $logger->info('Class [' . $class . '] does not exist in the module [' . $module . '] for [' . $type . ']');
            return false;
        }

        /**
         * Check if in module list can have the config, view, helper, language
         * @param string $name the name of config, view, helper, language
         * @param string $module the module name
         * @param string $type the name of the type "config", "functions", "views", "lang"
         * @param string|null $appLang the application language. This is use only when $type = "lang"
         * @return boolean|string  false or null if no module 
         * have this resource, return the full path of the resource
         */
        protected static function findNonClassInModuleFullFilePath($name, $module, $type, $appLang = null) {
            $logger = self::getLogger();
            $name = str_ireplace('.php', '', $name);
            $file = $name . '.php';
            $filePath = MODULE_PATH . $module . DS . $type . DS . $file;
            switch ($type) {
                case 'functions':
                    $name = str_ireplace('function_', '', $name);
                    $file = 'function_' . $name . '.php';
                    $filePath = MODULE_PATH . $module . DS . $type . DS . $file;
                break;
                case 'views':
                    $name = trim($name, '/\\');
                    $name = str_ireplace('/', DS, $name);
                    $file = $name . '.php';
                    $filePath = MODULE_PATH . $module . DS . $type . DS . $file;
                break;
                case 'lang':
                    $name = str_ireplace('lang_', '', $name);
                    $file = 'lang_' . $name . '.php';
                    $filePath = MODULE_PATH . $module . DS . $type . DS . $appLang . DS . $file;
                break;
            }
            $logger->debug('Checking resource [' . $name . '] in module [' . $module . '] for [' . $type . '] ...');
            if (file_exists($filePath)) {
                $logger->info('Found resource [' . $name . '] in module [' . $module . '] for [' . $type . '] the file path is [' . $filePath . ']');
                return $filePath;
            }
            $logger->info('Resource [' . $name . '] does not exist in the module [' . $module . '] for [' . $type . ']');
            return false;
        }

    }

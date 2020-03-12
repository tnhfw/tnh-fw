<?php
    defined('ROOT_PATH') || exit('Access denied');
    /**
     * TNH Framework
     *
     * A simple PHP framework using HMVC architecture
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2017 TNH Framework
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in all
     * copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
     * SOFTWARE.
     */
   
    class Module extends BaseClass {
		
        /**
         * list of loaded module
         * @var array
         */
        private $list = array();

        /**
         * Construct new module
         * Initialize the module list by scanning the directory MODULE_PATH
         */
        public function __construct() {
            parent::__construct();

            $this->logger->debug('Check if the application contains the modules ...');
            $dirList = glob(MODULE_PATH . '*', GLOB_ONLYDIR);
            if ($dirList !== false) {
               $this->list = array_map('basename', $dirList);
            }
            if (!empty($this->list)) {
                $this->logger->info('The application contains the module below [' . implode(', ', $this->list) . ']');
            }
        }
		
        /**
         * Add new module in the list
         * @param string $name the name of the module
         *
         * @return object the current instance
         */
        public function add($name) {
            if (in_array($name, $this->list)) {
               $this->logger->info('The module [' .$name. '] already added skipping.');
               return $this;
            }
            $this->list[] = $name;
            return $this;
        }

        /**
         * Remove the module from list
         * @param  string   $name the module name
         *
         * @return object the current instance
         */
        public function remove($name) {
            $this->logger->debug('Removing of the module [' . $name . '] ...');
            if (false !== $index = array_search($name, $this->list, true)) {
                $this->logger->info('Found the module at index [' . $index . '] remove it');
                unset($this->list[$index]);
            } else {
                $this->logger->info('Cannot found this module in the list');
            }
            return $this;
        }
        
        /**
         * Remove all the module. 
         */
        public function removeAll() {
            $this->logger->debug('Removing of all module ...');
            $this->list = array();
        }

         /**
         * Get the list of module loaded
         * @return array the module list
         */
        public function getModuleList() {
            return $this->list;
        }

        /**
         * Check if the application has an module
         * @return boolean
         */
        public function hasModule() {
            return !empty($this->list);
        }
		
        /**
         * Get the list of the custom autoload configuration from module if exists
         * @return array|boolean the autoload configurations list or false if no module contains the autoload configuration values
         */
        public function getModulesAutoloadConfig() {
            if (empty($this->list)) {
                $this->logger->info('No module was loaded skipping.');
                return false;
            }
            $autoloads = array();
            $autoloads['libraries'] = array();
            $autoloads['config']    = array();
            $autoloads['models']    = array();
            $autoloads['functions'] = array();
            $autoloads['languages'] = array();
			
            foreach ($this->list as $module) {
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
        public function getModulesRoutesConfig() {
            if (empty($this->list)) {
                $this->logger->info('No module was loaded skipping.');
                return false;
            }
            $routes = array();
            foreach ($this->list as $module) {
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
        public function findControllerFullPath($class, $module = null) {
            return $this->findClassInModuleFullFilePath($class, $module, 'controllers');
        }

        /**
         * Check if in module list can have this model
         * @see Module::findClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this model, return the full path of this model
         */
        public function findModelFullPath($class, $module = null) {
            return $this->findClassInModuleFullFilePath($class, $module, 'models');
        }

        /**
         * Check if in module list can have this library
         * @see Module::findClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this library,  return the full path of this library
         */
        public function findLibraryFullPath($class, $module = null) {
            return $this->findClassInModuleFullFilePath($class, $module, 'libraries');
        }

		
        /**
         * Check if in module list can have this config
         * @see  Module::findNonClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this configuration,  return the full path of this configuration
         */
        public function findConfigFullPath($configuration, $module = null) {
            return $this->findNonClassInModuleFullFilePath($configuration, $module, 'config');
        }

        /**
         * Check if in module list can have this helper
         * @see  Module::findNonClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this helper,  return the full path of this helper
         */
        public function findFunctionFullPath($helper, $module = null) {
            return $this->findNonClassInModuleFullFilePath($helper, $module, 'functions');
        }

        /**
         * Check if in module list can have this view
         * @see  Module::findNonClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this view, path the full path of the view
         */
        public function findViewFullPath($view, $module = null) {
            return $this->findNonClassInModuleFullFilePath($view, $module, 'views');
        }

        /**
         * Check if in module list can have this language
         * @see  Module::findNonClassInModuleFullFilePath
         * @return boolean|string  false or null if no module have this language,  return the full path of this language
         */
        public function findLanguageFullPath($language, $appLang, $module = null) {
            return $this->findNonClassInModuleFullFilePath($language, $module, 'lang', $appLang);
        }

        /**
         * Check if in module list can have the model, controller, library
         * @param  string $class the class name of library, model, controller
         * @param string $module the module name
         * @param string $type the name of the type "controllers", "libraries", "models"
         * @return boolean|string  false or null if no module 
         * have this class, return the full path of the class
         */
        protected function findClassInModuleFullFilePath($class, $module, $type) {
            $class = str_ireplace('.php', '', $class);
            $class = ucfirst($class);
            $classFile = $class . '.php';
            $this->logger->debug('Checking the class [' . $class . '] in module [' . $module . '] for [' . $type . '] ...');
            $filePath = MODULE_PATH . $module . DS . $type . DS . $classFile;
            if (file_exists($filePath)) {
                $this->logger->info('Found class [' . $class . '] in module [' . $module . '] for [' . $type . '] the file path is [' . $filePath . ']');
                return $filePath;
            }
            $this->logger->info('Class [' . $class . '] does not exist in the module [' . $module . '] for [' . $type . ']');
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
        protected function findNonClassInModuleFullFilePath($name, $module, $type, $appLang = null) {
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
            $this->logger->debug('Checking resource [' . $name . '] in module [' . $module . '] for [' . $type . '] ...');
            if (file_exists($filePath)) {
                $this->logger->info('Found resource [' . $name . '] in module [' . $module . '] for [' . $type . '] the file path is [' . $filePath . ']');
                return $filePath;
            }
            $this->logger->info('Resource [' . $name . '] does not exist in the module [' . $module . '] for [' . $type . ']');
            return false;
        }

    }

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
    class Loader extends BaseClass {
		
        /**
         * List of loaded resources
         * @var array
         */
        private $loaded = array();
		

        public function __construct() {
            parent::__construct();
            //add the resources already loaded during application bootstrap
            //in the list to prevent duplicate or loading the resources again.
            $this->loaded = class_loaded();
			
            //Load resources from autoload configuration
            $this->loadResourcesFromAutoloadConfig();
        }

		
        /**
         * Load the model class
         *
         * @param  string $class    the class name to be loaded
         * @param  string $instance the name of the instance to use in super object
         *
         * @return void
         */
        public function model($class, $instance = null) {
            $class = str_ireplace('.php', '', $class);
            $class = trim($class, '/\\');
            $file = ucfirst($class) . '.php';
            $this->logger->debug('Loading model [' . $class . '] ...');
            //************
            if (!$instance) {
                $instance = $this->getModelLibraryInstanceName($class);
            }
            //****************
            if (isset($this->loaded[$instance])) {
                $this->logger->info('Model [' . $class . '] already loaded no need to load it again, cost in performance');
                return;
            }
            $classFilePath = APPS_MODEL_PATH . $file;
            //first check if this model is in the module
            $this->logger->debug('Checking model [' . $class . '] from module list ...');
            //check if the request class contains module name
            $moduleInfo = $this->getModuleInfoForModelLibrary($class);
            $module = $moduleInfo['module'];
            $class  = $moduleInfo['class'];
			
            $moduleModelFilePath = get_instance()->module->findModelFullPath($class, $module);
            if ($moduleModelFilePath) {
                $this->logger->info('Found model [' . $class . '] from module [' . $module . '], the file path is [' . $moduleModelFilePath . '] we will used it');
                $classFilePath = $moduleModelFilePath;
            } else {
                $this->logger->info('Cannot find model [' . $class . '] from modules using the default location');
            }
            $this->logger->info('The model file path to be loaded is [' . $classFilePath . ']');
            if (file_exists($classFilePath)) {
                require_once $classFilePath;
                if (class_exists($class)) {
                    $c = new $class();
                    $obj = & get_instance();
                    $obj->{$instance} = $c;
                    $this->loaded[$instance] = $class;
                    $this->logger->info('Model [' . $class . '] --> ' . $classFilePath . ' loaded successfully.');
                } else {
                    show_error('The file ' . $classFilePath . ' exists but does not contain the class [' . $class . ']');
                }
            } else {
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
        public function library($class, $instance = null, array $params = array()) {
            $class = str_ireplace('.php', '', $class);
            $class = trim($class, '/\\');
            $file = ucfirst($class) . '.php';
            $this->logger->debug('Loading library [' . $class . '] ...');
            if (!$instance) {
                $instance = $this->getModelLibraryInstanceName($class);
            }
            if (isset($this->loaded[$instance])) {
                $this->logger->info('Library [' . $class . '] already loaded no need to load it again, cost in performance');
                return;
            }
            $obj = & get_instance();
            //Check and load Database library
            if (strtolower($class) == 'database') {
                $this->logger->info('This is the Database library ...');
                $obj->{$instance} = & class_loader('Database', 'classes/database');
                $this->loaded[$instance] = $class;
                $this->logger->info('Library Database loaded successfully.');
                return;
            }
            $libraryFilePath = null;
            $this->logger->debug('Check if this is a system library ...');
            if (file_exists(CORE_LIBRARY_PATH . $file)) {
                $libraryFilePath = CORE_LIBRARY_PATH . $file;
                $class = ucfirst($class);
                $this->logger->info('This library is a system library');
            } else {
                $this->logger->info('This library is not a system library');	
                //first check if this library is in the module
                $info = $this->getLibraryPathUsingModuleInfo($class);
                $class = $info['class'];
                $libraryFilePath = $info['path'];
            }
            if (!$libraryFilePath && file_exists(LIBRARY_PATH . $file)) {
                $libraryFilePath = LIBRARY_PATH . $file;
            }
            $this->logger->info('The library file path to be loaded is [' . $libraryFilePath . ']');
            $this->loadLibrary($libraryFilePath, $class, $instance, $params);
        }

        /**
         * Load the helper
         *
         * @param  string $function the helper name to be loaded
         *
         * @return void
         */
        public function functions($function) {
            $function = str_ireplace('.php', '', $function);
            $function = trim($function, '/\\');
            $function = str_ireplace('function_', '', $function);
            $file = 'function_' . $function . '.php';
            $this->logger->debug('Loading helper [' . $function . '] ...');
            if (isset($this->loaded['function_' . $function])) {
                $this->logger->info('Helper [' . $function . '] already loaded no need to load it again, cost in performance');
                return;
            }
            $functionFilePath = null;
            //first check if this helper is in the module
            $this->logger->debug('Checking helper [' . $function . '] from module list ...');
            $moduleInfo = $this->getModuleInfoForFunction($function);
            $module    = $moduleInfo['module'];
            $function  = $moduleInfo['function'];
            if (!empty($moduleInfo['file'])) {
                $file = $moduleInfo['file'];
            }
            $moduleFunctionPath = get_instance()->module->findFunctionFullPath($function, $module);
            if ($moduleFunctionPath) {
                $this->logger->info('Found helper [' . $function . '] from module [' . $module . '], the file path is [' . $moduleFunctionPath . '] we will used it');
                $functionFilePath = $moduleFunctionPath;
            } else {
                $this->logger->info('Cannot find helper [' . $function . '] from modules using the default location');
            }
            if (!$functionFilePath) {
                $functionFilePath = $this->getDefaultFilePathForFunctionLanguage($file, 'function');
            }
            $this->logger->info('The helper file path to be loaded is [' . $functionFilePath . ']');
            if ($functionFilePath) {
                require_once $functionFilePath;
                $this->loaded['function_' . $function] = $functionFilePath;
                $this->logger->info('Helper [' . $function . '] --> ' . $functionFilePath . ' loaded successfully.');
            } else {
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
        public function config($filename) {
            $filename = str_ireplace('.php', '', $filename);
            $filename = trim($filename, '/\\');
            $filename = str_ireplace('config_', '', $filename);
            $file = 'config_' . $filename . '.php';
            $this->logger->debug('Loading configuration [' . $filename . '] ...');
            $configFilePath = CONFIG_PATH . $file;
            //first check if this config is in the module
            $this->logger->debug('Checking config [' . $filename . '] from module list ...');
            $moduleInfo = $this->getModuleInfoForConfig($filename);
            $module    = $moduleInfo['module'];
            $filename  = $moduleInfo['filename'];
            $moduleConfigPath = get_instance()->module->findConfigFullPath($filename, $module);
            if ($moduleConfigPath) {
                $this->logger->info('Found config [' . $filename . '] from module [' . $module . '], the file path is [' . $moduleConfigPath . '] we will used it');
                $configFilePath = $moduleConfigPath;
            } else {
                $this->logger->info('Cannot find config [' . $filename . '] from modules using the default location');
            }
            $this->logger->info('The config file path to be loaded is [' . $configFilePath . ']');
            $config = array();
            if (file_exists($configFilePath)) {
                //note need use require instead of require_once
                require $configFilePath;
                if (!empty($config) && is_array($config)) {
                    get_instance()->config->setAll($config);
                    $this->logger->info('Configuration [' . $configFilePath . '] loaded successfully.');
                    $this->logger->info('The custom application configuration loaded are listed below: ' . stringfy_vars($config));
                    unset($config);
                }
            } else {
                show_error('Unable to find config file [' . $configFilePath . ']');
            }
        }


        /**
         * Load the language
         *
         * @param  string $language the language name to be loaded
         *
         * @return void
         */
        public function lang($language) {
            $language = str_ireplace('.php', '', $language);
            $language = trim($language, '/\\');
            $language = str_ireplace('lang_', '', $language);
            $file = 'lang_' . $language . '.php';
            $this->logger->debug('Loading language [' . $language . '] ...');
            if (isset($this->loaded['lang_' . $language])) {
                $this->logger->info('Language [' . $language . '] already loaded no need to load it again, cost in performance');
                return;
            }
            //get the current language
            $appLang = $this->getAppLang();
            $languageFilePath = null;
            //first check if this language is in the module
            $this->logger->debug('Checking language [' . $language . '] from module list ...');
            $moduleInfo = $this->getModuleInfoForLanguage($language);
            $module    = $moduleInfo['module'];
            $language  = $moduleInfo['language'];
            if (!empty($moduleInfo['file'])) {
                $file = $moduleInfo['file'];
            }
            $moduleLanguagePath = get_instance()->module->findLanguageFullPath($language, $appLang, $module);
            if ($moduleLanguagePath) {
                $this->logger->info('Found language [' . $language . '] from module [' . $module . '], the file path is [' . $moduleLanguagePath . '] we will used it');
                $languageFilePath = $moduleLanguagePath;
            } else {
                $this->logger->info('Cannot find language [' . $language . '] from modules using the default location');
            }
            if (!$languageFilePath) {
                $languageFilePath = $this->getDefaultFilePathForFunctionLanguage($file, 'language', $appLang);
            }
            $this->logger->info('The language file path to be loaded is [' . $languageFilePath . ']');
            $this->loadLanguage($languageFilePath, $language);
        }

        /**
         * Return the current app language by default will use the value from cookie 
         * if can not found will use the default value from configuration
         * @return string the app language like "en", "fr"
         */
        protected function getAppLang() {
            //determine the current language
            $appLang = get_config('default_language');
            //if the language exists in the cookie use it
            $cfgKey = get_config('language_cookie_name');
            $objCookie = & class_loader('Cookie');
            $cookieLang = $objCookie->get($cfgKey);
            if ($cookieLang) {
                $appLang = $cookieLang;
            }
            return $appLang;
        }

        /**
         * Return the default full file path for function, language
         * @param  string $file    the filename
         * @param  string $type    the type can be "function", "language"
         * @param  string $appLang the application language, only if type = "language"
         * @return string|null          the full file path
         */
        protected function getDefaultFilePathForFunctionLanguage($file, $type, $appLang = null){
            $searchDir = null;
            if ($type == 'function') {
               $searchDir = array(FUNCTIONS_PATH, CORE_FUNCTIONS_PATH);
            }
            else if ($type == 'language') {
                $searchDir = array(APP_LANG_PATH, CORE_LANG_PATH);
                $file = $appLang . DS . $file;
            }
            $fullFilePath = null;
            foreach ($searchDir as $dir) {
                $filePath = $dir . $file;
                if (file_exists($filePath)) {
                    $fullFilePath = $filePath;
                    //is already found not to continue
                    break;
                }
            }
            return $fullFilePath;
        }

        /**
         * Get the module using the attribute of super controller "moduleName"
         * @param  string|null $module the module if is not null will return it
         * @return string|null
         */
        protected function getModuleFromSuperController($module){
            $obj = & get_instance();
            if (!$module && !empty($obj->moduleName)) {
                $module = $obj->moduleName;
            }
            return $module;
        }

        /**
         * Get the module information for the model and library to load
         * @param  string $class the full class name like moduleName/className, className,
         * @return array        the module information
         * array(
         * 	'module'=> 'module_name'
         * 	'class' => 'class_name'
         * )
         */
        protected function getModuleInfoForModelLibrary($class) {
            $module = null;
            $path = explode('/', $class);
            if (count($path) >= 2 && in_array($path[0], get_instance()->module->getModuleList())) {
                $module = $path[0];
                $class = ucfirst($path[1]);
            } else {
                $class = ucfirst($class);
            }
            $module = $this->getModuleFromSuperController($module);
            return array(
                        'class' => $class,
                        'module' => $module
                    );
        }

        /**
         * Get the module information for the function to load
         * @param  string $function the function name like moduleName/functionName, functionName,
         * @return array        the module information
         * array(
         * 	'module'=> 'module_name'
         * 	'function' => 'function'
         * 	'file' => 'file'
         * )
         */
        protected function getModuleInfoForFunction($function) {
            $module = null;
            $file = null;
            //check if the request class contains module name
            $path = explode('/', $function);
            if (count($path) >= 2 && in_array($path[0], get_instance()->module->getModuleList())) {
                $module = $path[0];
                $function = 'function_' . $path[1];
                $file = $path[0] . DS . $function . '.php';
            }
            $module = $this->getModuleFromSuperController($module);
            return array(
                        'function' => $function,
                        'module' => $module,
                        'file' => $file
                    );
        }

        /**
         * Get the module information for the language to load
         * @param  string $language the language name like moduleName/languageName, languageName,
         * @return array        the module information
         * array(
         * 	'module'=> 'module_name'
         * 	'language' => 'language'
         * 	'file' => 'file'
         * )
         */
        protected function getModuleInfoForLanguage($language) {
            $module = null;
            $file = null;
            //check if the request class contains module name
            $path = explode('/', $language);
            if (count($path) >= 2 && in_array($path[0], get_instance()->module->getModuleList())) {
                $module = $path[0];
                $language = 'lang_' . $path[1] . '.php';
                $file = $path[0] . DS . $language;
            }
            $module = $this->getModuleFromSuperController($module);
            return array(
                        'language' => $language,
                        'module' => $module,
                        'file' => $file
                    );
        }


        /**
         * Get the module information for the config to load
         * @param  string $filename the filename of the configuration file,
         * @return array        the module information
         * array(
         * 	'module'=> 'module_name'
         * 	'filename' => 'filename'
         * )
         */
        protected function getModuleInfoForConfig($filename) {
            $module = null;
            //check if the request class contains module name
            $path = explode('/', $filename);
            if (count($path) >= 2 && in_array($path[0], get_instance()->module->getModuleList())) {
                $module = $path[0];
                $filename = $path[1] . '.php';
            }
            $module = $this->getModuleFromSuperController($module);
            return array(
                        'filename' => $filename,
                        'module' => $module
                    );
        }

        /**
         * Get the name of model or library instance if is null
         * @param  string $class the class name to determine the instance
         * @return string        the instance name
         */
        protected function getModelLibraryInstanceName($class) {
            //for module
            $instance = null;
            $path = explode('/', $class);
            if (count($path) >= 2) {
                $instance = strtolower($path[1]);
            } else {
                $instance = strtolower($class);
            }
            return $instance;
        }

        /**
         * Get the library file path and class name using the module information
         * @param  string $class the class name
         * @return array        the library file path and class name
         */
        protected function getLibraryPathUsingModuleInfo($class) {
            $libraryFilePath = null;
            $this->logger->debug('Checking library [' . $class . '] from module list ...');
            $moduleInfo = $this->getModuleInfoForModelLibrary($class);
            $module = $moduleInfo['module'];
            $class  = $moduleInfo['class'];
            $moduleLibraryPath = get_instance()->module->findLibraryFullPath($class, $module);
            if ($moduleLibraryPath) {
                $this->logger->info('Found library [' . $class . '] from module [' . $module . '], the file path is [' . $moduleLibraryPath . '] we will used it');
                $libraryFilePath = $moduleLibraryPath;
            } else {
                $this->logger->info('Cannot find library [' . $class . '] from modules using the default location');
            }
            return array(
                        'path' => $libraryFilePath,
                        'class' => $class
                    );
        }

        /**
         * Load the library 
         * @param  string $libraryFilePath the file path of the library to load
         * @param  string $class           the class name
         * @param  string $instance        the instance
         * @param  array  $params          the parameter to use
         * @return void
         */
        protected function loadLibrary($libraryFilePath, $class, $instance, $params = array()) {
            if ($libraryFilePath) {
                    require_once $libraryFilePath;
                if (class_exists($class)) {
                    $c = $params ? new $class($params) : new $class();
                    $obj = & get_instance();
                    $obj->{$instance} = $c;
                    $this->loaded[$instance] = $class;
                    $this->logger->info('Library [' . $class . '] --> ' . $libraryFilePath . ' loaded successfully.');
                } else {
                    show_error('The file ' . $libraryFilePath . ' exists but does not contain the class ' . $class);
                }
            } else {
                show_error('Unable to find library class [' . $class . ']');
            }
        }

        /**
         * Load the language 
         * @param  string $languageFilePath the file path of the language to load
         * @param  string $language           the language name
         * @return void
         */
        protected function loadLanguage($languageFilePath, $language) {
            if ($languageFilePath) {
                    $lang = array();
                require_once $languageFilePath;
                if (!empty($lang) && is_array($lang)) {
                    $this->logger->info('Language file  [' . $languageFilePath . '] contains the valid languages keys add them to language list');
                    //Note: may be here the class 'Lang' not yet loaded
                    $langObj = & class_loader('Lang', 'classes');
                    $langObj->addLangMessages($lang);
                    //free the memory
                    unset($lang);
                }
                $this->loaded['lang_' . $language] = $languageFilePath;
                $this->logger->info('Language [' . $language . '] --> ' . $languageFilePath . ' loaded successfully.');
            } else {
                show_error('Unable to find language [' . $language . ']');
            }
        }

        /**
         * Load the resources autoload array
         * @param  string $method    this object method name to call
         * @param  array  $resources the resource to load
         * @return void            
         */
        protected function loadAutoloadResourcesArray($method, array $resources) {
            foreach ($resources as $name) {
                $this->{$method}($name);
            }
        }

        /**
         * Get all the autoload using the configuration file
         * @return array
         */
        protected function getResourcesFromAutoloadConfig() {
            $autoloads = array();
            $autoloads['config']    = array();
            $autoloads['languages'] = array();
            $autoloads['libraries'] = array();
            $autoloads['models']    = array();
            $autoloads['functions'] = array();
            //loading of the resources from autoload configuration file
            if (file_exists(CONFIG_PATH . 'autoload.php')) {
                $autoload = array();
                require_once CONFIG_PATH . 'autoload.php';
                if (!empty($autoload) && is_array($autoload)) {
                    $autoloads = array_merge($autoloads, $autoload);
                    unset($autoload);
                }
            }
            //loading autoload configuration for modules
            $modulesAutoloads = get_instance()->module->getModulesAutoloadConfig();
            if (!empty($modulesAutoloads) && is_array($modulesAutoloads)) {
                $autoloads = array_merge_recursive($autoloads, $modulesAutoloads);
            }
            return $autoloads;
        }

        /**
         * Load the autoload configuration
         * @return void
         */
        protected function loadResourcesFromAutoloadConfig() {
            $autoloads = array();
            $autoloads['config']    = array();
            $autoloads['languages'] = array();
            $autoloads['libraries'] = array();
            $autoloads['models']    = array();
            $autoloads['functions'] = array();

            $list = $this->getResourcesFromAutoloadConfig();
            $autoloads = array_merge($autoloads, $list);
			
            //config autoload
            $this->loadAutoloadResourcesArray('config', $autoloads['config']);
			
            //languages autoload
            $this->loadAutoloadResourcesArray('lang', $autoloads['languages']);
			
            //libraries autoload
            $this->loadAutoloadResourcesArray('library', $autoloads['libraries']);

            //models autoload
            $this->loadAutoloadResourcesArray('model', $autoloads['models']);
			
            //functions autoload
            $this->loadAutoloadResourcesArray('functions', $autoloads['functions']);
        }
    }

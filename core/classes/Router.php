<?php
    defined('ROOT_PATH') or exit('Access denied');
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

    class Router extends BaseClass {
        /**
         * @var array $pattern: The list of URIs to validate against
         */
        private $pattern = array();

        /**
         * @var array $callback: The list of callback to call
         */
        private $callback = array();

        /**
         * @var string $uriTrim: The char to remove from the URIs
         */
        protected $uriTrim = '/\^$';

        /**
         * @var string $uri: The route URI to use
         */
        protected $uri = '';

        /**
         * The module name of the current request
         * @var string
         */
        protected $module = null;
		
        /**
         * The controller name of the current request
         * @var string
         */
        protected $controller = null;

        /**
         * The controller path
         * @var string
         */
        protected $controllerPath = null;

        /**
         * The method name. The default value is "index"
         * @var string
         */
        protected $method = 'index';

        /**
         * List of argument to pass to the method
         * @var array
         */
        protected $args = array();

        /**
         * List of routes configurations
         * @var array
         */
        protected $routes = array();

        /**
         * The segments array for the current request
         * @var array
         */
        protected $segments = array();

        /**
         * Whether the current request generate 404 error
         * @var boolean
         */
        protected $error404 = false;

        /**
         * The instance of module to use
         * @var object
         */
        protected $moduleInstance = null;

        /**
         * Construct the new Router instance
         *
         * @param object $module the instance of module to use
         */
        public function __construct(Module $module = null) {
            parent::__construct();
            $this->setModuleInstance($module);

            //loading routes for module
            $moduleRouteList = array();
            $modulesRoutes = $this->moduleInstance->getModulesRoutesConfig();
            if (is_array($modulesRoutes)) {
                $moduleRouteList = $modulesRoutes;
                unset($modulesRoutes);
            }
            $this->setRouteConfiguration($moduleRouteList);
            $this->logger->info('The routes configuration are listed below: ' 
                                . stringfy_vars($this->routes));
        }

        /**
         * Return the module instance to use
         * @return object
         */
        public function getModuleInstance() {
            return $this->moduleInstance;
        }

        /**
         * Set the module instance to use
         *
         * @param object $module the new module instance
         * 
         * @return object the current instance
         */
        public function setModuleInstance(Module $module = null) {
            $this->moduleInstance = $module;
            return $this;
        }

        /**
         * Return the 404 error or not
         * @return boolean
         */
        public function is404() {
            return $this->error404;
        }

        /**
         * Get the route patterns
         * @return array
         */
        public function getPattern() {
            return $this->pattern;
        }

        /**
         * Get the route callbacks
         * @return array
         */
        public function getCallback() {
            return $this->callback;
        }

        /**
         * Get the module name
         * @return string
         */
        public function getModule() {
            return $this->module;
        }
		
        /**
         * Get the controller name
         * @return string
         */
        public function getController() {
            return $this->controller;
        }

        /**
         * Get the controller file path
         * @return string
         */
        public function getControllerPath() {
            return $this->controllerPath;
        }

        /**
         * Get the controller method
         * @return string
         */
        public function getMethod() {
            return $this->method;
        }

        /**
         * Get the request arguments
         * @return array
         */
        public function getArgs() {
            return $this->args;
        }

        /**
         * Get the URL segments array
         * @return array
         */
        public function getSegments() {
            return $this->segments;
        }

        /**
         * Get the route URI
         * @return string
         */
        public function getRouteUri() {
            return $this->uri;
        }

        /**
         * Add the URI and callback to the list of URIs to validate
         *
         * @param string $uri the request URI
         * @param string $callback the callback function
         *
         * @return object the current instance
         */
        public function add($uri, $callback) {
            $uri = trim($uri, $this->uriTrim);
            if (in_array($uri, $this->pattern)) {
                $this->logger->warning('The route [' . $uri . '] already added, '
                                        . 'may be adding again can have route conflict');
            }
            $this->pattern[]  = $uri;
            $this->callback[] = $callback;
            return $this;
        }

        /**
         * Remove the route configuration
         *
         * @param string $uri the URI
         *
         * @return object the current instance
         */
        public function removeRoute($uri) {
            $uri = trim($uri, $this->uriTrim);
            $index = array_search($uri, $this->pattern, true);
            if ($index !== false) {
                $this->logger->info('Remove route for uri [' . $uri . '] from the configuration');
                unset($this->pattern[$index]);
                unset($this->callback[$index]);
            }
            return $this;
        }


        /**
         * Remove all the routes from the configuration
         *
         * @return object the current instance
         */
        public function removeAllRoute() {
            $this->logger->info('Remove all routes from the configuration');
            $this->pattern  = array();
            $this->callback = array();
            $this->routes   = array();
            return $this;
        }

        /**
        * Setting the route configuration using the configuration file 
        * and additional configuration from param
        * @param array $overwriteConfig the additional configuration 
        * to overwrite with the existing one
        * @param boolean $useConfigFile whether to use route configuration file
        * 
        * @return object
        */
        public function setRouteConfiguration(array $overwriteConfig = array(), $useConfigFile = true) {
            $route = array();
            if ($useConfigFile && file_exists(CONFIG_PATH . 'routes.php')) {
                require_once CONFIG_PATH . 'routes.php';
            }
            $route = array_merge($route, $overwriteConfig);
            $this->routes = $route;
            //if route is empty remove all configuration
            if (empty($route)) {
                $this->removeAllRoute();
            }
            //Set route informations
            $this->setRouteConfigurationInfos();
            return $this;
        }

        /**
         * Get the route configuration
         * 
         * @return array
         */
        public function getRouteConfiguration() {
            return $this->routes;
        }

        /**
         * Set the route URI to use later
         * @param string $uri the route URI, if is empty will 
         * determine automatically
         * @return object
         */
        public function setRouteUri($uri = '') {
            $routeUri = '';
            $globals = & class_loader('GlobalVar', 'classes');
            $cliArgs = $globals->server('argv');
            if (!empty($uri)) {
                $routeUri = $uri;
            } else if ($globals->server('REQUEST_URI')) {
                $routeUri = $globals->server('REQUEST_URI');
            }
            //if the application is running in CLI mode use the first argument
            else if (IS_CLI && isset($cliArgs[1])) {
                $routeUri = $cliArgs[1];
            } 
            $routeUri = $this->removeSuffixAndQueryStringFromUri($routeUri);
            $this->uri = trim($routeUri, $this->uriTrim);
            return $this;
        }

        /**
         * Set the route segments informations
         * @param array $segements the route segments information
         * 
         * @return object
         */
        public function setRouteSegments(array $segments = array()) {
            if (!empty($segments)) {
                $this->segments = $segments;
            } else if (!empty($this->uri)) {
                $this->segments = explode('/', $this->uri);
            }
            $this->removeDocumentRootFrontControllerFromSegments();
            return $this;
        }

        /**
         * Setting the route parameters like module, controller, method, argument
         * @return object the current instance
         */
        public function determineRouteParamsInformation() {
            $this->logger->debug('Routing process start ...');
			
            //determine route parameters using the config
            $this->determineRouteParamsFromConfig();
			
            //if can not determine the module/controller/method via the 
            //defined routes configuration we will use
            //the URL like http://domain.com/module/controller/method/arg1/arg2/argn 
            if (!$this->controller) {
                $this->logger->info('Cannot determine the routing information ' 
                       . 'using the predefined routes configuration, will use the request URI parameters');
                //determine route parameters using the route URI param
                $this->determineRouteParamsFromRequestUri();
            }
            //Set the controller file path if not yet set
            $this->setControllerFilePath();
            $this->logger->debug('Routing process end.');

            return $this;
        }
        
        /**
         * Routing the request to the correspondant module/controller/method if exists
         * otherwise send 404 error.
         */
        public function processRequest() {
            //Setting the route URI
            $this->setRouteUri();

            //setting route segments
            $this->setRouteSegments();

            $this->logger->info('The final Request URI is [' . implode('/', $this->segments) . ']');

            //determine the route parameters information
            $this->determineRouteParamsInformation();

            //Now load the controller if exists
            $this->loadControllerIfExist();
            
            //if we have 404 error show it
            if ($this->error404) {
                $this->show404Error();
            } else {
                //render the final page to user
                $this->logger->info('Render the final output to the browser');
                get_instance()->response->renderFinalPage();
            }
        }
	    
        /**
         * Set the controller file path if is not set
         * @param string $path the file path if is null will using the route 
         * information
         *
         * @return object the current instance
         */
        public function setControllerFilePath($path = null) {
            if ($path !== null) {
                $this->controllerPath = $path;
                return $this;
            }
            //did we set the controller, so set the controller path
            //if not yet set before 
            if ($this->controller && !$this->controllerPath) {
                $this->logger->debug('Setting the file path for the controller [' . $this->controller . ']');
                $controllerPath = APPS_CONTROLLER_PATH . ucfirst($this->controller) . '.php';
                //if the controller is in module
                if ($this->module) {
                    $path = $this->moduleInstance->findControllerFullPath(ucfirst($this->controller), $this->module);
                    if ($path !== false) {
                        $controllerPath = $path;
                    }
                }
                $this->controllerPath = $controllerPath;
            }
            return $this;
        }

        /**
         * Set the route informations using the configuration
         *
         * @return object the current instance
         */
        protected function setRouteConfigurationInfos() {
            //adding route
            foreach ($this->routes as $pattern => $callback) {
                $this->add($pattern, $callback);
            }
            return $this;
        }

        /**
         * Remove the DOCUMENT_ROOT and front controller from segments if exists
         * @return void
         */
        protected function removeDocumentRootFrontControllerFromSegments(){
            $segment = $this->segments;
            $globals = & class_loader('GlobalVar', 'classes');
            $rootFolder = substr($globals->server('SCRIPT_NAME'), 0, strpos(
                                                                    $globals->server('SCRIPT_NAME'), 
                                                                    basename($globals->server('SCRIPT_FILENAME'))
                                                                ));
            //Remove "/" at the first or folder root
            $rootFolder = trim($rootFolder, $this->uriTrim);
            $segmentString = implode('/', $segment);
            $segmentString = str_ireplace($rootFolder, '', $segmentString);
            //Remove the "/" after replace like "root_folder/foo" => "/foo"
            $segmentString = trim($segmentString, $this->uriTrim);
            if (empty($segmentString)) {
                //So means we are on the home page
                $segment = array();
            } else {
                $segment = explode('/', $segmentString);
            }
            $this->segments = $segment;
            $this->logger->debug('Check if the request URI contains the front controller');
            if (isset($segment[0]) && $segment[0] == SELF) {
                $this->logger->info('The request URI contains the front controller');
                array_shift($segment);
                $this->segments = $segment;
            }
        }

         /**
         * Remove the URL suffix and query string values if exists
         * @param  string $uri the route URI to process
         * @return string      the final route uri after processed
         */
        protected function removeSuffixAndQueryStringFromUri($uri) {
            $this->logger->debug('Check if URL suffix is enabled in the configuration');
            //remove url suffix from the request URI
            $suffix = get_config('url_suffix');
            if ($suffix) {
                $this->logger->info('URL suffix is enabled in the configuration, the value is [' . $suffix . ']');
                $uri = str_ireplace($suffix, '', $uri);
            } 
            if (strpos($uri, '?') !== false) {
                $uri = substr($uri, 0, strpos($uri, '?'));
            }
            return $uri;
        }

        /**
         * Set the route params using the predefined config
         * @param int $findIndex the index in $this->callback
         */
        protected function setRouteParamsUsingPredefinedConfig($findIndex) {
            $callback = $this->callback[$findIndex];
            //only one
            if (preg_match('/^([a-z0-9_]+)$/i', $callback)) {
                $this->logger->info('Callback [' . $callback . '] does not have module or ' 
                    . 'controller definition try to check if is an module or controller');
                //get the module list
                $modules = $this->moduleInstance->getModuleList();
                if (in_array($callback, $modules)) {
                    $this->logger->info('Callback [' . $callback . '] found in module use it as an module');
                    $this->module = $callback;
                } else {
                    $this->logger->info('Callback [' . $callback . '] not found in module use it as an controller');
                    $this->controller = $callback;
                }
                return;
            }
           
            //Check for module
            if (strpos($callback, '#') !== false) {
                $part = explode('#', $callback);
                $this->logger->info('The current request use the module [' . $part[0] . ']');
                $this->module = $part[0];
                array_shift($part);
                //if the second part exists and not empty and don't have @
                //so use it as controller
                if (!empty($part[0]) && strpos($part[0], '@') === false) {
                    $this->controller = $part[0];
                    array_shift($part);
                }
                $callback = implode('', $part);
            }
            
            //Check for controller
            if (strpos($callback, '@') !== false) {
                $part = explode('@', $callback);
                $this->controller = $part[0];
                array_shift($part);
                $callback = implode('', $part);
            }

            //Check for method
            //the remaining will be the method if is not empty
            if (!empty($callback)) {
                $this->method = $callback;
            }
        }

        /**
         * Determine the route parameters from route configuration
         * @return void
         */
        protected function determineRouteParamsFromConfig() {
            $uri = implode('/', $this->segments);
            /*
	    * Generics routes patterns
	    */
            $pattern = array(':num', ':alpha', ':alnum', ':any');
            $replace = array('[0-9]+', '[a-zA-Z]+', '[a-zA-Z0-9]+', '.*');

            $this->logger->debug(
                                    'Begin to loop in the predefined routes configuration ' 
                                    . 'to check if the current request match'
                                    );
            $args = array();
            $findIndex = -1;
            //Cycle through the URIs stored in the array
            foreach ($this->pattern as $index => $uriList) {
                $uriList = str_ireplace($pattern, $replace, $uriList);
                // Check for an existant matching URI
                if (preg_match("#^$uriList$#", $uri, $args)) {
                    $this->logger->info(
                                        'Route found for request URI [' . $uri . '] using the predefined configuration '
                                        . ' [' . $this->pattern[$index] . '] --> [' . $this->callback[$index] . ']'
                                    );
                    $findIndex = $index;
                    //stop here
                    break;
                }
            }
            if($findIndex !== -1){
                /*
                * $args[0] => full string captured by preg_match
                * $args[1], $args[2], $args[n] => contains the value of 
                * (:num), (:alpha), (:alnum), (:any)
                * so need remove the first value $args[0]
                */
                array_shift($args);
                $this->args = $args;
                $this->setRouteParamsUsingPredefinedConfig($findIndex);
            }

            //first if the controller is not set and the module is set use the module name as the controller
            if (!$this->controller && $this->module) {
                $this->logger->info(
                                    'After loop in predefined routes configuration,'
                                    . 'the module name is set but the controller is not set,' 
									. 'so we will use module as the controller'
                                );
                $this->controller = $this->module;
            }
        }

        /**
         * Find file path of the current controller using the current module
         * @return boolean true if the file path is found otherwise false.
         */
        protected function findControllerFullPathUsingCurrentModule(){
            $path = $this->moduleInstance->findControllerFullPath(ucfirst($this->controller), $this->module);
            if (!$path) {
                $this->logger->info('The controller [' . $this->controller . '] not ' 
                                    . 'found in the module, may be will use the module [' . $this->module . '] as controller');
                $this->controller = $this->module;
                return false;
            }
            $this->controllerPath = $path;
            return true;
        }

        /**
         * Set the route information if application does not have modules,
         * or the current request does not use module
         * @return void
         */
        protected function setRouteParamsIfNoModuleOrNotFound(){
            $segment = $this->segments;
            //controller
            if (isset($segment[0])) {
                $this->controller = $segment[0];
                array_shift($segment);
            }
            //method
            if (isset($segment[0])) {
                $this->method = $segment[0];
                array_shift($segment);
            }
            //args
            $this->args = $segment;
        }

        /**
         * Set the route information if application have modules,
         * or the current request use module
         * @return void
         */
        protected function setRouteParamsIfAppHasModuleOrFound(){
            //get the module list
            $modules = $this->moduleInstance->getModuleList();
            $segment = $this->segments;
            if (in_array($segment[0], $modules)) {
                $this->logger->info('Found, the current request use the module [' . $segment[0] . ']');
                $this->module = $segment[0];
                array_shift($segment);
                //check if the second arg is the controller from module
                if (isset($segment[0])) {
                    $this->controller = $segment[0];
                    //check if the request use the same module name and controller
                    if($this->findControllerFullPathUsingCurrentModule()){
                        array_shift($segment);
                    }
                }
                //check for method
                if (isset($segment[0])) {
                    $this->method = $segment[0];
                    array_shift($segment);
                }
                //the remaining is for args
                $this->args = $segment;
            } else {
                $this->logger->info('The current request information is not found in the module list');
                $this->setRouteParamsIfNoModuleOrNotFound();
            }
        }

        /**
         * Determine the route parameters using the server variable "REQUEST_URI"
         * @return void
         */
        protected function determineRouteParamsFromRequestUri() {
            $segment = $this->segments;
            $nbSegment = count($segment);
            //if segment is null so means no need to perform
            if ($nbSegment > 0) {
                //get the module list
                $modules = $this->moduleInstance->getModuleList();
                //first check if no module
                if (empty($modules)) {
                    $this->logger->info('No module was loaded will skip the module checking');
                    $this->setRouteParamsIfNoModuleOrNotFound();
                } else {
                    $this->logger->info('The application contains a loaded module will check if the current request is found in the module list');
                    $this->setRouteParamsIfAppHasModuleOrFound();
                }
                if (!$this->controller && $this->module) {
                    $this->logger->info('After using the request URI the module name is set but the controller is not set so we will use module as the controller');
                    $this->controller = $this->module;
                }
            }
        }

        /**
         * Show error 404 if can not found route for the current request
         * @return void
         */
        protected function show404Error() {
            if (IS_CLI) {
                set_http_status_header(404);
                echo 'Error 404: page not found.';
            } else {
                //create the instance of Controller
                $c404 = new Controller();
                //remove other content set to prevent duplicate view
                $c404->response->setFinalPageContent(null);
                $c404->response->render('404');
                $c404->response->send404();
            }
        }

        /**
         * Load the controller and call it method based on the routing information
         * @return void
         */
        protected function loadControllerIfExist() {
            $e404 = false;
            $classFilePath = $this->controllerPath;
            $controller = ucfirst($this->controller);
            $this->logger->info('The routing information are: module [' . $this->module . '], controller [' . $controller . '], method [' . $this->method . '], args [' . stringfy_vars($this->args) . ']');
            $this->logger->debug('Loading controller [' . $controller . '], the file path is [' . $classFilePath . ']...');
            
            if (file_exists($classFilePath)) {
                require_once $classFilePath;
                if (!class_exists($controller, false)) {
                    $e404 = true;
                    $this->logger->warning('The controller file [' . $classFilePath . '] exists but does not contain the class [' . $controller . ']');
                } else {
                    $controllerInstance = new $controller();
                    $controllerMethod = $this->getMethod();
                    if (!method_exists($controllerInstance, $controllerMethod)) {
                        $e404 = true;
                        $this->logger->warning('The controller [' . $controller . '] exist but does not contain the method [' . $controllerMethod . ']');
                    } else {
                        $this->logger->info('Routing data is set correctly now GO!');
                        call_user_func_array(array($controllerInstance, $controllerMethod), $this->args);
                    }
                }
            } else {
                $this->logger->info('The controller file path [' . $classFilePath . '] does not exist');
                $e404 = true;
            }
            $this->error404 = $e404;
        }
    }

<?php
    defined('ROOT_PATH') or exit('Access denied');
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
         * Construct the new Router instance
         */
        public function __construct() {
            parent::__construct();
			
            //loading routes for module
            $moduleRouteList = array();
            $modulesRoutes = Module::getModulesRoutesConfig();
            if ($modulesRoutes && is_array($modulesRoutes)) {
                $moduleRouteList = $modulesRoutes;
                unset($modulesRoutes);
            }
            $this->setRouteConfiguration($moduleRouteList);
            $this->logger->info('The routes configuration are listed below: ' . stringfy_vars($this->routes));

            //Set route informations
            $this->setRouteConfigurationInfos();
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
                $this->logger->warning('The route [' . $uri . '] already added, may be adding again can have route conflict');
            }
            $this->pattern[] = $uri;
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
            $this->routes = array();
            return $this;
        }

        /**
         * Set the route URI to use later
         * @param string $uri the route URI, if is empty will determine automatically
         * @return object
         */
        public function setRouteUri($uri = '') {
            $routeUri = '';
            if (!empty($uri)) {
                $routeUri = $uri;
            }
            //if the application is running in CLI mode use the first argument
            else if (IS_CLI && isset($_SERVER['argv'][1])) {
                $routeUri = $_SERVER['argv'][1];
            } else if (isset($_SERVER['REQUEST_URI'])) {
                $routeUri = $_SERVER['REQUEST_URI'];
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
			
            //if can not determine the module/controller/method via the defined routes configuration we will use
            //the URL like http://domain.com/module/controller/method/arg1/arg2
            if (!$this->controller) {
                $this->logger->info('Cannot determine the routing information using the predefined routes configuration, will use the request URI parameters');
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
                        //render the final page to user
                        $this->logger->info('Render the final output to the browser');
                        get_instance()->response->renderFinalPage();
                    }
                }
            } else {
                $this->logger->info('The controller file path [' . $classFilePath . '] does not exist');
                $e404 = true;
            }
            if ($e404) {
                if (IS_CLI) {
                    set_http_status_header(404);
                    echo 'Error 404: page not found.';
                } else {
                    $response = & class_loader('Response', 'classes');
                    $response->send404();
                }
            }
        }


        /**
         * Setting the route configuration using the configuration file and additional configuration from param
         * @param array $overwriteConfig the additional configuration to overwrite with the existing one
         * @param boolean $useConfigFile whether to use route configuration file
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
            return $this;
        }

            /**
             * Get the route configuration
             * @return array
             */
        public function getRouteConfiguration() {
            return $this->routes;
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
            if ($this->controller && !$this->controllerPath) {
                $this->logger->debug('Setting the file path for the controller [' . $this->controller . ']');
                $controllerPath = APPS_CONTROLLER_PATH . ucfirst($this->controller) . '.php';
                //if the controller is in module
                if ($this->module) {
                    $path = Module::findControllerFullPath(ucfirst($this->controller), $this->module);
                    if ($path !== false) {
                        $controllerPath = $path;
                    }
                }
                $this->controllerPath = $controllerPath;
            }
            return $this;
        }

        /**
         * Remove the DOCUMENT_ROOT and front controller from segments if exists
         * @return void
         */
        protected function removeDocumentRootFrontControllerFromSegments(){
            $segment = $this->segments;
            $baseUrl = get_config('base_url');
            //check if the app is not in DOCUMENT_ROOT
            if (isset($segment[0]) && stripos($baseUrl, $segment[0]) !== false) {
                array_shift($segment);
                $this->segments = $segment;
            }
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
            // Cycle through the URIs stored in the array
            foreach ($this->pattern as $index => $uriList) {
                $uriList = str_ireplace($pattern, $replace, $uriList);
                // Check for an existant matching URI
                if (preg_match("#^$uriList$#", $uri, $args)) {
                    $this->logger->info(
                                        'Route found for request URI [' . $uri . '] using the predefined configuration '
                                        . ' [' . $this->pattern[$index] . '] --> [' . $this->callback[$index] . ']'
                                    );
                    $findIndex = $index;
                    // stop here
                    break;
                }
            }
            if($findIndex !== -1){
                array_shift($args);
                //check if this contains an module
                $moduleControllerMethod = explode('#', $this->callback[$findIndex]);
                if (count($moduleControllerMethod) >= 2) {
                    $this->logger->info('The current request use the module [' . $moduleControllerMethod[0] . ']');
                    $this->module = $moduleControllerMethod[0];
                    $moduleControllerMethod = explode('@', $moduleControllerMethod[1]);
                } else {
                    $this->logger->info('The current request does not use the module');
                    $moduleControllerMethod = explode('@', $this->callback[$findIndex]);
                }
                if (is_array($moduleControllerMethod)) {
                    if (isset($moduleControllerMethod[0])) {
                        $this->controller = $moduleControllerMethod[0]; 
                    }
                    if (isset($moduleControllerMethod[1])) {
                        $this->method = $moduleControllerMethod[1];
                    }
                    $this->args = $args;
                }
            }

            //first if the controller is not set and the module is set use the module name as the controller
            if (!$this->controller && $this->module) {
                $this->logger->info(
                                    'After loop in predefined routes configuration, 
									the module name is set but the controller is not set, 
									so we will use module as the controller'
                                );
                $this->controller = $this->module;
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
                $modules = Module::getModuleList();
                //first check if no module
                if (empty($modules)) {
                    $this->logger->info('No module was loaded will skip the module checking');
                    //the application don't use module
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
                } else {
                    $this->logger->info('The application contains a loaded module will check if the current request is found in the module list');
                    if (in_array($segment[0], $modules)) {
                        $this->logger->info('Found, the current request use the module [' . $segment[0] . ']');
                        $this->module = $segment[0];
                        array_shift($segment);
                        //check if the second arg is the controller from module
                        if (isset($segment[0])) {
                            $this->controller = $segment[0];
                            //check if the request use the same module name and controller
                            $path = Module::findControllerFullPath(ucfirst($this->controller), $this->module);
                            if (!$path) {
                                $this->logger->info('The controller [' . $this->controller . '] not found in the module, may be will use the module [' . $this->module . '] as controller');
                                $this->controller = $this->module;
                            } else {
                                $this->controllerPath = $path;
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
                }
                if (!$this->controller && $this->module) {
                    $this->logger->info('After using the request URI the module name is set but the controller is not set so we will use module as the controller');
                    $this->controller = $this->module;
                }
            }
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
    }

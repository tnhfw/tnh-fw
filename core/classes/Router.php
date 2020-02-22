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

	class Router {
		
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
		protected $segments;

		/**
		 * The logger instance
		 * @var Log
		 */
		private $logger;

		/**
		 * Construct the new Router instance
		 */
		public function __construct(){
			$this->setLoggerFromParamOrCreateNewInstance(null);
			
			//loading routes for module
			$moduleRouteList = array();
			$modulesRoutes = Module::getModulesRoutes();
			if($modulesRoutes && is_array($modulesRoutes)){
				$moduleRouteList = $modulesRoutes;
				unset($modulesRoutes);
			}
			$this->setRouteConfiguration($moduleRouteList);
			$this->logger->info('The routes configuration are listed below: ' . stringfy_vars($this->routes));

			//Set route parameters
			$this->setRouteParams();
		}

		/**
		* Add the URI and callback to the list of URIs to validate
		*
		* @param string $uri the request URI
		* @param object $callback the callback function
		*/
		public function add($uri, $callback) {
			$uri = trim($uri, $this->uriTrim);
			if(in_array($uri, $this->pattern)){
				$this->logger->warning('The route [' . $uri . '] already added, may be adding again can have route conflict');
			}
			$this->pattern[] = $uri;
			$this->callback[] = $callback;
		}

		/**
		 * Get the module name
		 * @return string
		 */
		public function getModule(){
			return $this->module;
		}
		
		/**
		 * Get the controller name
		 * @return string
		 */
		public function getController(){
			return $this->controller;
		}

		/**
		 * Get the controller file path
		 * @return string
		 */
		public function getControllerPath(){
			return $this->controllerPath;
		}

		/**
		 * Get the controller method
		 * @return string
		 */
		public function getMethod(){
			return $this->method;
		}

		/**
		 * Get the request arguments
		 * @return array
		 */
		public function getArgs(){
			return $this->args;
		}

		/**
		 * Get the URL segments array
		 * @return array
		 */
		public function getSegments(){
			return $this->segments;
		}

		/**
		 * Routing the request to the correspondant module/controller/method if exists
		 * otherwise send 404 error.
		 */
		public function run() {
			$benchmark =& class_loader('Benchmark');
			$benchmark->mark('ROUTING_PROCESS_START');
			$this->logger->debug('Routing process start ...');
			$segment = $this->segments;
			$baseUrl = get_config('base_url');
			//check if the app is not in DOCUMENT_ROOT
			if(isset($segment[0]) && stripos($baseUrl, $segment[0]) != false){
				array_shift($segment);
				$this->segments = $segment;
			}
			$this->logger->debug('Check if the request URI contains the front controller');
			if(isset($segment[0]) && $segment[0] == SELF){
				$this->logger->info('The request URI contains the front controller');
				array_shift($segment);
				$this->segments = $segment;
			}
			else{
				$this->logger->info('The request URI does not contain the front controller');
			}
			$uri = implode('/', $segment);
			$this->logger->info('The final Request URI is [' . $uri . ']' );
			//generic routes
			$pattern = array(':num', ':alpha', ':alnum', ':any');
			$replace = array('[0-9]+', '[a-zA-Z]+', '[a-zA-Z0-9]+', '.*');
			$this->logger->debug('Begin to loop in the predefined routes configuration to check if the current request match');
			// Cycle through the URIs stored in the array
			foreach ($this->pattern as $index => $uriList) {
				$uriList = str_ireplace($pattern, $replace, $uriList);
				// Check for an existant matching URI
				if (preg_match("#^$uriList$#", $uri, $args)) {
					$this->logger->info('Route found for request URI [' . $uri . '] using the predefined configuration [' . $this->pattern[$index] . '] --> [' . $this->callback[$index] . ']');
					array_shift($args);
					//check if this contains an module
					$moduleControllerMethod = explode('#', $this->callback[$index]);
					if(is_array($moduleControllerMethod) && count($moduleControllerMethod) >= 2){
						$this->logger->info('The current request use the module [' .$moduleControllerMethod[0]. ']');
						$this->module = $moduleControllerMethod[0];
						$moduleControllerMethod = explode('@', $moduleControllerMethod[1]);
					}
					else{
						$this->logger->info('The current request does not use the module');
						$moduleControllerMethod = explode('@', $this->callback[$index]);
					}
					if(is_array($moduleControllerMethod)){
						if(isset($moduleControllerMethod[0])){
							$this->controller = $moduleControllerMethod[0];	
						}
						if(isset($moduleControllerMethod[1])){
							$this->method = $moduleControllerMethod[1];
						}
						$this->args = $args;
					}
					// stop here
					break;
				}
			}
			//first if the controller is not set and the module is set use the module name as the controller
			if(! $this->getController() && $this->getModule()){
				$this->logger->info('After loop in predefined routes configuration, the module name is set but the controller is not set, so we will use module as the controller');
				$this->controller = $this->getModule();
			}
			//if can not determine the module/controller/method via the defined routes configuration we will use
			//the URL like http://domain.com/module/controller/method/arg1/arg2
			if(! $this->getController()){
				$this->logger->info('Cannot determine the routing information using the predefined routes configuration, will use the request URI parameters');
				$nbSegment = count($segment);
				//if segment is null so means no need to perform
				if($nbSegment > 0){
					//get the module list
					$modules = Module::getModuleList();
					//first check if no module
					if(! $modules){
						$this->logger->info('No module was loaded will skip the module checking');
						//the application don't use module
						//controller
						if(isset($segment[0])){
							$this->controller = $segment[0];
							array_shift($segment);
						}
						//method
						if(isset($segment[0])){
							$this->method = $segment[0];
							array_shift($segment);
						}
						//args
						$this->args = $segment;
					}
					else{
						$this->logger->info('The application contains a loaded module will check if the current request is found in the module list');
						if(in_array($segment[0], $modules)){
							$this->logger->info('Found, the current request use the module [' . $segment[0] . ']');
							$this->module = $segment[0];
							array_shift($segment);
							//check if the second arg is the controller from module
							if(isset($segment[0])){
								$this->controller = $segment[0];
								//check if the request use the same module name and controller
								$path = Module::findControllerFullPath(ucfirst($this->getController()), $this->getModule());
								if(! $path){
									$this->logger->info('The controller [' . $this->getController() . '] not found in the module, may be will use the module [' . $this->getModule() . '] as controller');
									$this->controller = $this->getModule();
								}
								else{
									$this->controllerPath = $path;
									array_shift($segment);
								}
							}
							//check for method
							if(isset($segment[0])){
								$this->method = $segment[0];
								array_shift($segment);
							}
							//the remaining is for args
							$this->args = $segment;
						}
						else{
							$this->logger->info('The current request information is not found in the module list');
							//controller
							if(isset($segment[0])){
								$this->controller = $segment[0];
								array_shift($segment);
							}
							//method
							if(isset($segment[0])){
								$this->method = $segment[0];
								array_shift($segment);
							}
							//args
							$this->args = $segment;
						}
					}
				}
			}
			if(! $this->getController() && $this->getModule()){
				$this->logger->info('After using the request URI the module name is set but the controller is not set so we will use module as the controller');
				$this->controller = $this->getModule();
			}
			//did we set the controller, so set the controller path
			if($this->getController() && ! $this->getControllerPath()){
				$this->logger->debug('Setting the file path for the controller [' . $this->getController() . ']');
				//if it is the module controller
				if($this->getModule()){
					$this->controllerPath = Module::findControllerFullPath(ucfirst($this->getController()), $this->getModule());
				}
				else{
					$this->controllerPath = APPS_CONTROLLER_PATH . ucfirst($this->getController()) . '.php';
				}
			}
			$controller = ucfirst($this->getController());
			$this->logger->info('The routing information are: module [' . $this->getModule() . '], controller [' . $controller . '], method [' . $this->getMethod() . '], args [' . stringfy_vars($this->args) . ']');
			$classFilePath = $this->getControllerPath();
			$this->logger->debug('Loading controller [' . $controller . '], the file path is [' . $classFilePath . ']...');
			$benchmark->mark('ROUTING_PROCESS_END');
			$e404 = false;
			if(file_exists($classFilePath)){
				require_once $classFilePath;
				if(! class_exists($controller, false)){
					$e404 = true;
					$this->logger->info('The controller file [' .$classFilePath. '] exists but does not contain the class [' . $controller . ']');
				}
				else{
					$controllerInstance = new $controller();
					$controllerMethod = $this->getMethod();
					if(! method_exists($controllerInstance, $controllerMethod)){
						$e404 = true;
						$this->logger->info('The controller [' . $controller . '] exist but does not contain the method [' . $controllerMethod . ']');
					}
					else{
						$this->logger->info('Routing data is set correctly now GO!');
						call_user_func_array(array($controllerInstance, $controllerMethod), $this->getArgs());
						$obj = & get_instance();
						//render the final page to user
						$this->logger->info('Render the final output to the browser');
						$obj->response->renderFinalPage();
					}
				}
			}
			else{
				$this->logger->info('The controller file path [' . $classFilePath . '] does not exist');
				$e404 = true;
			}
			if($e404){
				$response =& class_loader('Response', 'classes');
				$response->send404();
			}
		}

	/**
     * Return the Log instance
     * @return Log
     */
    public function getLogger(){
      return $this->logger;
    }

    /**
     * Set the log instance
     * @param Log $logger the log object
	 * @return object
     */
    public function setLogger($logger){
      $this->logger = $logger;
      return $this;
    }

    /**
    * Setting the route configuration using the configuration file and additional configuration from param
    * @param array $overwriteConfig the additional configuration to overwrite with the existing one
    * @param boolean $useConfigFile whether to use route configuration file
	* @return object
    */
    public function setRouteConfiguration(array $overwriteConfig = array(), $useConfigFile = true){
        $route = array();
        if ($useConfigFile && file_exists(CONFIG_PATH . 'routes.php')){
            require_once CONFIG_PATH . 'routes.php';
        }
        $route = array_merge($route, $overwriteConfig);
        $this->routes = $route;
		return $this;
    }

    /**
     * Set the route paramaters using the configuration
     */
    protected function setRouteParams(){
    	//adding route
		foreach($this->routes as $pattern => $callback){
			$this->add($pattern, $callback);
		}
		
		//here use directly the variable $_SERVER
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		$this->logger->debug('Check if URL suffix is enabled in the configuration');
		//remove url suffix from the request URI
		$suffix = get_config('url_suffix');
		if ($suffix) {
			$this->logger->info('URL suffix is enabled in the configuration, the value is [' . $suffix . ']' );
			$uri = str_ireplace($suffix, '', $uri);
		} 
		if (strpos($uri, '?') !== false){
			$uri = substr($uri, 0, strpos($uri, '?'));
		}
		$uri = trim($uri, $this->uriTrim);
		$this->segments = explode('/', $uri);
	}

	/**
     * Set the Log instance using argument or create new instance
     * @param object $logger the Log instance if not null
     */
    protected function setLoggerFromParamOrCreateNewInstance(Log $logger = null){
      if ($logger !== null){
        $this->logger = $logger;
      }
      else{
          $this->logger =& class_loader('Log', 'classes');
          $this->logger->setLogger('Library::Router');
      }
    }
}

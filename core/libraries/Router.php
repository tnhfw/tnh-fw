<?php
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

		protected $controller = null;

		protected $method = 'index';

		protected $args = array();

		protected $routes = array();

		protected $request = null;

		private $logger;


		function __construct(){
			if(!class_exists('Log')){
	            //here the Log class is not yet loaded
	            //load it manually
	            require_once CORE_LIBRARY_PATH . 'Log.php';
	        }
	        $this->logger = new Log();
	        $this->logger->setLogger('Library::Router');
	         $this->logger->debug('Try to load the routes configuration');
			if(file_exists(CONFIG_PATH.'routes.php')){
				 $this->logger->info('Routes configuration file [' .CONFIG_PATH. 'routes.php] exists require it');
				require_once CONFIG_PATH.'routes.php';
				if(!empty($route) && is_array($route)){
					$this->routes = $route;
					 $this->logger->info('The routes configuration are listed below: ' . stringfy_vars($route));
				}
				else{
					show_error('No routing configuration found in routes.php');
				}
			}
			else{
				show_error('Unable to find the route configuration file');
			}
			
			$this->request = new Request();
			foreach($this->routes as $pattern => $callback){
				$this->add($pattern, $callback);
			}

		}
		
		/**
		* Adds the function and callback to the list of URIs to validate
		*
		* @param string $uri The main request
		* @param object $callback An anonymous function
		*/
		public function add($uri, $callback) {
			$uri = trim($uri, $this->uriTrim);
			$this->pattern[] = $uri;
			$this->callback[] = $callback;
		}


		public function getController(){
			return $this->controller;
		}

		public function getMethod(){
			return $this->method;
		}

		public function getRequest(){
			return $this->request;
		}

		public function getArgs(){
			return $this->args;
		}

		public function dispatch() {
			 $this->logger->debug('Routing process start ...');
			$uri = $this->getRequest()->requestUri();
			$this->logger->info('Request URI [' .$uri. ']' );

			$this->logger->debug('Check if URL suffix is enabled in the configuration');
			//remove url suffix from the request URI
			if($suffix = Config::get('url_suffix')){
				$this->logger->info('URL suffix is enabled in the configuration, the value is [' .$suffix. ']' );
				$uri = str_ireplace($suffix, '', $uri);
			}
			else{
				$this->logger->info('URL suffix is not enabled in the configuration');
			}
			if(strpos($uri, '?') != false){
				$uri = substr($uri, 0, strpos($uri, '?'));
			}
			$uri = trim($uri, $this->uriTrim);
			$temp = explode('/', $uri);
			$base_url = Config::get('base_url');
			if(isset($temp[0]) && stripos($base_url, $temp[0]) != false){
				array_shift($temp);
			}
			$this->logger->debug('Check if the front controller is enabled in the configuration');
			if(isset($temp[0]) && $temp[0] == Config::get('front_controller')){
				$this->logger->info('front controller is enabled in the configuration, the value is [' .$temp[0]. ']' );
				array_shift($temp);
			}
			$uri = implode('/', $temp);
			$this->logger->info('The final Request URI is [' .$uri. ']' );
			$pattern = array(':num', ':alpha', ':alnum', ':any');
			$replace = array('[0-9]+', '[a-zA-Z]+', '[a-zA-Z0-9]+', '.*');
			// Cycle through the URIs stored in the array
			foreach ($this->pattern as $index => $uriList) {
				$uriList = str_ireplace($pattern, $replace, $uriList);
				// Check for a existant matching URI
				if (preg_match("#^$uriList$#", $uri, $args)) {
					array_shift($args);
					$temp = explode('/', $this->callback[$index]);

					if(isset($temp[0])){
						$this->controller = $temp[0];
					}

					if(isset($temp[1])){
						$this->method = $temp[1];
					}
					$this->args = $args;
					// stop here
					break;
				}
			}
			$e404 = false;
			$controller = ucfirst($this->getController());
			Loader::controller($controller);
			$this->logger->info('The routing information are: controller [' .$controller. '], method [' .$this->method. '], args [' .stringfy_vars($this->args). ']' );
			if(!class_exists($controller)){
				$e404 = true;
				$this->logger->warning('Controller class [' .$controller. '] does not exist');
			}
			else{
				$c = new $controller();
				$m = $this->getMethod();
				if(!method_exists($c, $m)){
					$e404 = true;
					$this->logger->warning('Controller class [' .$controller. '] exist but the method [' .$m. '] does not exists');
				}
				else{
					$this->logger->info('Routing data is set correctly lunch the application');
					call_user_func_array(array($c, $m), $this->getArgs());
				}
			}

			if($e404){
				Response::send404();
			}
		}
	}
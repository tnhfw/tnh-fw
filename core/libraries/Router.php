<?php

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


	function __construct(){
		if(file_exists(CONFIG_PATH.'routes.php')){
			require_once CONFIG_PATH.'routes.php';
			if(!empty($route) && is_array($route)){
				$this->routes = $route;
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
		$uri = $this->getRequest()->requestUri();
		if(strpos($uri, '?') != false){
			$uri = substr($uri, 0, strpos($uri, '?'));
		}
		$uri = trim($uri, $this->uriTrim);
		$temp = explode('/', $uri);
		$base_url = Config::get('base_url');
		if(isset($temp[0]) && stripos($base_url, $temp[0]) != false){
			array_shift($temp);
		}
		if(isset($temp[0]) && $temp[0] == Config::get('front_controller')){
			array_shift($temp);
		}
		$uri = implode('/', $temp);
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

		if(!class_exists($controller)){
			$e404 = true;
		}
		else{
			$c = new $controller();
			$m = $this->getMethod();
			if(!method_exists($c, $m)){
				$e404 = true;
			}
			else{
				call_user_func_array(array($c, $m), $this->getArgs());
			}
		}

		if($e404){
			Response::send404();
		}
	}
}

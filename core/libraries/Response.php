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

	class Response{
		/**
		 * The list of request header to send with response
		 * @var array
		 */
		private static $headers = array();

		/**
		 * The logger instance
		 * @var Log
		 */
		private static $logger;
		
		/**
		 * Construct new response instance
		 */
		public function __construct(){
		}

		/**
		 * Get the logger singleton instance
		 * @return Log the logger instance
		 */
		private static function getLogger(){
			if(static::$logger == null){
				static::$logger[0] =& class_loader('Log');
				static::$logger[0]->setLogger('Library::Response');
			}
			return static::$logger[0];
		}

		/**
		 * Send the HTTP Response headers
		 * @param  integer $http_code the HTTP status code
		 * @param  array   $headers   the additional headers to add to the existing header list
		 */
		public static function sendHeaders($http_code = 200, array $headers = array()){
			set_http_status_header($http_code);
			static::setHeaders($headers);
			if(!headers_sent()){
				foreach(static::getHeaders() as $key => $value){
					header($key .':'.$value);
				}
			}
		}

		/**
		 * Get the list of the headers
		 * @return array the headers list
		 */
		public static function getHeaders(){
			return static::$headers;
		}

		/**
		 * Redirect user in the specified page
		 * @param  string $path the URL or URI to be redirect to
		 */
		public static function redirect($path = ''){
			$logger = static::getLogger();
			$url = Url::site_url($path);
			$logger->info('Redirect to URL [' .$url. ']');
			if(!headers_sent()){
				header('Location:'.$url);
				exit;
			}
			else{
				echo '<script>
						location.href = "'.$url.'";
					</script>';
			}
		}

		/**
		 * Get the header value for the given name
		 * @param  string $name the header name
		 * @return string       the header value
		 */
		public static function getHeader($name){
			return isset(static::$headers[$name])?static::$headers[$name] : null;
		}


		/**
		 * Set the header value for the specified name
		 * @param string $name  the header name
		 * @param string $value the header value to be set
		 */
		public static function setHeader($name,$value){
			static::$headers[$name] = $value;
		}

		/**
		 * Set the headers using array
		 * @param array $headers the list of the headers to set. 
		 * Note: this will merge with the existing headers
		 */
		public static function setHeaders(array $headers){
			static::$headers = array_merge(static::getHeaders(), $headers);
		}

		/**
		 * Render the view to display or return the content
		 * @param  string  $view   the view name or path
		 * @param  array   $data   the variable data to use in the view
		 * @param  boolean $return whether to return the view generated content or display it directly
		 * @return void|string          if $return is true will return the view content otherwise
		 * will display the view content.
		 */
		public function render($view, array $data = array(), $return = false){
			$logger = static::getLogger();
			$view = str_ireplace('.php', '', $view);
			$view = trim($view, '/\\');
			$viewFile = $view . '.php';
			$path = APPS_VIEWS_PATH . $viewFile;
			$obj = & get_instance();
			$cacheEnable = get_config('cache_enable', false);
			$viewCacheEnable = !empty($obj->view_cache_enable);
			$viewCacheTtl = !empty($obj->view_cache_ttl) ? $obj->view_cache_ttl : 0;
			$cacheInstance = null;
			$cacheKey = null;
			$cached = false;
			$dispatcher = $obj->eventdispatcher;
			if($cacheEnable && $viewCacheEnable){
				$cacheKey = md5(Url::current() . $view . serialize($data) . serialize($return));
				$logger->debug('Getting the view [' . $view . '] content from cache ...');
				$cacheInstance = $obj->{strtolower(get_config('cache_handler'))};
				$content = $cacheInstance->get($cacheKey);
				if($content){
					$cached = true;
					$logger->info('The view [' . $view . '] content already cached just use it');
					//dispatch
					$event = $dispatcher->dispatch('VIEW_LOADED', new Event('VIEW_LOADED', $content, true));
					$content = (!empty($event->payload) && $event instanceof Event) ? $event->payload: null;
					if(empty($content)){
						$logger->warning('The view content is empty after dispatch to Event Listeners.');
					}
					if($return){
						return $content;
					}
					echo $content;
				}
			}
			else{
				$logger->info('The cache is not enabled for the view [' . $view . '] skipping');
			}
			if(! $cached){
				//check in module first
				$logger->debug('Checking the view [' . $view . '] from module list ...');
				$mod = null;
				//check if the request class contains module name
				if(strpos($view, '/') !== false){
					$viewPath = explode('/', $view);
					if(isset($viewPath[0]) && in_array($viewPath[0], Module::getModuleList())){
						$mod = $viewPath[0];
						array_shift($viewPath);
						$view = implode('/', $viewPath);
						$viewFile = $view . '.php';
					}
				}
				if(! $mod && !empty($obj->module_name)){
					$mod = $obj->module_name;
				}
				if($mod){
					$moduleViewPath = Module::findViewFullPath($view, $mod);
					if($moduleViewPath){
						$path = $moduleViewPath;
						$logger->info('Found view [' . $view . '] in module [' .$mod. '], the file path is [' .$moduleViewPath. '] we will used it');
					}
					else{
						$logger->info('Cannot find view [' . $view . '] in module [' .$mod. '] using the default location');
					}
				}
				else{
					$logger->info('The current request does not use module using the default location.');
				}
				$logger->info('The view file path to be loaded is [' . $path . ']');
				$found = false;
				if(file_exists($path)){
					$obj = & get_instance();
					foreach($obj as $key => $value){
						if(!isset($this->{$key})){
							$this->{$key} = & $obj->{$key};
						}
					}
					ob_start();
					extract($data);
					require_once $path;
					$content = ob_get_clean();
					if($cacheEnable && $viewCacheEnable){
						$logger->debug('Save the view [' . $view . '] content into the cache ...');
						$cacheInstance->set($cacheKey, $content, $viewCacheTtl ? $viewCacheTtl : get_config('cache_ttl'));
					}
					//dispatch
					$event = $dispatcher->dispatch('VIEW_LOADED', new Event('VIEW_LOADED', $content, true));
					$content = (!empty($event->payload) && $event instanceof Event) ? $event->payload: null;
					if(empty($content)){
						$logger->warning('The view content is empty after dispatch to Event Listeners.');
					}
					if($return){
						return $content;
					}
					echo $content;
					$found = true;
				}
				if(!$found){
					show_error('Unable to find view [' .$view . ']');
				}
			}
		}

		/**
		 * Send the HTTP 404 error if can not found the 
		 * routing information for the current request
		 */
		public static function send404(){
			/********* for logs **************/
			//can't use $obj = & get_instance()  here because the global super object will be available until
			//the main controller is loaded even for Loader::library('xxxx');
			$logger = static::getLogger();
			$r =& class_loader('Request');
			$b =& class_loader('Browser');
			$browser = $b->getPlatform().', '.$b->getBrowser().' '.$b->getVersion();
			
			//here can't use Loader::functions just include the helper manually
			require_once CORE_FUNCTIONS_PATH . 'function_user_agent.php';

			$str = '[404 page not found] : ';
			$str .= ' Unable to find the request page ['.$r->requestUri().']. The visitor IP address ['.get_ip(). '], browser ['.$browser.']';
			$logger->error($str);
			/***********************************/
			$path = CORE_VIEWS_PATH . '404.php';
			if(file_exists($path)){
				static::sendHeaders(404);
				ob_start();
				require_once $path;
				$output = ob_get_clean();
				echo $output;
			}
			else{
				show_error('The 404 view [' .$path. '] does not exist');
			}
		}

		/**
		 * Display the error to user
		 * @param  array  $data the error information
		 */
		public static function sendError(array $data = array()){
			$path = CORE_VIEWS_PATH . 'errors.php';
			if(file_exists($path)){
				static::sendHeaders(503);
				ob_start();
				extract($data);
				require_once $path;
				$output = ob_get_clean();
				echo $output;
			}
			else{
				show_error('The error view [' .$path. '] does not exist');
			}
		}
	}

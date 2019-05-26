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
		 * The final page content to display to user
		 * @var string
		 */
		private $_pageRender = null;
		
		/**
		 * The current request URL
		 * @var string
		 */
		private $_currentUrl = null;
		
		/**
		 * The current request URL cache key
		 * @var string
		 */
		private $_currentUrlCacheKey = null;
		
		/**
		* Whether we can compress the output using Gzip
		* @var boolean
		*/
		private static $_canCompressOutput = false;
		
		/**
		 * Construct new response instance
		 */
		public function __construct(){
			$this->_currentUrl =  (! empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '' )
					. (! empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '' );
					
			$this->_currentUrlCacheKey = md5($this->_currentUrl);
			
			static::$_canCompressOutput = get_config('compress_output')
										  && isset($_SERVER['HTTP_ACCEPT_ENCODING']) 
										  && stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false 
										  && extension_loaded('zlib')
										  && (bool) ini_get('zlib.output_compression') == false;
		}

		/**
		 * Get the logger singleton instance
		 * @return Log the logger instance
		 */
		private static function getLogger(){
			if(static::$logger == null){
				static::$logger[0] =& class_loader('Log', 'classes');
				static::$logger[0]->setLogger('Library::Response');
			}
			return static::$logger[0];
		}

		/**
		 * Send the HTTP Response headers
		 * @param  integer $httpCode the HTTP status code
		 * @param  array   $headers   the additional headers to add to the existing headers list
		 */
		public static function sendHeaders($httpCode = 200, array $headers = array()){
			set_http_status_header($httpCode);
			static::setHeaders($headers);
			if(! headers_sent()){
				foreach(static::getHeaders() as $key => $value){
					header($key .': '.$value);
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
		 * Get the header value for the given name
		 * @param  string $name the header name
		 * @return string       the header value
		 */
		public static function getHeader($name){
			return array_key_exists($name, static::$headers) ? static::$headers[$name] : null;
		}


		/**
		 * Set the header value for the specified name
		 * @param string $name  the header name
		 * @param string $value the header value to be set
		 */
		public static function setHeader($name, $value){
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
		 * Redirect user in the specified page
		 * @param  string $path the URL or URI to be redirect to
		 */
		public static function redirect($path = ''){
			$logger = static::getLogger();
			$url = Url::site_url($path);
			$logger->info('Redirect to URL [' .$url. ']');
			if(! headers_sent()){
				header('Location: '.$url);
				exit;
			}
			else{
				echo '<script>
						location.href = "'.$url.'";
					</script>';
			}
		}

		/**
		 * Render the view to display later or return the content
		 * @param  string  $view   the view name or path
		 * @param  array   $data   the variable data to use in the view
		 * @param  boolean $return whether to return the view generated content or display it directly
		 * @return void|string          if $return is true will return the view content otherwise
		 * will display the view content.
		 */
		public function render($view, $data = array(), $return = false){
			$logger = static::getLogger();
			//convert data to an array
			$data = ! is_array($data) ? (array) $data : $data;
			$view = str_ireplace('.php', '', $view);
			$view = trim($view, '/\\');
			$viewFile = $view . '.php';
			$path = APPS_VIEWS_PATH . $viewFile;
			
			//super instance
			$obj = & get_instance();
			
			if(Module::hasModule()){
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
				if(! $mod && !empty($obj->moduleName)){
					$mod = $obj->moduleName;
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
			}
			$logger->info('The view file path to be loaded is [' . $path . ']');
			$found = false;
			if(file_exists($path)){
				foreach(get_object_vars($obj) as $key => $value){
					if(! isset($this->{$key})){
						$this->{$key} = & $obj->{$key};
					}
				}
				ob_start();
				extract($data);
				//need use require() instead of require_once because can load this view many time
				require $path;
				$content = ob_get_clean();
				if($return){
					return $content;
				}
				$this->_pageRender .= $content;
				$found = true;
			}
			if(! $found){
				show_error('Unable to find view [' .$view . ']');
			}
		}
		
		/**
		* Send the final page output to user
		*/
		public function renderFinalPage(){
			$logger = static::getLogger();
			$obj = & get_instance();
			$cachePageStatus = get_config('cache_enable', false) && !empty($obj->view_cache_enable);
			$dispatcher = $obj->eventdispatcher;
			$content = $this->_pageRender;
			
			//dispatch
			$event = $dispatcher->dispatch(new Event('FINAL_VIEW_READY', $content, true));
			$content = ! empty($event->payload) ? $event->payload : null;
			if(empty($content)){
				$logger->warning('The view content is empty after dispatch to Event Listeners.');
			}
			
			//check whether need save the page into cache.
			if($cachePageStatus){
				//current page URL
				$url = $this->_currentUrl;
				//Cache view Time to live in second
				$viewCacheTtl = !empty($obj->view_cache_ttl) ? $obj->view_cache_ttl : get_config('cache_ttl');
				//the cache handler instance
				$cacheInstance = $obj->cache;
				//the current page cache key for identification
				$cacheKey = $this->_currentUrlCacheKey;
				
				$logger->debug('Save the page content for URL [' . $url . '] into the cache ...');
				$cacheInstance->set($cacheKey, $content, $viewCacheTtl);
				
				//get the cache information to prepare header to send to browser
				$cacheInfo = $cacheInstance->getInfo($cacheKey);
				if($cacheInfo){
					$lastModified = $cacheInfo['mtime'];
					$expire = $cacheInfo['expire'];
					$maxAge = $expire - time();
					static::setHeader('Pragma', 'public');
					static::setHeader('Cache-Control', 'max-age=' . $maxAge . ', public');
					static::setHeader('Expires', gmdate('D, d M Y H:i:s', $expire).' GMT');
					static::setHeader('Last-modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT');	
				}
			}
			
			// Parse out the elapsed time and memory usage,
			// then swap the pseudo-variables with the data
			$elapsedTime = $obj->benchmark->elapsedTime('APP_EXECUTION_START', 'APP_EXECUTION_END');
			$memoryUsage	= round($obj->benchmark->memoryUsage('APP_EXECUTION_START', 'APP_EXECUTION_END') / 1024 / 1024, 6) . 'MB';
			$content = str_replace(array('{elapsed_time}', '{memory_usage}'), array($elapsedTime, $memoryUsage), $content);
			
			//compress the output if is available
			if (static::$_canCompressOutput){
				ob_start('ob_gzhandler');
			}
			else{
				ob_start();
			}
			static::sendHeaders(200);
			echo $content;
			@ob_end_flush();
		}
		
		/**
		* Send the final page output to user if is cached
		*/
		public function renderFinalPageFromCache(&$cache){
			$logger = static::getLogger();
			$url = $this->_currentUrl;					
			//the current page cache key for identification
			$pageCacheKey = $this->_currentUrlCacheKey;
			
			$logger->debug('Checking if the page content for the URL [' . $url . '] is cached ...');
			//get the cache information to prepare header to send to browser
			$cacheInfo = $cache->getInfo($pageCacheKey);
			if($cacheInfo){
				$lastModified = $cacheInfo['mtime'];
				$expire = $cacheInfo['expire'];
				$maxAge = $expire - $_SERVER['REQUEST_TIME'];
				static::setHeader('Pragma', 'public');
				static::setHeader('Cache-Control', 'max-age=' . $maxAge . ', public');
				static::setHeader('Expires', gmdate('D, d M Y H:i:s', $expire).' GMT');
				static::setHeader('Last-modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT');
				if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $lastModified <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
					$logger->info('The cache page content is not yet expire for the URL [' . $url . '] send 304 header to browser');
					static::sendHeaders(304);
					exit;
				}
				else{
					$logger->info('The cache page content is expired or the browser don\'t send the HTTP_IF_MODIFIED_SINCE header for the URL [' . $url . '] send cache headers to tell the browser');
					static::sendHeaders(200);
					//get the cache content
					$content = $cache->get($pageCacheKey);
					if($content){
						$logger->info('The page content for the URL [' . $url . '] already cached just display it');
						//load benchmark class
						$benchmark = & class_loader('Benchmark');
						
						// Parse out the elapsed time and memory usage,
						// then swap the pseudo-variables with the data
						$elapsedTime = $benchmark->elapsedTime('APP_EXECUTION_START', 'APP_EXECUTION_END');
						$memoryUsage	= round($benchmark->memoryUsage('APP_EXECUTION_START', 'APP_EXECUTION_END') / 1024 / 1024, 6) . 'MB';
						$content = str_replace(array('{elapsed_time}', '{memory_usage}'), array($elapsedTime, $memoryUsage), $content);
						
						///display the final output
						//compress the output if is available
						if (static::$_canCompressOutput){
							ob_start('ob_gzhandler');
						}
						else{
							ob_start();
						}
						echo $content;
						@ob_end_flush();
						exit;
					}
					else{
						$logger->info('The page cache content for the URL [' . $url . '] is not valid may be already expired');
						$cache->delete($pageCacheKey);
					}
				}
			}
		}
		
		/**
		* Get the final page to be render
		* @return string
		*/
		public function getFinalPageRendered(){
			return $this->_pageRender;
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
			$request =& class_loader('Request', 'classes');
			$userAgent =& class_loader('Browser');
			$browser = $userAgent->getPlatform().', '.$userAgent->getBrowser().' '.$userAgent->getVersion();
			
			//here can't use Loader::functions just include the helper manually
			require_once CORE_FUNCTIONS_PATH . 'function_user_agent.php';

			$str = '[404 page not found] : ';
			$str .= ' Unable to find the request page [' . $request->requestUri() . ']. The visitor IP address [' . get_ip() . '], browser [' . $browser . ']';
			$logger->error($str);
			/***********************************/
			$path = CORE_VIEWS_PATH . '404.php';
			if(file_exists($path)){
				//compress the output if is available
				if (static::$_canCompressOutput){
					ob_start('ob_gzhandler');
				}
				else{
					ob_start();
				}
				require_once $path;
				$output = ob_get_clean();
				static::sendHeaders(404);
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
				//compress the output if exists
				if (static::$_canCompressOutput){
					ob_start('ob_gzhandler');
				}
				else{
					ob_start();
				}
				extract($data);
				require_once $path;
				$output = ob_get_clean();
				static::sendHeaders(503);
				echo $output;
			}
			else{
				//can't use show_error() at this time because some dependencies not yet loaded and to prevent loop
				set_http_status_header(503);
				echo 'The error view [' . $path . '] does not exist';
				exit(1);
			}
		}
	}

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

	class Response extends BaseStaticClass{

		/**
		 * The list of request header to send with response
		 * @var array
		 */
		private static $headers = array();
		
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
			$currentUrl = '';
			if (! empty($_SERVER['REQUEST_URI'])){
				$currentUrl = $_SERVER['REQUEST_URI'];
			}
			if (! empty($_SERVER['QUERY_STRING'])){
				$currentUrl .= '?' . $_SERVER['QUERY_STRING'];
			}
			$this->_currentUrl =  $currentUrl;
					
			$this->_currentUrlCacheKey = md5($this->_currentUrl);
			
			self::$_canCompressOutput = get_config('compress_output')
										  && isset($_SERVER['HTTP_ACCEPT_ENCODING']) 
										  && stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false 
										  && extension_loaded('zlib')
										  && (bool) ini_get('zlib.output_compression') === false;
		}

		
		/**
		 * Send the HTTP Response headers
		 * @param  integer $httpCode the HTTP status code
		 * @param  array   $headers   the additional headers to add to the existing headers list
		 */
		public static function sendHeaders($httpCode = 200, array $headers = array()){
			set_http_status_header($httpCode);
			self::setHeaders($headers);
			if(! headers_sent()){
				foreach(self::getHeaders() as $key => $value){
					header($key .': '.$value);
				}
			}
		}

		/**
		 * Get the list of the headers
		 * @return array the headers list
		 */
		public static function getHeaders(){
			return self::$headers;
		}

		/**
		 * Get the header value for the given name
		 * @param  string $name the header name
		 * @return string|null       the header value
		 */
		public static function getHeader($name){
			if(array_key_exists($name, self::$headers)){
				return self::$headers[$name];
			}
			return null;
		}


		/**
		 * Set the header value for the specified name
		 * @param string $name  the header name
		 * @param string $value the header value to be set
		 */
		public static function setHeader($name, $value){
			self::$headers[$name] = $value;
		}

		/**
		 * Set the headers using array
		 * @param array $headers the list of the headers to set. 
		 * Note: this will merge with the existing headers
		 */
		public static function setHeaders(array $headers){
			self::$headers = array_merge(self::getHeaders(), $headers);
		}
		
		/**
		 * Redirect user to the specified page
		 * @param  string $path the URL or URI to be redirect to
		 */
		public static function redirect($path = ''){
			$logger = self::getLogger();
			$url = Url::site_url($path);
			$logger->info('Redirect to URL [' .$url. ']');
			if(! headers_sent()){
				header('Location: '.$url);
				exit;
			}
			echo '<script>
					location.href = "'.$url.'";
				</script>';
		}

		/**
		 * Render the view to display later or return the content
		 * @param  string  $view   the view name or path
		 * @param  array|object   $data   the variable data to use in the view
		 * @param  boolean $return whether to return the view generated content or display it directly
		 * @return void|string          if $return is true will return the view content otherwise
		 * will display the view content.
		 */
		public function render($view, $data = null, $return = false){
			$logger = self::getLogger();
			//convert data to an array
			$data = (array) $data;
			$view = str_ireplace('.php', '', $view);
			$view = trim($view, '/\\');
			$viewFile = $view . '.php';
			$path = APPS_VIEWS_PATH . $viewFile;
			
			//check in module first
			$logger->debug('Checking the view [' . $view . '] from module list ...');
			$moduleInfo = $this->getModuleInfoForView($view);
			$module    = $moduleInfo['module'];
			$view  = $moduleInfo['view'];
			
			$moduleViewPath = Module::findViewFullPath($view, $module);
			if($moduleViewPath){
				$path = $moduleViewPath;
				$logger->info('Found view [' . $view . '] in module [' .$module. '], the file path is [' .$moduleViewPath. '] we will used it');
			}
			else{
				$logger->info('Cannot find view [' . $view . '] in module [' .$module. '] using the default location');
			}
			
			$logger->info('The view file path to be loaded is [' . $path . ']');
			
			/////////
			if($return){
				return $this->loadView($path, $data, true);
			}
			$this->loadView($path, $data, false);
		}

		
		/**
		* Send the final page output to user
		*/
		public function renderFinalPage(){
			$logger = self::getLogger();
			$obj = & get_instance();
			$cachePageStatus = get_config('cache_enable', false) && !empty($obj->view_cache_enable);
			$dispatcher = $obj->eventdispatcher;
			$content = $this->_pageRender;
			if(! $content){
				$logger->warning('The final view content is empty.');
				return;
			}
			//dispatch
			$event = $dispatcher->dispatch(new EventInfo('FINAL_VIEW_READY', $content, true));
			$content = null;
			if(! empty($event->payload)){
				$content = $event->payload;
			}
			if(empty($content)){
				$logger->warning('The view content is empty after dispatch to event listeners.');
			}
			//remove unsed space in the content
			$content = preg_replace('~>\s*\n\s*<~', '><', $content);
			//check whether need save the page into cache.
			if($cachePageStatus){
				$this->savePageContentIntoCache($content);
			}
			$content = $this->replaceElapseTimeAndMemoryUsage($content);

			//compress the output if is available
			$type = null;
			if (self::$_canCompressOutput){
				$type = 'ob_gzhandler';
			}
			ob_start($type);
			self::sendHeaders(200);
			echo $content;
			ob_end_flush();
		}

		
		/**
		* Send the final page output to user if is cached
		* @param object $cache the cache instance
		*
		* @return boolean whether the page content if available or not
		*/
		public function renderFinalPageFromCache(&$cache){
			$logger = self::getLogger();
			//the current page cache key for identification
			$pageCacheKey = $this->_currentUrlCacheKey;
			
			$logger->debug('Checking if the page content for the URL [' . $this->_currentUrl . '] is cached ...');
			//get the cache information to prepare header to send to browser
			$cacheInfo = $cache->getInfo($pageCacheKey);
			if($cacheInfo){
				$status = $this->sendCacheNotYetExpireInfoToBrowser($cacheInfo);
				if($status === false){
					return $this->sendCachePageContentToBrowser($cache);
				}
				return true;
			}
			return false;
		}
	
		
		/**
		* Get the final page to be rendered
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
			$logger = self::getLogger();
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
				$type = null;
				if (self::$_canCompressOutput){
					$type = 'ob_gzhandler';
				}
				ob_start($type);
				require_once $path;
				$output = ob_get_clean();
				self::sendHeaders(404);
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
				//compress the output if is available
				$type = null;
				if (self::$_canCompressOutput){
					$type = 'ob_gzhandler';
				}
				ob_start($type);
				extract($data);
				require_once $path;
				$output = ob_get_clean();
				self::sendHeaders(503);
				echo $output;
			}
			else{
				//can't use show_error() at this time because some dependencies not yet loaded and to prevent loop
				set_http_status_header(503);
				echo 'The error view [' . $path . '] does not exist';
			}
		}

		/**
		 * Send the cache not yet expire to browser
		 * @param  array $cacheInfo the cache information
		 * @return boolean            true if the information is sent otherwise false
		 */
		protected function sendCacheNotYetExpireInfoToBrowser($cacheInfo){
			if(! empty($cacheInfo)){
				$logger = self::getLogger();
				$lastModified = $cacheInfo['mtime'];
				$expire = $cacheInfo['expire'];
				$maxAge = $expire - $_SERVER['REQUEST_TIME'];
				self::setHeader('Pragma', 'public');
				self::setHeader('Cache-Control', 'max-age=' . $maxAge . ', public');
				self::setHeader('Expires', gmdate('D, d M Y H:i:s', $expire).' GMT');
				self::setHeader('Last-modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT');
				if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $lastModified <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
					$logger->info('The cache page content is not yet expire for the URL [' . $this->_currentUrl . '] send 304 header to browser');
					self::sendHeaders(304);
					return true;
				}
			}
			return false;
		}

		/**
		 * Set the value of '{elapsed_time}' and '{memory_usage}'
		 * @param  string $content the page content
		 * @return string          the page content after replace 
		 * '{elapsed_time}', '{memory_usage}'
		 */
		protected function replaceElapseTimeAndMemoryUsage($content){
			//load benchmark class
			$benchmark = & class_loader('Benchmark');
			
			// Parse out the elapsed time and memory usage,
			// then swap the pseudo-variables with the data
			$elapsedTime = $benchmark->elapsedTime('APP_EXECUTION_START', 'APP_EXECUTION_END');
			$memoryUsage	= round($benchmark->memoryUsage('APP_EXECUTION_START', 'APP_EXECUTION_END') / 1024 / 1024, 6) . 'MB';
			return str_replace(array('{elapsed_time}', '{memory_usage}'), array($elapsedTime, $memoryUsage), $content);	
		}

		/**
		 * Send the page content from cache to browser
		 * @param object $cache the cache instance
		 * @return boolean     the status of the operation
		 */
		protected function sendCachePageContentToBrowser(&$cache){
			$logger = self::getLogger();
			$logger->info('The cache page content is expired or the browser does not send the HTTP_IF_MODIFIED_SINCE header for the URL [' . $this->_currentUrl . '] send cache headers to tell the browser');
			self::sendHeaders(200);
			//current page cache key
			$pageCacheKey = $this->_currentUrlCacheKey;
			//get the cache content
			$content = $cache->get($pageCacheKey);
			if($content){
				$logger->info('The page content for the URL [' . $this->_currentUrl . '] already cached just display it');
				$content = $this->replaceElapseTimeAndMemoryUsage($content);
				///display the final output
				//compress the output if is available
				$type = null;
				if (self::$_canCompressOutput){
					$type = 'ob_gzhandler';
				}
				ob_start($type);
				echo $content;
				ob_end_flush();
				return true;
			}
			$logger->info('The page cache content for the URL [' . $this->_currentUrl . '] is not valid may be already expired');
			$cache->delete($pageCacheKey);
			return false;
		}

		/**
		 * Save the content of page into cache
		 * @param  string $content the page content to be saved
		 * @return void
		 */
		protected function savePageContentIntoCache($content){
			$obj = & get_instance();
			$logger = self::getLogger();

			//current page URL
			$url = $this->_currentUrl;
			//Cache view Time to live in second
			$viewCacheTtl = get_config('cache_ttl');
			if (!empty($obj->view_cache_ttl)){
				$viewCacheTtl = $obj->view_cache_ttl;
			}
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
				self::setHeader('Pragma', 'public');
				self::setHeader('Cache-Control', 'max-age=' . $maxAge . ', public');
				self::setHeader('Expires', gmdate('D, d M Y H:i:s', $expire).' GMT');
				self::setHeader('Last-modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT');	
			}
		}
		

		/**
		 * Get the module information for the view to load
		 * @param  string $view the view name like moduleName/viewName, viewName
		 * 
		 * @return array        the module information
		 * array(
		 * 	'module'=> 'module_name'
		 * 	'view' => 'view_name'
		 * 	'viewFile' => 'view_file'
		 * )
		 */
		protected  function getModuleInfoForView($view){
			$module = null;
			$viewFile = null;
			$obj = & get_instance();
			//check if the request class contains module name
			if(strpos($view, '/') !== false){
				$viewPath = explode('/', $view);
				if(isset($viewPath[0]) && in_array($viewPath[0], Module::getModuleList())){
					$module = $viewPath[0];
					array_shift($viewPath);
					$view = implode('/', $viewPath);
					$viewFile = $view . '.php';
				}
			}
			if(! $module && !empty($obj->moduleName)){
				$module = $obj->moduleName;
			}
			return array(
						'view' => $view,
						'module' => $module,
						'viewFile' => $viewFile
					);
		}

		/**
		 * Render the view page
		 * @see  Response::render
		 * @return void|string
		 */
		protected  function loadView($path, array $data = array(), $return = false){
			$found = false;
			if(file_exists($path)){
				//super instance
				$obj = & get_instance();
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
					//remove unused html space 
					return preg_replace('~>\s*\n\s*<~', '><', $content);
				}
				$this->_pageRender .= $content;
				$found = true;
			}
			if(! $found){
				show_error('Unable to find view [' .$path . ']');
			}
		}

	}

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

    class Response extends BaseStaticClass {

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
         * Construct new instance
         */
        public function __construct() {
            $globals = & class_loader('GlobalVar', 'classes');
            $currentUrl = '';
            if ($globals->server('REQUEST_URI')) {
                $currentUrl = $globals->server('REQUEST_URI');
            }
            if ($globals->server('QUERY_STRING')) {
                $currentUrl .= '?' . $globals->server('QUERY_STRING');
            }
            $this->_currentUrl = $currentUrl;		
            $this->_currentUrlCacheKey = md5($this->_currentUrl);
			
            self::$_canCompressOutput = get_config('compress_output')
                                          && $globals->server('HTTP_ACCEPT_ENCODING') !== null 
                                          && stripos($globals->server('HTTP_ACCEPT_ENCODING'), 'gzip') !== false 
                                          && extension_loaded('zlib')
                                          && (bool) ini_get('zlib.output_compression') === false;
        }

		
        /**
         * Send the HTTP Response headers
         * @param  integer $httpCode the HTTP status code
         * @param  array   $headers   the additional headers to add to the existing headers list
         */
        public static function sendHeaders($httpCode = 200, array $headers = array()) {
            set_http_status_header($httpCode);
            self::setHeaders($headers);
            self::setRequiredHeaders();
            //@codeCoverageIgnoreStart
            //not available when running in CLI mode
            if (!headers_sent()) {
                foreach (self::getHeaders() as $key => $value) {
                    header($key . ': ' . $value);
                }
            }
            //@codeCoverageIgnoreEnd
        }

        /**
         * Get the list of the headers
         * @return array the headers list
         */
        public static function getHeaders() {
            return self::$headers;
        }

        /**
         * Get the header value for the given name
         * @param  string $name the header name
         * @return string|null       the header value
         */
        public static function getHeader($name) {
            if (array_key_exists($name, self::$headers)) {
                return self::$headers[$name];
            }
            return null;
        }


        /**
         * Set the header value for the specified name
         * @param string $name  the header name
         * @param string $value the header value to be set
         */
        public static function setHeader($name, $value) {
            self::$headers[$name] = $value;
        }

        /**
         * Set the headers using array
         * @param array $headers the list of the headers to set. 
         * Note: this will merge with the existing headers
         */
        public static function setHeaders(array $headers) {
            self::$headers = array_merge(self::getHeaders(), $headers);
        }
		
        /**
         * Redirect user to the specified page
         * @param  string $path the URL or URI to be redirect to
         * @codeCoverageIgnore
         */
        public static function redirect($path = '') {
            $logger = self::getLogger();
            $url = Url::site_url($path);
            $logger->info('Redirect to URL [' . $url . ']');
            if (!headers_sent()) {
                header('Location: ' . $url);
                exit;
            }
            echo '<script>
					location.href = "'.$url . '";
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
        public function render($view, $data = null, $return = false) {
            $logger = self::getLogger();
            //convert data to an array
            $data = (array) $data;
            $view = str_ireplace('.php', '', $view);
            $view = trim($view, '/\\');
            $viewFile = $view . '.php';
            $path = null;
			
            //check in module first
            $logger->debug('Checking the view [' . $view . '] from module list ...');
            $moduleInfo = $this->getModuleInfoForView($view);
            $module = $moduleInfo['module'];
            $view = $moduleInfo['view'];
			
            $moduleViewPath = get_instance()->module->findViewFullPath($view, $module);
            if ($moduleViewPath) {
                $path = $moduleViewPath;
                $logger->info('Found view [' . $view . '] in module [' . $module . '], the file path is [' . $moduleViewPath . '] we will used it');
            } else {
                $logger->info('Cannot find view [' . $view . '] in module [' . $module . '] using the default location');
            }
			if (!$path) {
                $path = $this->getDefaultFilePathForView($viewFile);
            }
            $logger->info('The view file path to be loaded is [' . $path . ']');
			
            if ($return) {
                return $this->loadView($path, $data, true);
            }
            $this->loadView($path, $data, false);
        }

		
        /**
         * Send the final page output to user
         */
        public function renderFinalPage() {
            $logger = self::getLogger();
            $obj = & get_instance();
            $cachePageStatus = get_config('cache_enable', false) && !empty($obj->view_cache_enable);
            $dispatcher = $obj->eventdispatcher;
            $content = $this->_pageRender;
            if (!$content) {
                $logger->warning('The final view content is empty.');
                return;
            }
            //dispatch
            $event = $dispatcher->dispatch(new EventInfo('FINAL_VIEW_READY', $content, true));
            $content = null;
            if (!empty($event->payload)) {
                $content = $event->payload;
            }
            if (empty($content)) {
                $logger->warning('The view content is empty after dispatch to event listeners.');
            }
            //check whether need save the page into cache.
            if ($cachePageStatus) {
                $this->savePageContentIntoCache($content);
            }
            //update content
            $this->_pageRender = $content;

            $content = $this->replaceElapseTimeAndMemoryUsage($content);

            //compress the output if is available
            $type = null;
            if (self::$_canCompressOutput) {
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
        public function renderFinalPageFromCache(&$cache) {
            $logger = self::getLogger();
            //the current page cache key for identification
            $pageCacheKey = $this->_currentUrlCacheKey;
			
            $logger->debug('Checking if the page content for the URL [' . $this->_currentUrl . '] is cached ...');
            //get the cache information to prepare header to send to browser
            $cacheInfo = $cache->getInfo($pageCacheKey);
            if ($cacheInfo) {
                $status = $this->sendCacheNotYetExpireInfoToBrowser($cacheInfo);
                if ($status === false) {
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
        public function getFinalPageRendered() {
            return $this->_pageRender;
        }

         /**
         * Set the final page to be rendered
         * @param string $finalPage the content of the final page
         * 
         * @return object
         */
        public function setFinalPageContent($finalPage) {
            $this->_pageRender = $finalPage;
            return $this;
        }

        /**
         * Send the HTTP 404 error if can not found the 
         * routing information for the current request
         */
        public function send404() {
            $logger = self::getLogger();
            $obj = & get_instance();
            $cachePageStatus = get_config('cache_enable', false) && !empty($obj->view_cache_enable);
            $dispatcher = $obj->eventdispatcher;
            $content = $this->_pageRender;
            if (!$content) {
                $logger->warning('The final view content is empty.');
                return;
            }
            //dispatch
            $dispatcher->dispatch(new EventInfo('PAGE_NOT_FOUND'));
            //check whether need save the page into cache.
            if ($cachePageStatus) {
                $this->savePageContentIntoCache($content);
            }
            $content = $this->replaceElapseTimeAndMemoryUsage($content);

            /**************************************** save the content into logs **************/
            $bwsr = & class_loader('Browser');
            $browser = $bwsr->getPlatform() . ', ' . $bwsr->getBrowser() . ' ' . $bwsr->getVersion();
            $obj->loader->functions('user_agent');
            $str = '[404 page not found] : ';
            $str .= ' Unable to find the request page [' . $obj->request->requestUri() . ']. The visitor IP address [' . get_ip() . '], browser [' . $browser . ']';
            
            //Todo fix the issue the logger name change after load new class
            $logger = self::getLogger();
            $logger->error($str);
            /**********************************************************************/

            //compress the output if is available
            $type = null;
            if (self::$_canCompressOutput) {
                $type = 'ob_gzhandler';
            }
            ob_start($type);
            self::sendHeaders(404);
            echo $content;
            ob_end_flush();
        }

        /**
         * Display the error to user
         */
        public function sendError() {
            $logger = self::getLogger();
            $content = $this->_pageRender;
            if (!$content) {
                $logger->warning('The final view content is empty.');
                return;
            }
            $content = $this->replaceElapseTimeAndMemoryUsage($content);
            //compress the output if is available
            $type = null;
            if (self::$_canCompressOutput) {
                $type = 'ob_gzhandler';
            }
            ob_start($type);
            self::sendHeaders(503);
            echo $content;
            ob_end_flush();
        }

         /**
         * Return the default full file path for view
         * @param  string $file    the filename
         * 
         * @return string|null          the full file path
         */
        protected static function getDefaultFilePathForView($file){
            $searchDir = array(APPS_VIEWS_PATH, CORE_VIEWS_PATH);
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
         * Send the cache not yet expire to browser
         * @param  array $cacheInfo the cache information
         * @return boolean            true if the information is sent otherwise false
         */
        protected function sendCacheNotYetExpireInfoToBrowser($cacheInfo) {
            if (!empty($cacheInfo)) {
                $logger = self::getLogger();
                $lastModified = $cacheInfo['mtime'];
                $expire = $cacheInfo['expire'];
                $globals = & class_loader('GlobalVar', 'classes');
                $maxAge = $expire - (double) $globals->server('REQUEST_TIME');
                self::setHeader('Pragma', 'public');
                self::setHeader('Cache-Control', 'max-age=' . $maxAge . ', public');
                self::setHeader('Expires', gmdate('D, d M Y H:i:s', $expire) . ' GMT');
                self::setHeader('Last-modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
                $headerModifiedSince = $globals->server('HTTP_IF_MODIFIED_SINCE');
                if (!empty($headerModifiedSince) && $lastModified <= strtotime($headerModifiedSince)) {
                    $logger->info('The cache page content is not yet expire for the URL [' . $this->_currentUrl . '] send 304 header to browser');
                    self::sendHeaders(304);
                    return true;
                }
            }
            return false;
        }

        /**
         * Send the page content from cache to browser
         * @param object $cache the cache instance
         * @return boolean     the status of the operation
         */
        protected function sendCachePageContentToBrowser(&$cache) {
            $logger = self::getLogger();
            $logger->info('The cache page content is expired or the browser does not send the HTTP_IF_MODIFIED_SINCE header for the URL [' . $this->_currentUrl . '] send cache headers to tell the browser');
            self::sendHeaders(200);
            //current page cache key
            $pageCacheKey = $this->_currentUrlCacheKey;
            //get the cache content
            $content = $cache->get($pageCacheKey);
            if ($content) {
                $logger->info('The page content for the URL [' . $this->_currentUrl . '] already cached just display it');
                $content = $this->replaceElapseTimeAndMemoryUsage($content);
                ///display the final output
                //compress the output if is available
                $type = null;
                if (self::$_canCompressOutput) {
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
        protected function savePageContentIntoCache($content) {
            $obj = & get_instance();
            $logger = self::getLogger();

            //current page URL
            $url = $this->_currentUrl;
            //Cache view Time to live in second
            $viewCacheTtl = get_config('cache_ttl');
            if (!empty($obj->view_cache_ttl)) {
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
            if ($cacheInfo) {
                $lastModified = $cacheInfo['mtime'];
                $expire = $cacheInfo['expire'];
                $maxAge = $expire - time();
                self::setHeader('Pragma', 'public');
                self::setHeader('Cache-Control', 'max-age=' . $maxAge . ', public');
                self::setHeader('Expires', gmdate('D, d M Y H:i:s', $expire) . ' GMT');
                self::setHeader('Last-modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');	
            }
        }

        /**
         * Set the value of '{elapsed_time}' and '{memory_usage}'
         * @param  string $content the page content
         * @return string          the page content after replace 
         * '{elapsed_time}', '{memory_usage}'
         */
        protected function replaceElapseTimeAndMemoryUsage($content) {
            //load benchmark class
            $benchmark = & class_loader('Benchmark');
            
            // Parse out the elapsed time and memory usage,
            // then swap the pseudo-variables with the data
            $elapsedTime = $benchmark->elapsedTime('APP_EXECUTION_START', 'APP_EXECUTION_END');
            $memoryUsage    = round($benchmark->memoryUsage('APP_EXECUTION_START', 'APP_EXECUTION_END') / 1024 / 1024, 6) . 'MB';
            return str_replace(array('{elapsed_time}', '{memory_usage}'), array($elapsedTime, $memoryUsage), $content); 
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
        protected  function getModuleInfoForView($view) {
            $module = null;
            $viewFile = null;
            $obj = & get_instance();
            //check if the request class contains module name
            $viewPath = explode('/', $view);
            if (count($viewPath) >= 2 && in_array($viewPath[0], get_instance()->module->getModuleList())) {
                $module = $viewPath[0];
                array_shift($viewPath);
                $view = implode('/', $viewPath);
                $viewFile = $view . '.php';
            }
            if (!$module && !empty($obj->moduleName)) {
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
        protected  function loadView($path, array $data = array(), $return = false) {
            $found = false;
            if (file_exists($path)) {
                //super instance
                $obj = & get_instance();
                if ($obj instanceof Controller) {
                    foreach (get_object_vars($obj) as $key => $value) {
                        if (!isset($this->{$key})) {
                            $this->{$key} = & $obj->{$key};
                        }
                    }
                }
                ob_start();
                extract($data);
                //need use require() instead of require_once because can load this view many time
                require $path;
                $content = ob_get_clean();
                if ($return) {
                    return $content;
                }
                $this->_pageRender .= $content;
                $found = true;
            }
            if (!$found) {
                show_error('Unable to find view [' . $path . ']');
            }
        }

         /**
         * Set the mandory headers, like security, etc.
         */
        protected static function setRequiredHeaders() {
            $requiredHeaders = array(
                                'X-XSS-Protection' => '1; mode=block',
                                'X-Frame-Options'  => 'SAMEORIGIN'
                            );
            foreach ($requiredHeaders as $key => $value) {
               if (!isset(self::$headers[$key])) {
                    self::$headers[$key] = $value;
               } 
            }
        }
    }

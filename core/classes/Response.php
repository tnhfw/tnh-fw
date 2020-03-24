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

    class Response extends BaseClass {

        /**
         * The list of request header to send with response
         * @var array
         */
        private $headers = array();
		
        /**
         * The final page content to display to user
         * @var string
         */
        private $finalPageContent = null;
		
        /**
         * The current request URL
         * @var string
         */
        private $currentUrl = null;
		
        /**
         * The current request URL cache key
         * @var string
         */
        private $currentUrlCacheKey = null;
		
        /**
         * Whether we can compress the output using Gzip
         * @var boolean
         */
        private $canCompressOutput = false;
		
        /**
         * Construct new instance
         */
        public function __construct() {
            parent::__construct();
            $globals = & class_loader('GlobalVar', 'classes');
            $currentUrl = '';
            if ($globals->server('REQUEST_URI')) {
                $currentUrl = $globals->server('REQUEST_URI');
            }
            if ($globals->server('QUERY_STRING')) {
                $currentUrl .= '?' . $globals->server('QUERY_STRING');
            }
            $this->currentUrl = $currentUrl;		
            $this->currentUrlCacheKey = md5($this->currentUrl);

            $this->setOutputCompressionStatus();
        }

        /**
         * Send the HTTP Response headers
         * @param  integer $httpCode the HTTP status code
         * @param  array   $headers   the additional headers to add to the existing headers list
         */
        public function sendHeaders($httpCode = 200, array $headers = array()) {
            set_http_status_header($httpCode);
            $this->setHeaders($headers);
            $this->setRequiredHeaders();
            //@codeCoverageIgnoreStart
            //not available when running in CLI mode
            if (!headers_sent()) {
                foreach ($this->getHeaders() as $key => $value) {
                    header($key . ': ' . $value);
                }
            }
            //@codeCoverageIgnoreEnd
        }

        /**
         * Get the list of the headers
         * @return array the headers list
         */
        public function getHeaders() {
            return $this->headers;
        }

        /**
         * Get the header value for the given name
         * @param  string $name the header name
         * @return string|null       the header value
         */
        public function getHeader($name) {
            if (array_key_exists($name, $this->headers)) {
                return $this->headers[$name];
            }
            return null;
        }


        /**
         * Set the header value for the specified name
         * @param string $name  the header name
         * @param string $value the header value to be set
         */
        public function setHeader($name, $value) {
            $this->headers[$name] = $value;
        }

        /**
         * Set the headers using array
         * @param array $headers the list of the headers to set. 
         * Note: this will merge with the existing headers
         */
        public function setHeaders(array $headers) {
            $this->headers = array_merge($this->headers, $headers);
        }
		
        /**
         * Redirect user to the specified page
         * @param  string $path the URL or URI to be redirect to
         * @codeCoverageIgnore
         */
        public function redirect($path = '') {
            $url = get_instance()->url->appUrl($path);
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
         * 
         * @param  string  $view   the view name or path
         * @param  array|object   $data   the variable data to use in the view
         * @param  boolean $return whether to return the view generated content or display it directly
         * 
         * @return void|string          if $return is true will return the view content otherwise
         * will display the view content.
         */
        public function render($view, $data = null, $return = false) {
            //try to convert data to an array if is object or other thing
            $data = (array) $data;
            $view = str_ireplace('.php', '', $view);
            $view = trim($view, '/\\');
            $viewFile = $view . '.php';
            $path = null;
			
            //check in module first
            $this->logger->debug('Checking the view [' . $view . '] from module list ...');
            $moduleInfo = $this->getModuleInfoForView($view);
            $module = $moduleInfo['module'];
            $view = $moduleInfo['view'];
            $moduleViewPath = get_instance()->module->findViewFullPath($view, $module);
            if ($moduleViewPath) {
                $path = $moduleViewPath;
                $this->logger->info('Found view [' . $view . '] in module [' . $module . '], '
                                    . 'the file path is [' . $moduleViewPath . '] we will used it');
            } else {
                $this->logger->info('Cannot find view [' . $view . '] in module [' . $module . '] '
                                    . 'using the default location');
            }
			if (!$path) {
                $path = $this->getDefaultFilePathForView($viewFile);
            }
            $this->logger->info('The view file path to be loaded is [' . $path . ']');
			
            if ($return) {
                return $this->loadView($path, $data, true);
            }
            $this->loadView($path, $data, false);
        }

        /**
         * Send the final page output
         */
        public function renderFinalPage() {
            $content = $this->finalPageContent;
            if (!$content) {
                $this->logger->warning('The final view content is empty.');
                return;
            }
            $obj = & get_instance();
            $cachePageStatus = get_instance()->config->get('cache_enable', false) 
                               && !empty($obj->view_cache_enable);
            
            $content = $this->dispatchFinalViewEvent();
            
            //check whether need save the page into cache.
            if ($cachePageStatus) {
                $this->savePageContentIntoCache($content);
            }
            //update final page content
            $this->finalPageContent = $content;
            $content = $this->replaceElapseTimeAndMemoryUsage($content);

            //compress the output if is available
            $compressOutputHandler = $this->getCompressOutputHandler();
            ob_start($compressOutputHandler);
            $this->sendHeaders(200);
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
            //the current page cache key for identification
            $pageCacheKey = $this->currentUrlCacheKey;
			
            $this->logger->debug('Checking if the page content for the '
                                . 'URL [' . $this->currentUrl . '] is cached ...');
            //get the cache information to prepare header to send to browser
            $cacheInfo = $cache->getInfo($pageCacheKey);
            if ($cacheInfo) {
                $status = $this->sendCacheNotYetExpireInfoToBrowser($cacheInfo);
                if($status === false) {
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
            return $this->finalPageContent;
        }

         /**
         * Set the final page to be rendered
         * @param string $finalPage the content of the final page
         * 
         * @return object
         */
        public function setFinalPageContent($finalPage) {
            $this->finalPageContent = $finalPage;
            return $this;
        }

        /**
         * Send the HTTP 404 error if can not found the 
         * routing information for the current request
         */
        public function send404() {
            $content = $this->finalPageContent;
            if (!$content) {
                $this->logger->warning('The final view content is empty.');
                return;
            }
            $obj = & get_instance();
            $cachePageStatus = get_instance()->config->get('cache_enable', false) 
                                && !empty($obj->view_cache_enable);
            //dispatch
            get_instance()->eventdispatcher->dispatch(new EventInfo('PAGE_NOT_FOUND'));
            //check whether need save the page into cache.
            if ($cachePageStatus) {
                $this->savePageContentIntoCache($content);
            }
            $content = $this->replaceElapseTimeAndMemoryUsage($content);

            /**************************************** save the content into logs **************/
            $userAgent = & class_loader('Browser');
            $browser = $userAgent->getPlatform() . ', ' . $userAgent->getBrowser() . ' ' . $userAgent->getVersion();
            $obj->loader->functions('user_agent');
            $str = '[404 page not found] : ';
            $str .= ' Unable to find the request page [' . $obj->request->requestUri() . '].'
                    .' The visitor IP address [' . get_ip() . '], browser [' . $browser . ']';
            $this->logger->error($str);
            /**********************************************************************/
            
            //compress the output if is available
            $compressOutputHandler = $this->getCompressOutputHandler();
            ob_start($compressOutputHandler);
            $this->sendHeaders(404);
            echo $content;
            ob_end_flush();
        }

        /**
         * Display the error to user
         *
         * @param  array  $data the error information
         */
        public function sendError(array $data = array()) {
            $path = CORE_VIEWS_PATH . 'errors.php';
            if(file_exists($path)){
                //compress the output if is available
                $compressOutputHandler = $this->getCompressOutputHandler();
                ob_start($compressOutputHandler);
                extract($data);
                require $path;
                $content = ob_get_clean();
                $this->finalPageContent = $content;
                $this->sendHeaders(503);
                echo $content;
            }
            //@codeCoverageIgnoreStart
            else{
                //can't use show_error() at this time because 
                //some dependencies not yet loaded
                set_http_status_header(503);
                echo 'The error view [' . $path . '] does not exist';
            }
            //@codeCoverageIgnoreEnd
        }

                /**
         * Dispatch the FINAL_VIEW_READY event
         *             
         * @return string|null the final view content after processing by each listener
         * if they exists otherwise the same content will be returned
         */
        protected function dispatchFinalViewEvent() {
            //dispatch
            $event = get_instance()->eventdispatcher->dispatch(
                                                                new EventInfo(
                                                                                'FINAL_VIEW_READY', 
                                                                                $this->finalPageContent, 
                                                                                true
                                                                            )
                                                            );
            $content = null;
            if (!empty($event->payload)) {
                $content = $event->payload;
            }
            if (empty($content)) {
                $this->logger->warning('The view content is empty after dispatch to event listeners.');
            }
            return $content;
        }

        /**
         * Get the compress output handler is can compress the page content
         * before send
         * @return null|string the name of function to handler compression
         */
        protected function getCompressOutputHandler() {
            $handler = null;
            if ($this->canCompressOutput) {
                $handler = 'ob_gzhandler';
            }
            return $handler;
        }

        /**
         * Set the status of output compression
         */
        protected function setOutputCompressionStatus() {
            $globals = & class_loader('GlobalVar', 'classes');
            $this->canCompressOutput = get_config('compress_output')
                                          && $globals->server('HTTP_ACCEPT_ENCODING') !== null 
                                          && stripos($globals->server('HTTP_ACCEPT_ENCODING'), 'gzip') !== false 
                                          && extension_loaded('zlib')
                                          && (bool) ini_get('zlib.output_compression') === false;
        }

         /**
         * Return the default full file path for view
         * @param  string $file    the filename
         * 
         * @return string|null          the full file path
         */
        protected function getDefaultFilePathForView($file){
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
                $lastModified = $cacheInfo['mtime'];
                $expire = $cacheInfo['expire'];
                $globals = & class_loader('GlobalVar', 'classes');
                $maxAge = $expire - (double) $globals->server('REQUEST_TIME');
                $this->setHeader('Pragma', 'public');
                $this->setHeader('Cache-Control', 'max-age=' . $maxAge . ', public');
                $this->setHeader('Expires', gmdate('D, d M Y H:i:s', $expire) . ' GMT');
                $this->setHeader('Last-modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
                $headerModifiedSince = $globals->server('HTTP_IF_MODIFIED_SINCE');
                if (!empty($headerModifiedSince) && $lastModified <= strtotime($headerModifiedSince)) {
                    $this->logger->info('The cache page content is not yet expire for the '
                                         . 'URL [' . $this->currentUrl . '] send 304 header to browser');
                    $this->sendHeaders(304);
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
            $this->logger->info('The cache page content is expired or the browser does '
                 . 'not send the HTTP_IF_MODIFIED_SINCE header for the URL [' . $this->currentUrl . '] '
                 . 'send cache headers to tell the browser');
            $this->sendHeaders(200);
            //current page cache key
            $pageCacheKey = $this->currentUrlCacheKey;
            //get the cache content
            $content = $cache->get($pageCacheKey);
            if ($content) {
                $this->logger->info('The page content for the URL [' . $this->currentUrl . '] already cached just display it');
                $content = $this->replaceElapseTimeAndMemoryUsage($content);
                ///display the final output
                //compress the output if is available
                $compressOutputHandler = $this->getCompressOutputHandler();
                ob_start($compressOutputHandler);
                echo $content;
                ob_end_flush();
                return true;
            }
            $this->logger->info('The page cache content for the URL [' . $this->currentUrl . '] is not valid may be already expired');
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
            //current page URL
            $url = $this->currentUrl;
            //Cache view Time to live in second
            $viewCacheTtl = get_instance()->config->get('cache_ttl');
            if (!empty($obj->view_cache_ttl)) {
                $viewCacheTtl = $obj->view_cache_ttl;
            }
            //the cache handler instance
            $cacheInstance = $obj->cache;
            //the current page cache key for identification
            $cacheKey = $this->currentUrlCacheKey;
            $this->logger->debug('Save the page content for URL [' . $url . '] into the cache ...');
            $cacheInstance->set($cacheKey, $content, $viewCacheTtl);
			
            //get the cache information to prepare header to send to browser
            $cacheInfo = $cacheInstance->getInfo($cacheKey);
            if ($cacheInfo) {
                $lastModified = $cacheInfo['mtime'];
                $expire = $cacheInfo['expire'];
                $maxAge = $expire - time();
                $this->setHeader('Pragma', 'public');
                $this->setHeader('Cache-Control', 'max-age=' . $maxAge . ', public');
                $this->setHeader('Expires', gmdate('D, d M Y H:i:s', $expire) . ' GMT');
                $this->setHeader('Last-modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');	
            }
        }

        /**
         * Set the value of '{elapsed_time}' and '{memory_usage}'
         * @param  string $content the page content
         * @return string          the page content after replace 
         * '{elapsed_time}', '{memory_usage}'
         */
        protected function replaceElapseTimeAndMemoryUsage($content) {
            // Parse out the elapsed time and memory usage,
            // then swap the pseudo-variables with the data
            $elapsedTime = get_instance()->benchmark->elapsedTime('APP_EXECUTION_START', 'APP_EXECUTION_END');
            $memoryUsage = round(get_instance()->benchmark->memoryUsage(
                                                                        'APP_EXECUTION_START', 
                                                                        'APP_EXECUTION_END') / 1024 / 1024, 6) . 'MB';
            return str_replace(array('{elapsed_time}', '{memory_usage}'), array($elapsedTime, $memoryUsage), $content); 
        }

        /**
         * Get the module information for the view to load
         * 
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
                        if (!property_exists($this, $key)) {
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
                $this->finalPageContent .= $content;
                $found = true;
            }
            if (!$found) {
                show_error('Unable to find view [' . $path . ']');
            }
        }

         /**
         * Set the mandory headers, like security, etc.
         */
        protected function setRequiredHeaders() {
            $requiredHeaders = array(
                                'X-XSS-Protection' => '1; mode=block',
                                'X-Frame-Options'  => 'SAMEORIGIN'
                            );
            foreach ($requiredHeaders as $key => $value) {
               if (!isset($this->headers[$key])) {
                    $this->headers[$key] = $value;
               } 
            }
        }
    }

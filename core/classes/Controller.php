<?php
    defined('ROOT_PATH') || exit('Access denied');
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

    class Controller extends BaseClass {
		
        /**
         * The name of the module if this controller belong to an module
         * @var string
         */
        public $moduleName = null;

        /**
         * The singleton of the super object
         * @var Controller
         */
        private static $instance;


        /**
         * Class constructor
         */
        public function __construct() {
            parent::__construct();
			
            //instance of the super object
            self::$instance = & $this;

            //Load the resources loaded during the application bootstrap
            $this->logger->debug('Adding the loaded classes to the super instance');
            foreach (class_loaded() as $var => $class) {
                $this->$var = & class_loader($class);
            }
			
            //set module using the router
            $this->setModuleNameFromRouter();

            //load the required resources
            $this->loadRequiredResources();

            //set application supported languages
            $this->setAppSupportedLanguages();
			
            //set the cache instance using the configuration
            $this->setCacheFromParamOrConfig(null);
			
            //set application session configuration
            $this->logger->debug('Setting PHP application session handler');
            set_session_config();

            //dispatch the loaded instance of super controller event
            $this->eventdispatcher->dispatch('SUPER_CONTROLLER_CREATED');
        }


        /**
         * This is a very useful method it's used to get the super object instance
         * @return Controller the super object instance
         */
        public static function &get_instance(){
            return self::$instance;
        }

        /**
         * This method is used to set the module name
         */
        protected function setModuleNameFromRouter() {
            //set the module using the router instance
            if (isset($this->router) && $this->router->getModule()) {
                $this->moduleName = $this->router->getModule();
            }
        }

        /**
         * Set the cache using the argument otherwise will use the configuration
         * @param CacheInterface $cache the implementation of CacheInterface if null will use the configured
         */
        protected function setCacheFromParamOrConfig(CacheInterface $cache = null) {
            $this->logger->debug('Setting the cache handler instance');
            //set cache handler instance
            if (get_config('cache_enable', false)) {
                if ($cache !== null) {
                    $this->cache = $cache;
                } else if (isset($this->{strtolower(get_config('cache_handler'))})) {
                    $this->cache = $this->{strtolower(get_config('cache_handler'))};
                    unset($this->{strtolower(get_config('cache_handler'))});
                } 
            }
        }


        /**
         * This method is used to load the required resources for framework to work
         * @return void 
         */
        private function loadRequiredResources() {
            $this->logger->debug('Loading the required classes into super instance');
            $this->eventdispatcher = & class_loader('EventDispatcher', 'classes');
            $this->loader = & class_loader('Loader', 'classes');
            $this->lang = & class_loader('Lang', 'classes');
            $this->request = & class_loader('Request', 'classes');
            //dispatch the request instance created event
            $this->eventdispatcher->dispatch('REQUEST_CREATED');
            $this->response = & class_loader('Response', 'classes', 'classes');
        }

        /**
         * Set the application supported languages
         */
        private function setAppSupportedLanguages() {
            //add the supported languages ('key', 'display name')
            $languages = get_config('languages', array());
            foreach ($languages as $key => $displayName) {
                $this->lang->addLang($key, $displayName);
            }
            unset($languages);
        }

    }

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

            //set the cache instance using the configuration
            $this->setCacheIfEnabled();
			
            //set module using the router
            $this->setModuleNameFromRouter();

            //load the required resources
            $this->loadRequiredResources();

            //set application supported languages
            $this->setAppSupportedLanguages();
			
            //set application session configuration and then started it
            $this->logger->debug('Starting application session handler');
            $this->startAppSession();

            //dispatch the loaded instance of super controller event
            $this->eventdispatcher->dispatch('SUPER_CONTROLLER_CREATED');
        }


        /**
         * This is a very useful method it's used to get the super object instance
         * @return object the super object instance
         */
        public static function &getInstance(){
            return self::$instance;
        }

        /**
         * This method is used to set the session configuration
         * using the configured value and start the session if not yet started
         *
         * @codeCoverageIgnore
         */
         private function startAppSession() {
            //$_SESSION is not available on cli mode 
            if (!IS_CLI) {
                //set session params
                $sessionName = $this->config->get('session_name');
                $this->logger->info('Session name: ' . $sessionName);
                if ($sessionName) {
                    session_name($sessionName);
                }

                //Set app session handler configuration
                $this->setAppSessionConfig();

                $lifetime = $this->config->get('session_cookie_lifetime', 0);
                $path = $this->config->get('session_cookie_path', '/');
                $domain = $this->config->get('session_cookie_domain', '');
                $secure = $this->config->get('session_cookie_secure', false);
                if (is_https()) {
                    $secure = true;
                }
                session_set_cookie_params(
                    $lifetime,
                    $path,
                    $domain,
                    $secure,
                    $httponly = true /*for security for access to cookie via javascript or XSS attack*/
                );
                //to prevent attack of Session Fixation 
                //thank to https://www.phparch.com/2018/01/php-sessions-in-depth/
                ini_set('session.use_strict_mode ', 1);
                ini_set('session.use_only_cookies', 1);
                ini_set('session.use_trans_sid ', 0);
                
                $this->logger->info('Session cookie lifetime: ' . $lifetime);
                $this->logger->info('Session cookie path: ' . $path);
                $this->logger->info('Session cookie domain: ' . $domain);
                $this->logger->info('Session is secure: ' . ($secure ? 'TRUE' : 'FALSE'));
                
                if ((session_status() !== PHP_SESSION_ACTIVE) || !session_id()) {
                    $this->logger->info('Session not yet started, start it now');
                    session_start();
                }
            }
        }

        /**
         * Set the session handler configuration
         * @codeCoverageIgnore
         */
        private function setAppSessionConfig() {
             //the default is to store in the files
            $sessionHandler = $this->config->get('session_handler', 'files');
            $this->logger->info('Session handler: ' . $sessionHandler);
            if ($sessionHandler == 'files') {
                $sessionSavePath = $this->config->get('session_save_path');
                if ($sessionSavePath) {
                    if (!is_dir($sessionSavePath)) {
                        mkdir($sessionSavePath, 1773);
                    }
                    $this->logger->info('Session save path: ' . $sessionSavePath);
                    session_save_path($sessionSavePath);
                }
            } else if ($sessionHandler == 'database') {
                //load database session handle library
                //Database Session handler Model
                require_once CORE_CLASSES_MODEL_PATH . 'DBSessionHandlerModel.php';
                $dbSessionHandler = & class_loader('DBSessionHandler', 'classes');
                session_set_save_handler($dbSessionHandler, true);
                $this->logger->info('Session database handler model: ' . $this->config->get('session_save_path'));
            } else {
                show_error('Invalid session handler configuration');
            }
        }

        /**
         * This method is used to set the module name
         */
        private function setModuleNameFromRouter() {
            //set the module using the router instance
            if (isset($this->router) && $this->router->getModule()) {
                $this->moduleName = $this->router->getModule();
            }
        }

        /**
         * Set the cache instance if is enabled in the configuration
         */
        private function setCacheIfEnabled() {
            $this->logger->debug('Setting the cache handler instance');
            //set cache handler instance
            if ($this->config->get('cache_enable', false)) {
                $cache = strtolower($this->config->get('cache_handler'));
                if (property_exists($this, $cache)) {
                    $this->cache = $this->{$cache};
                    unset($this->{$cache});
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
            $this->session = & class_loader('Session', 'classes');
            $this->response = & class_loader('Response', 'classes', 'classes');
        }

        /**
         * Set the application supported languages
         */
        private function setAppSupportedLanguages() {
            //add the supported languages ('key', 'display name')
            $languages = $this->config->get('languages', array());
            foreach ($languages as $key => $displayName) {
                $this->lang->addLang($key, $displayName);
            }
            unset($languages);
        }

    }

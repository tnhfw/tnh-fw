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

    class Config extends BaseClass {
		
        /**
         * The list of loaded configuration
         * @var array
         */
        private $config = array();

        /**
         * Initialize the configuration by loading all the configuration from config file
         *
         * @param boolean $init whether to load the configuration
         */
        public function __construct($init = true) {
            parent::__construct();
            if ($init) {
                $this->init();
                //@codeCoverageIgnoreStart
                 if (ENVIRONMENT == 'production' && in_array(strtolower($this->config['log_level']), array('debug', 'info', 'all'))) {
                    $this->logger->warning('You are in production environment, please set '
                                           . 'log level to WARNING, ERROR, FATAL to increase the application performance');
                }
                //@codeCoverageIgnoreEnd
            }
        }

        /**
         * Get the configuration item value
         * @param  string $item    the configuration item name to get
         * @param  mixed $default the default value to use if can not find the config item in the list
         * @return mixed          the config value if exist or the default value
         */
        public function get($item, $default = null) {
            if (array_key_exists($item, $this->config)) {
                return $this->config[$item];
            }
            $this->logger->warning('Cannot find config item [' . $item . '] using the default value [' . stringfy_vars($default) . ']');
            return $default;
        }

        /**
         * Set the configuration item value
         * @param string $item  the config item name to set
         * @param mixed $value the config item value
         */
        public function set($item, $value) {
            $this->config[$item] = $value;
        }

        /**
         * Get all the configuration values
         * @return array the config values
         */
        public function getAll() {
            return $this->config;
        }

        /**
         * Set the configuration values by merged with the existing configuration
         * @param array $config the config values to add in the configuration list
         */
        public function setAll(array $config = array()) {
            $this->config = array_merge($this->config, $config);
        }

        /**
         * Delete the configuration item in the list
         * @param  string $item the config item name to be deleted
         * @return boolean true if the item exists and is deleted successfully otherwise will return false.
         */
        public function delete($item) {
            if (array_key_exists($item, $this->config)) {
                $this->logger->info('Delete config item [' . $item . ']');
                unset($this->config[$item]);
                return true;
            } 
            $this->logger->warning('Config item [' . $item . '] to be deleted does not exist');
            return false;
            
        }

        /**
         * Delete all the configuration values
         */
        public function deleteAll() {
            $this->config = array();
        }

        /**
         * Load the configuration file. This an alias to Loader::config()
         * @param  string $config the config name to be loaded
         * @codeCoverageIgnore will test in Loader::config
         */
        public function load($config) {
            get_instance()->loader->config($config);
        }

        /**
         * Load the configuration using config file and check if the config "base_url" is not set
         * try to set it using serve variable
         */
        protected function init() {
            $this->logger->debug('Initialization of the configuration');
            $this->config = & load_configurations();
            $this->setBaseUrlUsingServerVar();
            $this->logger->info('Configuration initialized successfully');
            $this->logger->info('The application configuration are listed below: ' . stringfy_vars($this->config));
        }


        /**
         * Set the configuration for "base_url" if is not set in the configuration
         * @codeCoverageIgnore
         */
        private function setBaseUrlUsingServerVar() {
            if (empty($this->config['base_url'])) {
                if (ENVIRONMENT == 'production') {
                    $this->logger->warning('Application base URL is not set or invalid, please'
                                           . ' set application base URL to increase the application loading time');
                }
                $baseUrl = null;
                $protocol = 'http';
                if (is_https()) {
                    $protocol = 'https';
                }
                $protocol .= '://';
                $globals = & class_loader('GlobalVar', 'classes');
                $serverAddr = $globals->server('SERVER_ADDR');
                if ($serverAddr) {
                    $baseUrl = $serverAddr;
                    //check if the server is running under IPv6
                    if (strpos($serverAddr, ':') !== FALSE) {
                        $baseUrl = '[' . $serverAddr . ']';
                    }
                    $port = $this->getServerPort();
                    $baseUrl = $protocol . $baseUrl . $port . substr(
                                                                        $globals->server('SCRIPT_NAME'), 
                                                                        0, 
                                                                        strpos(
                                                                                $globals->server('SCRIPT_NAME'), 
                                                                                basename($globals->server('SCRIPT_FILENAME')
                                                                            ))
                                                                    );
                } else {
                    $this->logger->warning('Can not determine the application '
                                           . 'base URL automatically, use http://localhost as default');
                    $baseUrl = 'http://localhost/';
                }
                $this->config['base_url'] = $baseUrl;
            }
            $this->config['base_url'] = rtrim($this->config['base_url'], '/') . '/';
        }
         
        /**
        * Return the server port using variable
        *
        * @codeCoverageIgnore
        * @return string
        */
        protected function getServerPort() {
            $globals = & class_loader('GlobalVar', 'classes');
            $serverPort = $globals->server('SERVER_PORT');
            $port = '';
            if (!in_array($serverPort, array(80, 443))) {
                $port = ':' . $serverPort;
            }
            return $port;
        }
    }

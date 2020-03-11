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

    class Config extends BaseStaticClass {
		
        /**
         * The list of loaded configuration
         * @var array
         */
        private static $config = array();

        /**
         * Initialize the configuration by loading all the configuration from config file
         * @codeCoverageIgnore
         */
        public static function init() {
            $logger = self::getLogger();
            $logger->debug('Initialization of the configuration');
            self::$config = & load_configurations();
            self::setBaseUrlUsingServerVar();
            if (ENVIRONMENT == 'production' && in_array(strtolower(self::$config['log_level']), array('debug', 'info', 'all'))) {
                $logger->warning('You are in production environment, please set log level to WARNING, ERROR, FATAL to increase the application performance');
            }
            $logger->info('Configuration initialized successfully');
            $logger->info('The application configuration are listed below: ' . stringfy_vars(self::$config));
        }

        /**
         * Get the configuration item value
         * @param  string $item    the configuration item name to get
         * @param  mixed $default the default value to use if can not find the config item in the list
         * @return mixed          the config value if exist or the default value
         */
        public static function get($item, $default = null) {
            $logger = self::getLogger();
            if (array_key_exists($item, self::$config)) {
                return self::$config[$item];
            }
            $logger->warning('Cannot find config item [' . $item . '] using the default value [' . $default . ']');
            return $default;
        }

        /**
         * Set the configuration item value
         * @param string $item  the config item name to set
         * @param mixed $value the config item value
         */
        public static function set($item, $value) {
            self::$config[$item] = $value;
        }

        /**
         * Get all the configuration values
         * @return array the config values
         */
        public static function getAll() {
            return self::$config;
        }

        /**
         * Set the configuration values bu merged with the existing configuration
         * @param array $config the config values to add in the configuration list
         */
        public static function setAll(array $config = array()) {
            self::$config = array_merge(self::$config, $config);
        }

        /**
         * Delete the configuration item in the list
         * @param  string $item the config item name to be deleted
         * @return boolean true if the item exists and is deleted successfully otherwise will return false.
         */
        public static function delete($item) {
            $logger = self::getLogger();
            if (array_key_exists($item, self::$config)) {
                $logger->info('Delete config item [' . $item . ']');
                unset(self::$config[$item]);
                return true;
            } 
            $logger->warning('Config item [' . $item . '] to be deleted does not exists');
            return false;
            
        }

        /**
         * Delete all the configuration values
         */
        public static function deleteAll() {
            self::$config = array();
        }

        /**
         * Load the configuration file. This an alias to Loader::config()
         * @param  string $config the config name to be loaded
         * @codeCoverageIgnore will test in Loader::config
         */
        public static function load($config) {
            Loader::config($config);
        }

        /**
         * Set the configuration for "base_url" if is not set in the configuration
         * @codeCoverageIgnore
         */
        private static function setBaseUrlUsingServerVar() {
            $logger = self::getLogger();
            if (empty(self::$config['base_url'])) {
                if (ENVIRONMENT == 'production') {
                    $logger->warning('Application base URL is not set or invalid, please set application base URL to increase the application loading time');
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
                    $port = self::getServerPort();
                    $baseUrl = $protocol . $baseUrl . $port . substr(
                                                                        $globals->server('SCRIPT_NAME'), 
                                                                        0, 
                                                                        strpos($globals->server('SCRIPT_NAME'), basename($globals->server('SCRIPT_FILENAME')))
                                                                    );
                } else {
                    $logger->warning('Can not determine the application base URL automatically, use http://localhost as default');
                    $baseUrl = 'http://localhost/';
                }
                self::$config['base_url'] = $baseUrl;
            }
            self::$config['base_url'] = rtrim(self::$config['base_url'], '/') . '/';
        }
         
        /**
        * Return the server port using variable
        *
        * @codeCoverageIgnore
        * @return string
        */
        protected static function getServerPort() {
            $globals = & class_loader('GlobalVar', 'classes');
            $serverPortValue = $globals->server('SERVER_PORT');
            $serverPort = 80;
            if ($serverPortValue) {
                 $serverPort = $serverPortValue;
            }
            $port = '';
            if ((is_https() && $serverPort != 443) || (!is_https() && $serverPort != 80)) {
                $port = ':' . $serverPort;
            }
            return $port;
        }
    }

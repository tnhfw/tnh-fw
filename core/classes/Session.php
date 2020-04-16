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
    
    class Session extends BaseClass {
		
        /**
         * The session flash key to use
         * @const
         */
        const SESSION_FLASH_KEY = 'session_flash';


        /**
         * Construct new instance
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * Get the session item value
         * @param  string $item    the session item name to get
         * @param  mixed $default the default value to use if can not find the session item in the list
         * @return mixed          the session value if exist or the default value
         */
        public function get($item, $default = null) {
            $sessions = get_instance()->globalvar->session();
            $this->logger->debug('Getting session data for item [' . $item . '] ...');
            if (array_key_exists($item, $sessions)) {
                $this->logger->info('Found session data for item [' . $item . '] the vaue is : [' . stringfy_vars($sessions[$item]) . ']');
                return $sessions[$item];
            }
            $this->logger->warning('Cannot find session item [' . $item . '] using the default value [' . stringfy_vars($default) . ']');
            return $default;
        }

        /**
         * Set the session item value
         * @param string $item  the session item name to set
         * @param mixed $value the session item value
         */
        public function set($item, $value) {
            $this->logger->debug('Setting session data for item [' . $item . '], value [' . stringfy_vars($value) . ']');
            get_instance()->globalvar->setSession($item, $value);
        }

        /**
         * Get the session flash item value
         * @param  string $item    the session flash item name to get
         * @param  mixed $default the default value to use if can not find the session flash item in the list
         * @return mixed          the session flash value if exist or the default value
         */
        public function getFlash($item, $default = null) {
            $key = self::SESSION_FLASH_KEY . '_' . $item;
            $return = $default;
            $sessions = get_instance()->globalvar->session();
            if (array_key_exists($key, $sessions)) {
                $return = $sessions[$key];
                get_instance()->globalvar->removeSession($key);
            } else {
                $this->logger->warning('Cannot find session flash item [' . $key . '] using the default value [' . stringfy_vars($default) . ']');
            }
            return $return;
        }

        /**
         * Check whether the given session flash item exists
         * @param  string  $item the session flash item name
         * @return boolean 
         */
        public function hasFlash($item) {
            $key = self::SESSION_FLASH_KEY . '_' . $item;
            return array_key_exists($key, get_instance()->globalvar->session());
        }

        /**
         * Set the session flash item value
         * @param string $item  the session flash item name to set
         * @param mixed $value the session flash item value
         */
        public function setFlash($item, $value) {
            $key = self::SESSION_FLASH_KEY . '_' . $item;
            get_instance()->globalvar->setSession($key, $value);
        }

        /**
         * Clear the session item in the list
         * @param  string $item the session item name to be deleted
         *
         * @return boolean
         */
        public function clear($item) {
            if (array_key_exists($item, get_instance()->globalvar->session())) {
                $this->logger->info('Deleting of session for item [' . $item . ' ]');
                get_instance()->globalvar->removeSession($item);
                return true;
            } 
            $this->logger->warning('Session item [' . $item . '] to be deleted does not exist');
            return false;
        }
		
        /**
         * Clear the session flash item in the list
         * @param  string $item the session flash item name to be deleted
         *
         * @return boolean
         */
        public function clearFlash($item) {
            $key = self::SESSION_FLASH_KEY . '_' . $item;
            return $this->clear($key);
        }

        /**
         * Check whether the given session item exists
         * @param  string  $item the session item name
         * @return boolean 
         */
        public function exists($item) {
            return array_key_exists($item, get_instance()->globalvar->session());
        }

        /**
         * Destroy all session data values
         */
        public function clearAll() {
            session_unset();
            session_destroy();
        }

    }

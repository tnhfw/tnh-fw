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

    class Cookie extends BaseClass {

        /**
         * Construct new instance
         */
        public function __construct() {
            parent::__construct();
        }
        
        /**
         * Get the cookie item value
         * @param  string $item    the cookie item name to get
         * @param  mixed $default the default value to use if can not find the cokkie item in the list
         * @return mixed          the cookie value if exist or the default value
         */
        public function get($item, $default = null) {
            if (array_key_exists($item, get_instance()->globalvar->cookie())) {
                return get_instance()->globalvar->cookie($item);
            }
            $this->logger->warning('Cannot find cookie item [' . $item . '], using the default value [' . $default . ']');
            return $default;
        }

        /**
         * Set the cookie item value
         * @param string  $name     the cookie item name
         * @param string  $value    the cookie value to set
         * @param integer $expire   the time to live for this cookie
         * @param string  $path     the path that the cookie will be available
         * @param string  $domain   the domain that the cookie will be available
         * @param boolean $secure   if this cookie will be available on secure connection or not
         * @param boolean $httponly if this cookie will be available under HTTP protocol.
         */
        public function set($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
            if (headers_sent()) {
                show_error('There exists a cookie that we wanted to create that we couldn\'t '
						    . 'because headers was already sent. Make sure to do this first ' 
							. 'before outputing anything.');
            }
            $timestamp = $expire;
            if ($expire) {
                $timestamp = time() + $expire;
            }
            setcookie($name, $value, $timestamp, $path, $domain, $secure, $httponly);
        }

        /**
         * Delete the cookie item in the list
         * @param  string $item the cookie item name to be cleared
         * @return boolean true if the item exists and is deleted successfully otherwise will return false.
         */
        public function delete($item) {
            if (array_key_exists($item, get_instance()->globalvar->cookie())) {
                $this->logger->info('Delete cookie item [' . $item . ']');
                get_instance()->globalvar->removeCookie($item);
                return true;
            } else {
                $this->logger->warning('Cookie item [' . $item . '] to be deleted does not exists');
                return false;
            }
        }

        /**
         * Check if the given cookie item exists
         * @param  string $item the cookie item name
         * @return boolean       true if the cookie item is set, false or not
         */
        public function exists($item) {
            return array_key_exists($item, get_instance()->globalvar->cookie());
        }

    }

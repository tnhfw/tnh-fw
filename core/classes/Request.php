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

    class Request {
		
        /**
         * The request headers
         * @var array
         */
        private $header = null;

        /**
         * The current request method 'GET', 'POST', 'PUT', etc.
         * @var null
         */
        private $method = null;

        /**
         * The current request URI
         * @var string
         */
        private $requestUri = null;

        /**
         * Json body data
         * @var array
         */
        private $json = array();
		
        /**
         * Construct new request instance
         */
        public function __construct() {
            $this->method = $this->server('REQUEST_METHOD');
            $this->requestUri = $this->server('REQUEST_URI');
            $this->header = array();
            //@codeCoverageIgnoreStart
            if (function_exists('apache_request_headers')) {
                $this->header = apache_request_headers();
            } else if (function_exists('getallheaders')) {
                $this->header = getallheaders();
            }

            //@codeCoverageIgnoreEnd
            $jsonBody = file_get_contents('php://input');
            $this->json = json_decode($jsonBody, true);
        }

        /**
         * Get the request method
         * @return string
         */
        public function method() {
            return $this->method;
        }
		
        /**
         * Get the request URI
         * @return string
         */
        public function requestUri() {
            return $this->requestUri;
        }

        /**
         * Get the value from $_REQUEST for given key. if the key is empty will return all values
         * @see GlobalVar::request 
         */
        public function query($key = null, $xss = true) {
            return get_instance()->globalvar->request($key, $xss);
        }
		
        /**
         * Get the value from $_GET for given key. if the key is empty will return all values
         * @see GlobalVar::get 
         */
        public function get($key = null, $xss = true) {
            return get_instance()->globalvar->get($key, $xss);
        }
		
        /**
         * Get the value from $_POST for given key. if the key is empty will return all values
         * @see GlobalVar::post 
         */
        public function post($key = null, $xss = true) {
            return get_instance()->globalvar->post($key, $xss);
        }
		
        /**
         * Get the value from $_SERVER for given key. if the key is empty will return all values
         * @see GlobalVar::server 
         */
        public function server($key = null, $xss = false) {
            return get_instance()->globalvar->server($key, $xss);
        }
		
        /**
         * Get the value from $_COOKIE for given key. if the key is empty will return all values
         *
         *  NOTE: This super global is not filter by default
         *  
         * @see GlobalVar::cookie 
         */
        public function cookie($key = null, $xss = false) {
            return get_instance()->globalvar->cookie($key, $xss);
        }
		
        /**
         * Get the value from $_FILES for given key. if the key is empty will return all values
         * @see GlobalVar::files 
         */
        public function file($key, $xss = true) {
            return get_instance()->globalvar->files($key, $xss);
        }

        /**
         * Get the value for header for given key. if the key is empty will return the all values
         *
         *  NOTE: This is not filter by default
         *  
         * @param  string  $key the item key to be fetched
         * @param  boolean $xss if need apply some XSS rule on the value
         * @return array|mixed       the item value if the key exists or all array if the key is null
         */
        public function header($key = null, $xss = true) {
            $data = null;
            if ($key === null) {
                //return all
                $data = $this->header;
            } else if (array_key_exists($key, $this->header)) {
                $data = $this->header[$key];
            }
            if ($xss) {
                $data = clean_input($data);
            }
            return $data;
        }

        /**
         * Set the value for header.
         * @param  string|array  $key the item key to be set or array if need set the current header  
         * by this value
         * @param mixed $value the value to set if $key is not an array
         * 
         * @return object       the current instance
         */
        public function setHeader($key, $value = null) {
            if (is_array($key)) {
                //set all
                $this->header = $key;
            } else {
                $this->header[$key] = $value;
            }
            return $this;
        }

        /**
         * Get the value for JSON body for given key. if the key is empty will return all values
         *
         * @codeCoverageIgnore
         * @param  string  $key the item key to be fetched
         * @param  boolean $xss if need apply some XSS rule on the value
         * @return array|mixed       the item value if the key exists or all array if the key is null
         */
        public function json($key = null, $xss = true) {
            $data = null;
            if ($key === null) {
                //return all
                $data = $this->json;
            } else if (array_key_exists($key, $this->json)) {
                $data = $this->json[$key];
            }
            if ($xss) {
                $data = clean_input($data);
            }
            return $data;
        }

    }

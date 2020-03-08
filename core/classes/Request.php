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
         * The value for the super global $_GET
         * @var array
         */
        private $get = null;

        /**
         * The value for the super global $_POST
         * @var array
         */
        private $post = null;

        /**
         * The value for the super global $_SERVER
         * @var array
         */
        private $server = null;

        /**
         * The value for the super global $_COOKIE
         * @var array
         */
        private $cookie = null;

        /**
         * The value for the super global $_FILES
         * @var array
         */
        private $file = null;

        /**
         * The value for the super global $_REQUEST
         * @var array
         */
        private $query = null;
		
        /**
         * The session instance
         * @var Session
         */
        private $session = null;
		
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
         * Construct new request instance
         */
        public function __construct() {
            $this->get = $_GET;
            $this->post = $_POST;
            $this->server = $_SERVER;
            $this->query = $_REQUEST;
            $this->cookie = $_COOKIE;
            $this->file = $_FILES;
            $this->session = & class_loader('Session', 'classes');
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
         * Get the value from $_REQUEST for given key. if the key is empty will return the all values
         * @see Request::getVars 
         */
        public function query($key = null, $xss = true) {
            return $this->getVars('query', $key, $xss);
        }
		
        /**
         * Get the value from $_GET for given key. if the key is empty will return the all values
         * @see Request::getVars 
         */
        public function get($key = null, $xss = true) {
            return $this->getVars('get', $key, $xss);
        }
		
        /**
         * Get the value from $_POST for given key. if the key is empty will return the all values
         * @see Request::getVars 
         */
        public function post($key = null, $xss = true) {
            return $this->getVars('post', $key, $xss);
        }
		
        /**
         * Get the value from $_SERVER for given key. if the key is empty will return the all values
         * @see Request::getVars 
         */
        public function server($key = null, $xss = true) {
            return $this->getVars('server', $key, $xss);
        }
		
        /**
         * Get the value from $_COOKIE for given key. if the key is empty will return the all values
         * @see Request::getVars 
         */
        public function cookie($key = null, $xss = true) {
            return $this->getVars('cookie', $key, $xss);
        }

        /**
         * Get the value from header array for given key.
         * @see Request::getVars 
         */
        public function header($key = null, $xss = true) {
            return $this->getVars('header', $key, $xss);
        }
		
        /**
         * Get the value from $_FILES for given key. if the key is empty will return the all values
         * @param  string  $key the item key to be fetched
         * @return array|mixed       the item value if the key exists or all array if the key does not exists or is empty
         */
        public function file($key) {
            $file = array_key_exists($key, $this->file) ? $this->file[$key] : null;
            return $file;
        }
		
        /**
         * Get the value from $_SESSION for given key. if the key is empty will return the all values
         * @param  string  $key the item key to be fetched
         * @param  boolean $xss if need apply some XSS attack rule on the value
         * @return array|mixed       the item value if the key exists or null if the key does not exists
         */
        public function session($key, $xss = true) {
            $session = $this->session->get($key);
            if ($xss) {
                $session = clean_input($session);
            }
            return $session;
        }

        /**
         * Set the value for $_REQUEST for the given key.
         * @see Request::setVars 
         */
        public function setQuery($key, $value = null) {
            return $this->setVars('query', $key, $value);
        }

        /**
         * Set the value for $_GET for the given key.
         * @see Request::setVars 
         */
        public function setGet($key, $value = null) {
            return $this->setVars('get', $key, $value);
        }

        /**
         * Set the value for $_POST for the given key.
         * @see Request::setVars 
         */
        public function setPost($key, $value = null) {
            return $this->setVars('post', $key, $value);
        }

        /**
         * Set the value for $_SERVER for the given key.
         * @see Request::setVars 
         */
        public function setServer($key, $value = null) {
            return $this->setVars('server', $key, $value);
        }

        /**
         * Set the value for $_COOKIE for the given key.
         * @see Request::setVars 
         */
        public function setCookie($key, $value = null) {
            return $this->setVars('cookie', $key, $value);
        }

        /**
         * Set the value for header for the given key.
         * @see Request::setVars 
         */
        public function setHeader($key, $value = null) {
            return $this->setVars('header', $key, $value);
        }

        /**
         * Set the value for $_FILES for the given key.
         * @see Request::setVars 
         */
        public function setFile($key, $value = null) {
            return $this->setVars('file', $key, $value);
        }

        /**
         * Set the instance for session.
         * @param object|null $session the object of Session to be set
         * @return object the current instance
         */
        public function setSession(Session $session = null) {
            $this->session = $session;
            return $this;
        }

         /**
         * Return the instance of session.
         * @return object the session instance
         */
        public function getSession() {
            return $this->session;
        }

         /**
         * Set the value for $_GET, $_POST, $_SERVER etc. if the key is an array will
         * set the current super variable value by this.
         * @param string $type the type can be "post", "get", etc.
         * @param  string|array  $key the item key to be set or array if need set the current global variable 
         * by this value
         * @param mixed $value the value to set if $key is not an array
         *
         * @return object       the current instance
         */
        protected function setVars($type, $key, $value = null) {
            if (is_array($key)) {
                //set all
                $this->{$type} = $key;
            } else {
                $this->{$type}[$key] = $value;
            }
            return $this;
        }

        /**
         * Get the value from $_GET, $_POST, $_SERVER etc. for given key. if the key is empty will return the all values
         * @param string $type the type can be "post", "get", etc.
         * @param  string  $key the item key to be fetched
         * @param  boolean $xss if need apply some XSS rule on the value
         * @return array|mixed       the item value if the key exists or all array if the key is null
         */
        protected function getVars($type, $key = null, $xss = true) {
            $data = null;
            if ($key === null) {
                //return all
                $data = $this->{$type};
            } else if (array_key_exists($key, $this->{$type})) {
                $data = $this->{$type}[$key];
            }
            if ($xss) {
                $data = clean_input($data);
            }
            return $data;
        }

       
		
    }

<?php
    defined('ROOT_PATH') or exit('Access denied');
    /**
     * TNH Framework
     *
     * A simple PHP framework using HMVC architecture
     *
     * This content is released under the GNU GPL License (GPL)
     *
     * Copyright (C) 2017 Tony NGUEREZA
     *
     * This program is free software; you can redistribute it and/or
     * modify it under the terms of the GNU General Public License
     * as published by the Free Software Foundation; either version 3
     * of the License, or (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program; if not, write to the Free Software
     * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
     */

    class Request {
		
        /**
         * The value for the super global $_GET
         * @var array
         */
        public $get = null;

        /**
         * The value for the super global $_POST
         * @var array
         */
        public $post = null;

        /**
         * The value for the super global $_SERVER
         * @var array
         */
        public $server = null;

        /**
         * The value for the super global $_COOKIE
         * @var array
         */
        public $cookie = null;

        /**
         * The value for the super global $_FILES
         * @var array
         */
        public $file = null;

        /**
         * The value for the super global $_REQUEST
         * @var array
         */
        public $query = null;
		
        /**
         * The session instance
         * @var Session
         */
        public $session = null;
		
        /**
         * The request headers
         * @var array
         */
        public $header = null;

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
            if (function_exists('apache_request_headers')) {
                $this->header = apache_request_headers();
            } else if (function_exists('getallheaders')) {
                $this->header = getallheaders();
            }
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
         * Get the value from $_GET, $_POST, $_SERVER etc. for given key. if the key is empty will return the all values
         * @param string $type the type can be "post", "get", etc.
         * @param  string  $key the item key to be fetched
         * @param  boolean $xss if need apply some XSS rule on the value
         * @return array|mixed       the item value if the key exists or all array if the key is null
         */
        protected function getVars($type, $key = null, $xss = true) {
            $data = null;
            if ($key !== null) {
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

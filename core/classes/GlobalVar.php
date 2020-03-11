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

    class GlobalVar {
        
        /**
         * Get the value from $_GET for given key. if the key is empty will return all values
         * @see GlobalVar::getVars 
         */
        public function get($key = null, $xss = true) {
            return $this->getVars($_GET, $key, $xss);
        }

        /**
         * Set the value for $_GET for the given key.
         * @see GlobalVar::setVars 
         */
        public function setGet($key, $value = null) {
            return $this->setVars($_GET, $key, $value);
        }

        /**
         * Remove the value from $_GET for the given key.
         * @see GlobalVar::removeVars 
         */
        public function removeGet($key) {
            return $this->removeVars($_GET, $key);
        }

        /**
         * Get the value from $_POST for given key. if the key is empty will return all values
         * @see GlobalVar::getVars 
         */
        public function post($key = null, $xss = true) {
            return $this->getVars($_POST, $key, $xss);
        }

        /**
         * Set the value for $_POST for the given key.
         * @see GlobalVar::setVars 
         */
        public function setPost($key, $value = null) {
            return $this->setVars($_POST, $key, $value);
        }

        /**
         * Remove the value from $_POST for the given key.
         * @see GlobalVar::removeVars 
         */
        public function removePost($key) {
            return $this->removeVars($_POST, $key);
        }

        /**
         * Get the value from $_FILES for given key. if the key is empty will return all values
         * @see GlobalVar::getVars 
         */
        public function files($key = null, $xss = true) {
            return $this->getVars($_FILES, $key, $xss);
        }

        /**
         * Set the value for $_FILES for the given key.
         * @see GlobalVar::setVars 
         */
        public function setFiles($key, $value = null) {
            return $this->setVars($_FILES, $key, $value);
        }

        /**
         * Remove the value from $_FILES for the given key.
         * @see GlobalVar::removeVars 
         */
        public function removeFiles($key) {
            return $this->removeVars($_FILES, $key);
        }

        /**
         * Get the value from $_REQUEST for given key. if the key is empty will return all values
         * @see GlobalVar::getVars 
         */
        public function request($key = null, $xss = true) {
            return $this->getVars($_REQUEST, $key, $xss);
        }

        /**
         * Set the value for $_REQUEST for the given key.
         * @see GlobalVar::setVars 
         */
        public function setRequest($key, $value = null) {
            return $this->setVars($_REQUEST, $key, $value);
        }

        /**
         * Remove the value from $_REQUEST for the given key.
         * @see GlobalVar::removeVars 
         */
        public function removeRequest($key) {
            return $this->removeVars($_REQUEST, $key);
        }

        /**
         * Get the value from $_COOKIE for given key. if the key is empty will return all values
         *
         * NOTE: This super global is not filter by default
         * 
         * @see GlobalVar::getVars 
         */
        public function cookie($key = null, $xss = false) {
            return $this->getVars($_COOKIE, $key, $xss);
        }

        /**
         * Set the value for $_COOKIE for the given key.
         * @see GlobalVar::setVars 
         */
        public function setCookie($key, $value = null) {
            return $this->setVars($_COOKIE, $key, $value);
        }

        /**
         * Remove the value from $_COOKIE for the given key.
         * @see GlobalVar::removeVars 
         */
        public function removeCookie($key) {
            return $this->removeVars($_COOKIE, $key);
        }

        /**
         * Get the value from $_SESSION for given key. if the key is empty will return all values
         *
         * NOTE: This super global is not filter by default
         * 
         * @see GlobalVar::getVars 
         */
        public function session($key = null, $xss = false) {
            return $this->getVars($_SESSION, $key, $xss);
        }

        /**
         * Set the value for $_SESSION for the given key.
         * @see GlobalVar::setVars 
         */
        public function setSession($key, $value = null) {
            return $this->setVars($_SESSION, $key, $value);
        }

        /**
         * Remove the value from $_SESSION for the given key.
         * @see GlobalVar::removeVars 
         */
        public function removeSession($key) {
            return $this->removeVars($_SESSION, $key);
        }

        /**
         * Get the value from $_SERVER for given key. if the key is empty will return all values
         *
         * NOTE: This super global is not filter by default
         * 
         * @see GlobalVar::getVars 
         */
        public function server($key = null, $xss = false) {
            return $this->getVars($_SERVER, $key, $xss);
        }

        /**
         * Set the value for $_SERVER for the given key.
         * @see GlobalVar::setVars 
         */
        public function setServer($key, $value = null) {
            return $this->setVars($_SERVER, $key, $value);
        }

        /**
         * Remove the value from $_SERVER for the given key.
         * @see GlobalVar::removeVars 
         */
        public function removeServer($key) {
            return $this->removeVars($_SERVER, $key);
        }
		
        /**
         * Get the value from $_ENV for given key. if the key is empty will return all values
         *
         * NOTE: This super global is not filter by default
         * 
         * @see GlobalVar::getVars 
         */
        public function env($key = null, $xss = false) {
            return $this->getVars($_ENV, $key, $xss);
        }

        /**
         * Set the value for $_ENV for the given key.
         * @see GlobalVar::setVars 
         */
        public function setEnv($key, $value = null) {
            return $this->setVars($_ENV, $key, $value);
        }

        /**
         * Remove the value from $_ENV for the given key.
         * @see GlobalVar::removeVars 
         */
        public function removeEnv($key) {
            return $this->removeVars($_ENV, $key);
        }

         /**
         * Get the value from $GLOBALS for given key. if the key is empty will return all values
         * @see GlobalVar::getVars 
         */
        public function globals($key = null, $xss = true) {
            return $this->getVars($GLOBALS, $key, $xss);
        }

        /**
         * Set the value for $GLOBALS for the given key.
         * @see GlobalVar::setVars 
         */
        public function setGlobals($key, $value = null) {
            return $this->setVars($GLOBALS, $key, $value);
        }

        /**
         * Remove the value from $GLOBALS for the given key.
         * @see GlobalVar::removeVars 
         */
        public function removeGlobals($key) {
            return $this->removeVars($GLOBALS, $key);
        }

        
         /**
         * Set the value for $_GET, $_POST, $_SERVER etc. if the key is an array will
         * set the current super variable value by this.
         * @param array $var the super global variable to use, can be "$_POST", "$_GET", etc.
         * @param  string|array  $key the item key to be set or array if need set the current global variable 
         * by this value
         * @param mixed $value the value to set if $key is not an array
         *
         * @return object       the current instance
         */
        protected function setVars(&$var, $key, $value = null) {
            if (is_array($key)) {
                //set all
                $var = $key;
            } else {
                $var[$key] = $value;
            }
            return $this;
        }

        /**
         * Get the value from $_GET, $_POST, $_SERVER etc. for given key. if the key is empty will return all values
         * @param array $var the super global variable to use, can be "$_POST", "$_GET", etc.
         * @param  string  $key the item key to be fetched
         * @param  boolean $xss if need apply some XSS rule on the value
         * @return array|mixed       the item value if the key exists or all array if the key is null
         */
        protected function getVars(&$var, $key = null, $xss = true) {
            $data = null;
            if ($key === null) {
                //return all
                $data = $var;
            } else if (array_key_exists($key, $var)) {
                $data = $var[$key];
            }
            if ($xss) {
                $data = clean_input($data);
            }
            return $data;
        }

        /**
         * Delete the value from $_GET, $_POST, $_SERVER etc. for given key.
         * @param array $var the super global variable to use, can be "$_POST", "$_GET", etc.
         * @param  string  $key the item key to be deleted
         * 
         * @return boolean true if the key is found and removed otherwise false
         */
        protected function removeVars(&$var, $key) {
            if (array_key_exists($key, $var)) {
                unset($var[$key]);
                return true;
            }
            return false;
        }
    }

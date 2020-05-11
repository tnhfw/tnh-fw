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

    class Security extends BaseClass {

        /**
         * Construct new instance
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * This method is used to generate the CSRF token
         * @return string the generated CSRF token
         */
        public function generateCSRF() {
            $this->logger->debug('Generation of CSRF ...');
            $key = get_config('csrf_key', 'csrf_key');
            $expire = get_config('csrf_expire', 60);
            $keyExpire = 'csrf_expire';
            $currentTime = time();
            $sessionInstance = get_instance()->session;
            if (
                    $sessionInstance->exists($key) 
                    && $sessionInstance->exists($keyExpire) 
                    && $sessionInstance->get($keyExpire) > $currentTime
                ) {
                $this->logger->info('The CSRF token not yet expire just return it');
                return $sessionInstance->get($key);
            } else {
                $newTime = $currentTime + $expire;
                $token = sha1(uniqid()) . sha1(uniqid());
                $this->logger->info('The CSRF informations are listed below: '
                    . 'key [' . $key . '], key expire [' . $keyExpire . '], ' 
                    . 'expire time [' . $expire . '], token [' . $token . ']');
                $sessionInstance->set($keyExpire, $newTime);
                $sessionInstance->set($key, $token);
                return $sessionInstance->get($key);
            }
        }

        /**
         * This method is used to check the CSRF if is valid, not yet expire, etc.
         * @return boolean true if valid, false if not valid
         */
        public function validateCSRF() {
            $this->logger->debug('Validation of CSRF ...');
            $key = get_config('csrf_key', 'csrf_key');
            $expire = get_config('csrf_expire', 60);
            $keyExpire = 'csrf_expire';
            $currentTime = time();
            $sessionInstance = get_instance()->session;
            $this->logger->info('The CSRF informations are listed below: key [' . $key . '], key expire [' . $keyExpire . '], expire time [' . $expire . ']');
            if (!$sessionInstance->exists($key) || $sessionInstance->get($keyExpire) <= $currentTime) {
                $this->logger->warning('The CSRF session data is not valide');
                return false;
            }
            //perform form data
            $token = get_instance()->request->post($key);
            if ($token !== $sessionInstance->get($key) || $sessionInstance->get($keyExpire) <= $currentTime) {
                $this->logger->warning('The CSRF data [' . $token . '] is not valide may be attacker do his job');
                return false;
            }
            $this->logger->info('The CSRF data [' . $token . '] is valide the form data is safe continue');
            //remove the token from session and data
            $sessionInstance->clear($key);
            $sessionInstance->clear($keyExpire);
            get_instance()->globalvar->removePost($key);
            return true;
        }
		
        /**
        * This method is used to check the whitelist IP address access
        *
        * @return boolean
        */
        public function checkWhiteListIpAccess() {
            $this->logger->debug('Validation of the IP address access ...');
            $this->logger->debug('Check if whitelist IP access is enabled in the configuration ...');
            $isEnable = get_config('white_list_ip_enable', false);
            if (!$isEnable) {
                $this->logger->info('Whitelist IP access is not enabled in the configuration, ignore checking');
                return true;
            }
            $this->logger->info('Whitelist IP access is enabled in the configuration');
            $list = get_config('white_list_ip_addresses', array());
            if (empty($list)) {
                $this->logger->info('The list of whitelist IP is empty, ignore checking');
                return true;
            }
            //Can't use Loader::functions() at this time because teh "Loader" library is loader after the security prossessing
            require_once CORE_FUNCTIONS_PATH . 'function_user_agent.php';
            $ip = get_ip();
            if ((count($list) == 1 && $list[0] == '*') || in_array($ip, $list)) {
                $this->logger->info('IP address ' . $ip . ' is allowed using the wildcard "*" or the full IP address');
                //wildcard to access all ip address
                return true;
            }
            // go through all whitelisted ips
            foreach ($list as $ipaddr) {
                // find the wild card * in whitelisted ip (f.e. find position in "127.0.*" or "127*")
                $wildcardPosition = strpos($ipaddr, '*');
                if ($wildcardPosition === false) {
                    // no wild card in whitelisted ip --continue searching
                    continue;
                }
                // cut ip at the position where we got the wild card on the whitelisted ip
                // and add the wold card to get the same pattern
                if (substr($ip, 0, $wildcardPosition) . '*' === $ipaddr) {
                    // f.e. we got
                    //  ip "127.0.0.1"
                    //  whitelisted ip "127.0.*"
                    // then we compared "127.0.*" with "127.0.*"
                    // return success
                    $this->logger->info('IP address ' . $ip . ' is allowed using the wildcard address like "x.x.x.*"');
                    return true;
                }
            }
            $this->logger->warning('IP address ' . $ip . ' is not allowed to access to this application');
            return false;
        }
    }

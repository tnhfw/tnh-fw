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

    class Url extends BaseClass{

        /**
         * Construct new instance
         */
        public function __construct() {
            parent::__construct();
        }
        
        /**
         * Return the link using "base_url" config without front controller "index.php"
         * @param  string $path the link path or full URL
         * @return string the full link URL
         */
        public function mainUrl($path = '') {
            if (is_url($path)) {
                return $path;
            }
            return get_config('base_url') . $path;
        }

        /**
         * Return the link using "base_url" config with front controller "index.php"
         * @param  string $path the link path or full URL
         * @return string the full link URL
         */
        public function appUrl($path = '') {
            if (is_url($path)) {
                return $path;
            }
            $path = rtrim($path, '/');
            $url = get_config('base_url');
            $frontController = get_config('front_controller');
            if ($frontController) {
                $url .= $frontController . '/';
            }
            $path = $this->addSuffixInPath($path);
            return $url . $path;
        }

        /**
         * Return the current site URL
         * @return string
         */
        public function current() {
            $current = '/';
            $requestUri = get_instance()->request->requestUri();
            if ($requestUri) {
                $current = $requestUri;
            }
            return $this->domain() . $current;
        }

        /**
         * Generate a friendly  text to use in link (slugs)
         * @param  string  $str       the title or text to use to get the friendly text
         * @param  string  $separator the caracters separator
         * @param  boolean $lowercase whether to set the final text to lowe case or not
         * @return string the friendly generated text
         */
        public function title($str = null, $separator = '-', $lowercase = true) {
            $str  = trim($str);
            $from = array('ç', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'à', 'á', 'â', 'ã', 'ä', 'å', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'Ç', 'ç', 'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï', 'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü', 'ÿ', 'Ñ', 'ñ');
            $to   = array('c', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'y', 'n', 'n');
            $str  = str_replace($from, $to, $str);
            $str  = preg_replace('#([^a-z0-9]+)#i', $separator, $str);
            $str  = str_replace('--', $separator, $str);
            //if after process we get something like one-two-three-, need truncate the last separator "-"
            if (substr($str, -1) == $separator) {
                $str = substr($str, 0, -1);
            }
            if ($lowercase) {
                $str = strtolower($str);
            }
            return $str;
        }

        /**
         * Get the current application domain with protocol
         * @return string the domain name
         */
        public function domain() {
            $domain = 'localhost';
            $protocol = 'http';
            if (is_https()) {
                $protocol = 'https';
            }

            $domainserverVars = array(
                'HTTP_HOST',
                'SERVER_NAME',
                'SERVER_ADDR'
            );
            foreach ($domainserverVars as $var) {
                $value = get_instance()->request->server($var);
                if ($value) {
                    $domain = $value;
                    break;
                }
            }
	    $port = get_instance()->request->server('SERVER_PORT');
            if ($port && !in_array($port, array(80, 443))) {
                $domain .= ':' . $port;
            }
            return $protocol . '://' . $domain;
        }

        /**
         * Get the current request query string
         * @return string
         */
        public function queryString() {
            return get_instance()->request->server('QUERY_STRING');
        }

        /**
         * Add configured suffixe in the path
         * @param string $path the path
         *
         * @return string the final path after add suffix if configured
         * otherwise the same value will be returned
         */
        protected function addSuffixInPath($path){
            $suffix = get_config('url_suffix');
            if ($suffix && $path) {
                if (strpos($path, '?') !== false) {
                    $query    = explode('?', $path);
                    $query[0] = str_ireplace($suffix, '', $query[0]);
                    $query[0] = rtrim($query[0], '/');
                    $query[0] .= $suffix;
                    $path     = implode('?', $query);
                } else {
                    $path .= $suffix;
                }
            }
            return $path;
        }

    }

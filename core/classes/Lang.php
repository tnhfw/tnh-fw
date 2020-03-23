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

    /**
     * For application languages management
     */
    class Lang extends BaseClass {
		
        /**
         * The supported available language for this application.
         * @example "en" => "english" 
         * @see Lang::addLang()
         * @var array
         */
        protected $availables = array();

        /**
         * The all messages language
         * @var array
         */
        protected $languages = array();

        /**
         * The default language to use if can not
         *  determine the client language
         *  
         * @example $default = 'en'
         * @var string
         */
        protected $default = null;

        /**
         * The current client language
         * @var string
         */
        protected $current = null;

        /**
         * Construct new Lang instance
         */
        public function __construct() {
            parent::__construct();

            $this->default = get_config('default_language', 'en');
            $this->logger->debug('Setting the supported languages');

            //if the language exists in cookie use it
            $cfgKey = get_config('language_cookie_name');
            $this->logger->debug('Getting current language from cookie [' . $cfgKey . ']');
            $objCookie = & class_loader('Cookie');
            $cookieLang = $objCookie->get($cfgKey);
            if ($cookieLang && $this->isValid($cookieLang)) {
                $this->current = $cookieLang;
                $this->logger->info('Language from cookie [' . $cfgKey . '] is valid so '
                                     .'we will set the language using the cookie value [' . $cookieLang . ']');
            } else {
                $this->logger->info('Language from cookie [' . $cfgKey . '] is not set, use the default value [' . $this->getDefault() . ']');
                $this->current = $this->getDefault();
            }
        }

        /**
         * Get the all languages messages
         *
         * @return array the language message list
         */
        public function getAll() {
            return $this->languages;
        }

        /**
         * Set the language message
         *
         * @param string $key the language key to identify
         * @param string $value the language message value
         */
        public function set($key, $value) {
            $this->languages[$key] = $value;
        }

        /**
         * Get the language message for the given key. If can't find return the default value
         *
         * @param  string $key the message language key
         * @param  string $default the default value to return if can not found the language message key
         *
         * @return string the language message value
         */
        public function get($key, $default = 'LANGUAGE_ERROR') {
            if (isset($this->languages[$key])) {
                return $this->languages[$key];
            }
            $this->logger->warning('Language key  [' . $key . '] does not exist use the default value [' . $default . ']');
            return $default;
        }

        /**
         * Check whether the language file for given name exists
         *
         * @param  string  $language the language name like "fr", "en", etc.
         *
         * @return boolean true if the language directory exists, false or not
         */
        public function isValid($language) {
            $searchDir = array(CORE_LANG_PATH, APP_LANG_PATH);
            foreach ($searchDir as $dir) {
                if (file_exists($dir . $language) && is_dir($dir . $language)) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Get the default language value like "en" , "fr", etc.
         *
         * @return string the default language
         */
        public function getDefault() {
            return $this->default;
        }

        /**
         * Get the current language defined by cookie or the default value
         *
         * @return string the current language
         */
        public function getCurrent() {
            return $this->current;
        }

        /**
         * Add new supported or available language
         *
         * @param string $name the short language name like "en", "fr".
         * @param string $description the human readable description of this language
         */
        public function addLang($name, $description) {
            if (isset($this->availables[$name])) {
                return; //already added cost in performance
            }
            if ($this->isValid($name)) {
                $this->availables[$name] = $description;
            } else {
                show_error('The language [' . $name . '] is not valid or does not exist.');
            }
        }

        /**
         * Get the list of the application supported language
         *
         * @return array the list of the application language
         */
        public function getSupported() {
            return $this->availables;
        }

        /**
         * Add new language messages
         *
         * @param array $langs the languages array of the messages to be added
         */
        public function addLangMessages(array $langs) {
            foreach ($langs as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

<?php
	defined('ROOT_PATH') || exit('Access denied');
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

	/**
	 * For application languages management
	 */
	class Lang{
		
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
		 * The logger instance
		 * @var Log
		 */
		private $logger;

		/**
		 * Construct new Lang instance
		 */
		public function __construct(){
	        $this->logger =& class_loader('Log', 'classes');
	        $this->logger->setLogger('Library::Lang');

			$this->default = get_config('default_language', 'en');
			//determine the current language
			$language = null;
			//if the language exists in cookie use it
			$cfgKey = get_config('language_cookie_name');
			$this->logger->debug('Getting current language from cookie [' .$cfgKey. ']');
			$objCookie = & class_loader('Cookie');
			$cookieLang = $objCookie->get($cfgKey);
			if($cookieLang && $this->isValid($cookieLang)){
				$this->current = $cookieLang;
				$this->logger->info('Language from cookie [' .$cfgKey. '] is valid so we will set the language using the cookie value [' .$cookieLang. ']');
			}
			else{
				$this->logger->info('Language from cookie [' .$cfgKey. '] is not set, use the default value [' .$this->getDefault(). ']');
				$this->current = $this->getDefault();
			}
		}

		/**
		 * Get the all languages messages
		 *
		 * @return array the language message list
		 */
		public function getAll(){
			return $this->languages;
		}

		/**
		 * Set the language message
		 *
		 * @param string $key the language key to identify
		 * @param string $value the language message value
		 */
		public function set($key, $value){
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
		public function get($key, $default = 'LANGUAGE_ERROR'){
			if(isset($this->languages[$key])){
				return $this->languages[$key];
			}
			$this->logger->warning('Language key  [' .$key. '] does not exist use the default value [' .$default. ']');
			return $default;
		}

		/**
		 * Check whether the language file for given name exists
		 *
		 * @param  string  $language the language name like "fr", "en", etc.
		 *
		 * @return boolean true if the language directory exists, false or not
		 */
		public function isValid($language){
			$searchDir = array(CORE_LANG_PATH, APP_LANG_PATH);
			foreach($searchDir as $dir){
				if(file_exists($dir . $language) && is_dir($dir . $language)){
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
		public function getDefault(){
			return $this->default;
		}

		/**
		 * Get the current language defined by cookie or the default value
		 *
		 * @return string the current language
		 */
		public function getCurrent(){
			return $this->current;
		}

		/**
		 * Add new supported or available language
		 *
		 * @param string $name the short language name like "en", "fr".
		 * @param string $description the human readable description of this language
		 */
		public function addLang($name, $description){
			if(isset($this->availables[$name])){
				return; //already added cost in performance
			}
			if($this->isValid($name)){
				$this->availables[$name] = $description;
			}
			else{
				show_error('The language [' . $name . '] is not valid or does not exists.');
			}
		}

		/**
		 * Get the list of the application supported language
		 *
		 * @return array the list of the application language
		 */
		public function getSupported(){
			return $this->availables;
		}

		/**
		 * Add new language messages
		 *
		 * @param array $langs the languages array of the messages to be added
		 */
		public function addLangMessages(array $langs){
			foreach ($langs as $key => $value) {
				$this->set($key, $value);
			}
		}
	}
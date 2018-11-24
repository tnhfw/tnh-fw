<?php
	defined('ROOT_PATH') || exit('Access denied');

	/**
	 * for application language management
	 */
	class Lang{
		/**
		 * the supported available language supported by 
		 * this application.
		 * @example "en" => "english" 
		 * @see Lang::addLang
		 * @var array
		 */
		protected $availables = array();

		/**
		 * the all languages message
		 * @var array
		 */
		protected $languages = array();

		/**
		 * the default language to use if can not
		 *  determine the client language
		 *  
		 * @example $default = 'en'
		 * @var string
		 */
		protected $default = null;

		/**
		 * the current client language
		 * @var string
		 */
		protected $current = null;


		public function __construct(){
			$this->default = Config::get('default_language');
			//determine the current language
			$language = null;
			//if the language exists in the cookie use it
			$cfgKey = Config::get('language_cookie_name');
			$cLang = Cookie::get($cfgKey);
			if($cLang && $this->isValid($cLang)){
				$language = $cLang;
			}
			else{
				$language = $this->getDefault();
			}
			
			$path = $this->getFilePath($language);
			if(file_exists($path)){
				require_once $path;
				if(!empty($lang) && is_array($lang)){
					$this->languages = $lang;
					//free the memory
					unset($lang);
					$this->current = $language;
				}
				else{
					show_error('No language message found in '.$language.'.php');
				}
			}
			else{
				show_error('Unable to find the language file ');
			}
		}

		public function getAll(){
			return $this->languages;
		}

		public function set($key, $value){
			$this->languages[$key] = $value;
		}


		public function get($key, $default = 'LANGUAGE_ERROR'){
			if(isset($this->languages[$key])){
				return $this->languages[$key];
			}
			return $default;
		}

		public function isValid($language){
			return file_exists(LANG_PATH . $language. '.php');
		}

		public function getFilePath($language){
			if($this->isValid($language)){
				return LANG_PATH . $language . '.php';
			}
			return null;
		}

		public function getDefault(){
			return $this->default;
		}

		public function getCurrent(){
			return $this->current;
		}

		/**
		 * add new supported language
		 * @param string $name the short language name
		 * @param string $desc the human readable descrition of this language
		 */
		public function addLang($name, $descrition){
			if(isset($this->availables[$name])){
				return; //already added cost in performance
			}
			if($this->isValid($name)){
				$this->availables[$name] = $descrition;
			}
			else{
				show_error('The language ' . $name . ' is not valid or does not exists');
			}
		}

		public function getSupported(){
			return $this->availables;
		}

	}
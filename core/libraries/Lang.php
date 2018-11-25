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
				$this->current = $language;
			}
			else{
				$language = $this->getDefault();
			}
			//system language
			if(file_exists(CORE_LANG_PATH . $language . '.php')){
				require_once CORE_LANG_PATH . $language . '.php';
				if(!empty($lang) && is_array($lang)){
					$this->addLangMessages($lang);
					//free the memory
					unset($lang);
				}
				else{
					show_error('No language message found in '.$language.'.php');
				}
			}

			//app language
			if(file_exists(APP_LANG_PATH . $language . '.php')){
				require_once APP_LANG_PATH . $language . '.php';
				if(!empty($lang) && is_array($lang)){
					$this->addLangMessages($lang);
					//free the memory
					unset($lang);
				}
				else{
					show_error('No language message found in '.$language.'.php');
				}
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
			$search_dir = array(CORE_LANG_PATH, APP_LANG_PATH);
			foreach($search_dir as $dir){
				if(file_exists($dir . $language. '.php')){
					return true;
				}
			}
			return false;
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

		/**
		 * add new language messages
		 * @param array $langs the languages array of messages
		 */
		public function addLangMessages(array $langs){
			foreach ($langs as $key => $value) {
				$this->languages[$key] = $value;
			}
		}

	}
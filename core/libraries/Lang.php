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


		private $logger;

		public function __construct(){
			if(!class_exists('Log')){
	            //here the Log class is not yet loaded
	            //load it manually
	            require_once CORE_LIBRARY_PATH . 'Log.php';
	        }
	        $this->logger = new Log();
	        $this->logger->setLogger('Library::Lang');

			$this->default = Config::get('default_language');
			//determine the current language
			$language = null;
			//if the language exists in the cookie use it
			$cfgKey = Config::get('language_cookie_name');
			$this->logger->debug('Try to get the language from cookie [' .$cfgKey. ']');
			$cLang = Cookie::get($cfgKey);
			if($cLang && $this->isValid($cLang)){
				$language = $cLang;
				$this->current = $language;
				$this->logger->info('Language from cookie [' .$cfgKey. '] is valid set the language from the cookie');
			}
			else{
				$this->logger->info('Language from cookie [' .$cfgKey. '] is not set, use the default value [' .$this->getDefault(). ']');
				$language = $this->getDefault();
			}
			$systemLangPath = CORE_LANG_PATH . $language . '.php';
			$this->logger->debug('Try to include the system language file  [' .$systemLangPath. ']');
			//system language
			if(file_exists($systemLangPath)){
				$this->logger->info('System language file  [' .$systemLangPath. '] exists include it');
				require_once $systemLangPath;
				if(!empty($lang) && is_array($lang)){
					$this->logger->info('System language file  [' .$systemLangPath. '] contains the valide languages keys add them to the list');
					$this->addLangMessages($lang);
					//free the memory
					unset($lang);
				}
				else{
					show_error('No language message found in '.$language.'.php');
				}
			}
			else{
				$this->logger->warning('System language file  [' .$systemLangPath. '] does not exist');
			}

			$appLangPath = APP_LANG_PATH . $language . '.php';
			$this->logger->debug('Try to include the custom language file  [' .$appLangPath. ']');
			//app language
			if(file_exists($appLangPath)){
				$this->logger->info('Custom language file  [' .$appLangPath. '] exists include it');
				require_once $appLangPath;
				if(!empty($lang) && is_array($lang)){
					$this->logger->info('Custom language file  [' .$appLangPath. '] contains the valide languages keys add them to the list');
					$this->addLangMessages($lang);
					//free the memory
					unset($lang);
				}
				else{
					show_error('No language message found in '.$language.'.php');
				}
			}
			else{
				$this->logger->warning('Custom language file  [' .$appLangPath. '] does not exist');
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
			$this->logger->warning('Language key  [' .$key. '] does not exist use the default value [' .$default. ']');
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
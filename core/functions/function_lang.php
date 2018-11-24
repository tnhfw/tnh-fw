<?php
	defined('ROOT_PATH') || exit('Access denied');

	if(!function_exists('__')){
		/**
		 * function for the shortcut to Lang::get
		 * @param  string $key the language key to retrieve
		 * @return string  the language value
		 */
		function __($key){
			$obj = & get_instance();
			return $obj->lang->get($key);
		}

	}


	if(!function_exists('get_languages')){
		/**
		 * function for the shortcut to Lang::getSupported
		 * 
		 * @return arry all the supported languages
		 */
		function get_languages(){
			$obj = & get_instance();
			return $obj->lang->getSupported();
		}

	}
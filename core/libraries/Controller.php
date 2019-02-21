<?php
	defined('ROOT_PATH') || exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework created using the concept of codeigniter with bootstrap twitter
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

	class Controller{
		public $module = null;

		private static $instance;

		protected static $logger;

		public function __construct(){
			if(!class_exists('Log')){
				//here the Log class is not yet loaded
				//load it manually
				require_once CORE_LIBRARY_PATH . 'Log.php';
			}
			static::$logger = new Log();
			static::$logger->setLogger('MainController');
			self::$instance = & $this;

			$libraries = array('request', 'response', 'lang');
			$config = array();
			$models = array();
			$functions = array('lang');

			if(file_exists(CONFIG_PATH.'autoload.php')){
				require_once CONFIG_PATH.'autoload.php';
				if(!empty($autoload) && is_array($autoload)){
					//libraries autoload
					if(!empty($autoload['libraries'])){
						$libraries = array_merge($libraries, $autoload['libraries']);
					}

					//functions autoload
					if(!empty($autoload['functions'])){
						$functions = array_merge($functions, $autoload['functions']);
					}
					//config autoload
					if(!empty($autoload['config'])){
						$config = array_merge($config, $autoload['config']);
					}
					//models autoload
					if(!empty($autoload['models'])){
						$models = array_merge($models, $autoload['models']);
					}
				}
				else{
					show_error('No autoload configuration found in autoload.php');
				}
			}

			foreach($config as $c){
				Loader::config($c);
			}
			
			foreach($libraries as $library){
				Loader::library($library);
			}

			foreach($functions as $function){
				Loader::functions($function);
			}

			foreach($models as $model){
				Loader::model($model);
			}

			//add the supported languages ('key', 'display name')
			$languages = Config::get('languages', null);
			if(!empty($languages)){
				foreach($languages as $k => $v){
					$this->lang->addLang($k, $v);
				}
			}

			///////////////////////// PATCH SESSION HANDLER /////////////////////////////////
			//set session config
			static::$logger->debug('Setting PHP application session handler');
			set_session_config();
		}


		public static function &get_instance(){
			return self::$instance;
		}
	}

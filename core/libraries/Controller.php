<?php

	class Controller{

		private static $instance;

		public function __construct(){
			self::$instance = & $this;

			$libraries = array('loader', 'config', 'request', 'response');
			$config = array();
			$models = array();
			$functions = array();

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


			foreach($libraries as $library){
				Loader::library($library);
			}

			foreach($functions as $function){
				Loader::functions($function);
			}

			foreach($config as $c){
				Loader::config($c);
			}

			foreach($models as $model){
				Loader::model($model);
			}
		}


		public static function &get_instance(){
			return self::$instance;
		}
	}

<?php
	class Loader{
		public static $loaded = array();
		static function register(){
			spl_autoload_register(array('Loader', 'autoload'));
		}

		static function isLoaded($name, $type){
			return !empty(static::$loaded[$type][$name]);
		}

		static function isLoadedLibrary($name){
			return static::isLoaded($name, 'libraries');
		}

		static function isLoadedFunction($name){
			return static::isLoaded($name, 'functions');
		}


		static function isLoadedController($name){
			return static::isLoaded($name, 'controllers');
		}

		static function isLoadedModel($name){
			return static::isLoaded($name, 'models');
		}

		static function isLoadedConfig($name){
			return static::isLoaded($name, 'config');
		}

		static function isLoadedClass($name){
			return static::isLoaded($name, 'classes');
		}

		static function autoload($class){
			$search_dir = array(CORE_PATH, CORE_LIBRARY_PATH, LIBRARY_PATH, APPS_CONTROLLER_PATH);
			$file = $class.'.php';
			if(static::isLoadedClass($class)){
				return;
			}
			foreach($search_dir as $dir){
				if(file_exists($dir.$file)){
					require_once $dir.$file;
					if(class_exists($class)){
						static::$loaded['classes'][$class] = $dir.$file;
					}
					//is already found not to continue
					break;
				}
			}

		}

		static function controller($class, $graceful = true){
			$class = str_ireplace('.php', '', $class);
			$class = ucfirst($class);
			$file = $class.'.php';
			if(static::isLoadedController($class)){
				return;
			}
			if(file_exists(APPS_CONTROLLER_PATH.$file)){
				require_once APPS_CONTROLLER_PATH.$file;
				if(!class_exists($class)){
					show_error('The file '.$file.' exists but does not contain the class '.$class);
				}
				static::$loaded['controllers'][$class] = APPS_CONTROLLER_PATH.$file;;
			}
			else if($graceful){
				return false;
			}
			else{
				show_error('Unable to find controller class '.$class);
			}
		}

		static function model($class, $instance = null){
			$class = str_ireplace('.php', '', $class);
			$class = ucfirst($class);
			$file = $class.'.php';
			if(static::isLoadedModel($class)){
				return;
			}
			if(!$instance){
				$instance = $class;
			}
			if(file_exists(APPS_MODEL_PATH.$file)){
				require_once APPS_MODEL_PATH.$file;
				if(class_exists($class)){
					$c = new $class();
					$instance = strtolower($instance);
					$obj = & get_instance();
					$obj->{$instance} = $c;
				}
				else{
					show_error('The file '.$file.' exists but does not contain the class '.$class);
				}
			}
			else{
				show_error('Unable to find model class '.$class);
			}
			static::$loaded['models'][$class] = APPS_MODEL_PATH.$file;
		}

		static function library($class, $instance = null){
			$search_dir = array(LIBRARY_PATH, CORE_LIBRARY_PATH);
			$found = false;
			$class = str_ireplace('.php', '', $class);
			$class = ucfirst($class);
			$file = $class.'.php';
			if(static::isLoadedLibrary($class)){
				return;
			}
			if(!$instance){
				$instance = $class;
			}
			$instance = strtolower($instance);
			foreach($search_dir as $dir){
				if(file_exists($dir.$file)){
					require_once $dir.$file;
					if(class_exists($class)){
						$c = new $class();
						$obj = & get_instance();
						$obj->{$instance} = $c;
						static::$loaded['libraries'][$class] = $dir.$file;
					}
					else{
						show_error('The file '.$file.' exists but does not contain the class '.$class);
					}
					$found = true;
					//is already found not to continue
					break;
				}
			}
			if(!$found){
				show_error('Unable to find library class '.$class);
			}
		}

		static function functions($function){
			$search_dir = array(FUNCTIONS_PATH, CORE_FUNCTIONS_PATH);
			$found = false;
			$function = str_ireplace('.php', '', $function);
			$function = str_ireplace('function_', '', $function);
			$file = 'function_'.$function.'.php';
			if(static::isLoadedFunction($function)){
				return;
			}
			foreach($search_dir as $dir){
				if(file_exists($dir.$file)){
					require_once $dir.$file;
					static::$loaded['functions'][$function] = $dir.$file;
					$found = true;
					//is already found not to continue
					break;
				}
			}
			if(!$found){
				show_error('Unable to find function file '.$file);
			}
		}

		static function config($filename){
			$filename = str_ireplace('.php', '', $filename);
			$filename = str_ireplace('config_', '', $filename);
			$file = 'config_'.$filename.'.php';
			if(static::isLoadedConfig($filename)){
				return;
			}
			if(file_exists(CONFIG_PATH.$file)){
				require_once CONFIG_PATH.$file;
				if(!empty($config) && is_array($config)){
					Config::setAll($config);
				}
				else{
					show_error('No configuration found in '.$file);
				}
			}
			else{
				show_error('Unable to find config file '.$file);
			}
			static::$loaded['config'][$filename] = CONFIG_PATH.$file;
		}
	}

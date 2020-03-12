<?php
	//Autoload function
	function tests_autoload($class){
		$classesMap = array(
			//Caches
			'ApcCache' => CORE_CLASSES_CACHE_PATH . 'ApcCache.php',
			'CacheInterface' => CORE_CLASSES_CACHE_PATH . 'CacheInterface.php',
			'FileCache' => CORE_CLASSES_CACHE_PATH . 'FileCache.php',
            
            //Database
			'Database' => CORE_CLASSES_DATABASE_PATH . 'Database.php',
			'DatabaseCache' => CORE_CLASSES_DATABASE_PATH . 'DatabaseCache.php',
			'DatabaseConnection' => CORE_CLASSES_DATABASE_PATH . 'DatabaseConnection.php',
			'DatabaseQueryBuilder' => CORE_CLASSES_DATABASE_PATH . 'DatabaseQueryBuilder.php',
			'DatabaseQueryResult' => CORE_CLASSES_DATABASE_PATH . 'DatabaseQueryResult.php',
			'DatabaseQueryRunner' => CORE_CLASSES_DATABASE_PATH . 'DatabaseQueryRunner.php',
			
			//models
			'DBSessionHandlerModel' => CORE_CLASSES_MODEL_PATH . 'DBSessionHandlerModel.php',
			'Model' => CORE_CLASSES_MODEL_PATH . 'Model.php',
			
            //Core classes
			'BaseClass' => CORE_CLASSES_PATH . 'BaseClass.php',
			'BaseStaticClass' => CORE_CLASSES_PATH . 'BaseStaticClass.php',
			'Config' => CORE_CLASSES_PATH . 'Config.php',
			'Controller' => CORE_CLASSES_PATH . 'Controller.php',
            'DBSessionHandler' => CORE_CLASSES_PATH . 'DBSessionHandler.php',
			'EventInfo' => CORE_CLASSES_PATH . 'EventInfo.php',
            'GlobalVar' => CORE_CLASSES_PATH . 'GlobalVar.php',
			'EventDispatcher' => CORE_CLASSES_PATH . 'EventDispatcher.php',
			'Lang' => CORE_CLASSES_PATH . 'Lang.php',
			'Loader' => CORE_CLASSES_PATH . 'Loader.php',
			'Log' => CORE_CLASSES_PATH . 'Log.php',
			'Module' => CORE_CLASSES_PATH . 'Module.php',
			'Request' => CORE_CLASSES_PATH . 'Request.php',
			'Response' => CORE_CLASSES_PATH . 'Response.php',
			'Router' => CORE_CLASSES_PATH . 'Router.php',
			'Security' => CORE_CLASSES_PATH . 'Security.php',
			'Session' => CORE_CLASSES_PATH . 'Session.php',
			'Url' => CORE_CLASSES_PATH . 'Url.php',
			//Core libraries
			'Assets' => CORE_LIBRARY_PATH . 'Assets.php',
			'Benchmark' => CORE_LIBRARY_PATH . 'Benchmark.php',
			'Browser' => CORE_LIBRARY_PATH . 'Browser.php',
			'Cookie' => CORE_LIBRARY_PATH . 'Cookie.php',
			'Email' => CORE_LIBRARY_PATH . 'Email.php',
			'Form' => CORE_LIBRARY_PATH . 'Form.php',
			'FormValidation' => CORE_LIBRARY_PATH . 'FormValidation.php',
			'Html' => CORE_LIBRARY_PATH . 'Html.php',
			'Pagination' => CORE_LIBRARY_PATH . 'Pagination.php',
			'PDF' => CORE_LIBRARY_PATH . 'PDF.php',
			'StringHash' => CORE_LIBRARY_PATH . 'StringHash.php',
			'Upload' => CORE_LIBRARY_PATH . 'Upload.php',
		);
		if(isset($classesMap[$class])){
			if(file_exists($classesMap[$class])){
				require_once $classesMap[$class];
			}
			else{
				echo 'File for class ' . $class . ' not found';
			}
		}
	}
    
    /**
	* Register the tests autoload
	*/
	spl_autoload_register('tests_autoload');
    

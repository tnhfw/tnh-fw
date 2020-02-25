<?php

	/**
	* Function to test private & protected method
	*/
	function run_private_protected_method($object, $method, array $args = array()){
		$r = new ReflectionClass(get_class($object));
		$m = $r->getMethod($method);
		$m->setAccessible(true);
		return $m->invokeArgs($object, $args);
	}
    
    /**
	* Function to return the correct database configuration
	*/
    function get_db_config(){
        return array(
                    'driver'    =>  'sqlite',
                    'database'  =>  TESTS_PATH . 'assets/db_tests.db',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_general_ci',
                );
    }
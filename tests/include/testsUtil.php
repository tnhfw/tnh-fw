<?php

	/**
	* Function to test private & protected method
	*/
	function runPrivateOrProtectedMethod($object, $method, array $args = array()){
		$r = new ReflectionClass(get_class($object));
		$m = $r->getMethod($method);
		$m->setAccessible(true);
		return $m->invokeArgs($object, $args);
	}
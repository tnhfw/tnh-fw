<?php 

	/**
     * String functions tests
     *
     * @group core
     * @group functions
     */
	class FunctionStringTest extends TnhTestCase {	
	
        public static function setUpBeforeClass() {
            require_once CORE_FUNCTIONS_PATH . 'function_string.php';
		}
		
		public function testGetRandomString() {
            //default
            $value = get_random_string();
			$this->assertSame(10, strlen($value));
			$this->assertTrue(preg_match('/^([a-zA-Z0-9]+){10}$/', $value) == 1);
            
            //using type
            $value = get_random_string('num');
			$this->assertSame(10, strlen($value));
			$this->assertTrue(preg_match('/^([0-9]+){10}$/', $value) == 1);
            
            //using length
            $value = get_random_string('num', 7);
			$this->assertSame(7, strlen($value));
			$this->assertTrue(preg_match('/^([0-9]+){7}$/', $value) == 1);
            
            //Using lower case
            $value = get_random_string('alpha', 4, true);
			$this->assertSame(4, strlen($value));
			$this->assertTrue(preg_match('/^([a-z]+){4}$/', $value) == 1);
		}

	}
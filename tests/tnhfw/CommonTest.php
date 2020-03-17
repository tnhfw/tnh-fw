<?php 

	/**
     * Common functions tests
     *
     * @group core
     * @group commons
     */
	class CommonTest extends TnhTestCase {	

		public function testFunctionGetConfigKeyNotExist(){
			$key = 'foo';
			$cfg = get_config($key);
			$this->assertNull($cfg);
		}
		
		public function testFunctionGetConfigKeyNotExistUsingDefaultValue(){
			$key = 'foo';
			$expected = 'bar';
			$cfg = get_config($key, $expected);
			$this->assertEquals($cfg, $expected);
		}
		
		public function testFunctionGetConfigAfterSet(){
			$key = 'foo';
			$expected = 'bar';
			$this->config->set($key, $expected);
			$cfg = get_config($key);
            $this->assertEquals($cfg, $expected);
		}
        
        
		
	}
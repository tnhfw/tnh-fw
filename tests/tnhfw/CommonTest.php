<?php 

	
	class CommonTest extends TnhTestCase
	{	
	
		public static function setUpBeforeClass()
		{
			
		}
		
		public static function tearDownAfterClass()
		{
			
		}
	
		protected function setUp()
		{
		}

		protected function tearDown()
		{
		}

		
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
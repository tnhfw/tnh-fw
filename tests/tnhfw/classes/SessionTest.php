<?php 
	
	class SessionTest extends TnhTestCase
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

		// tests
		public function testSomeFeature()
		{
			Session::set('foo', 'bar');
			$this->assertEquals('bar', Session::get('foo'));
			//$this->expectException(InvalidArgumentException::class);
		}
	}
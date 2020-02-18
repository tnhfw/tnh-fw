<?php 
	use PHPUnit\Framework\TestCase;

	class SessionTest extends TestCase
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
		}
		
		public function testExc(){
			 //$this->expectException(InvalidArgumentException::class);
		}
	}
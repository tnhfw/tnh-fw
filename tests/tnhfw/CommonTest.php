<?php 

	use PHPUnit\Framework\TestCase;

	class CommonTest extends TestCase
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
			$c = new Config();
			$c->init();
			$c->set($key, $expected);
			$cfg = get_config($key);
			$this->assertEquals($cfg, $expected);
		}
		
		public function testVsStream(){
		
			$vfs =  vfsStream::setup('tnhfw');
			$this->assertFalse($vfs->hasChild('test'));
			mkdir(vfsStream::url('tnhfw') . DS . 'test');
			$this->assertTrue($vfs->hasChild('test'));
			echo vfsStream::url('test');
			
			
		}
	}
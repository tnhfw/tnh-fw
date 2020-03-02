<?php 

	
	class ConfigTest extends TnhTestCase
	{	
	
		public static function setUpBeforeClass()
		{
		
		}
		
		public static function tearDownAfterClass()
		{
			
		}
		
		protected function setUp()
		{
            //prevent duplicate or old value
            Config::deleteAll();
		}

		protected function tearDown()
		{
		}
		
		public function testGetValueWhenKeyNotExist()
		{
			$value = Config::get('foo');
            $this->assertNull($value);
		}
        
        public function testGetValueWhenKeyNotExistUsingDefaultValue()
		{
			$value = Config::get('foo', 'bar');
            $this->assertSame($value, 'bar');
		}
        
        public function testGetValueWhenKeyExist()
		{
			$value = 1234567890;
			Config::set('foo', $value);
            $this->assertSame($value, Config::get('foo'));
		}
        
        public function testSetValue()
		{
            //string
			$value = 'bar';
			Config::set('foo', $value);
            $this->assertSame($value, Config::get('foo'));
            
            //int
            $value = 1234;
            Config::set('foo', $value);
            $this->assertSame($value, Config::get('foo'));
            
             //double
            $value = 1234.001;
            Config::set('foo', $value);
            $this->assertSame($value, Config::get('foo'));
            
            //boolean
            $value = false;
            Config::set('foo', $value);
            $this->assertSame($value, Config::get('foo'));
            $this->assertFalse(Config::get('foo'));
            
            //array 1
            $value = array();
            Config::set('foo', $value);
            $this->assertSame($value, Config::get('foo'));
            $this->assertEmpty(Config::get('foo'));
            
            //array 2
            $value = array('bar');
            Config::set('foo', $value);
            $this->assertSame($value, Config::get('foo'));
            $this->assertSame(1, count(Config::get('foo')));
            $this->assertNotEmpty(Config::get('foo'));
            $this->assertContains('bar', Config::get('foo'));
            
            //array 3
            $value = array('key1' => 'value', 'key2' => true);
            Config::set('foo', $value);
            $this->assertSame($value, Config::get('foo'));
            $this->assertSame(2, count(Config::get('foo')));
            $this->assertNotEmpty(Config::get('foo'));
            $this->assertArrayHasKey('key1', Config::get('foo'));
            $this->assertArrayHasKey('key2', Config::get('foo'));
            
            //object 1
            $value = new stdClass();
            Config::set('foo', $value);
            $this->assertSame($value, Config::get('foo'));
            $this->assertInstanceOf('stdClass', Config::get('foo'));
            
            //object 2
            $value = new stdClass();
            $value->foo = 'bar';
            Config::set('foo', $value);
            $this->assertSame($value, Config::get('foo'));
            $this->assertSame('bar', Config::get('foo')->foo);
            $this->assertInstanceOf('stdClass', Config::get('foo'));
		}
        
        public function testGetAll()
		{
             $this->assertEmpty(Config::getAll());
             Config::set('foo', 'bar');
             $this->assertNotEmpty(Config::getAll());
             
        }
        
        public function testSetAll()
		{
             $this->assertEmpty(Config::getAll());
             Config::set('foo', 'bar');
             Config::setAll(array('bar' => 'foo'));
             $this->assertNotEmpty(Config::getAll());
             $this->assertSame(2, count(Config::getAll()));  
        }
        
        public function testSetAllArgumentIsEmpty()
		{
             $this->assertEmpty(Config::getAll());
             Config::set('foo', 'bar');
             Config::setAll(array());
             $this->assertNotEmpty(Config::getAll());
             $this->assertSame(1, count(Config::getAll()));
        }
        
        public function testDeleteWhenKeyNotExist()
		{
             $result = Config::delete('foo');
             $this->assertFalse($result); 
        }
        
         public function testDeleteWhenKeyExist()
		{
             Config::set('foo', 'bar');
             $this->assertSame(1, count(Config::getAll()));
             $result = Config::delete('foo');
             $this->assertTrue($result); 
             $this->assertEmpty(Config::getAll());
             $this->assertNull(Config::get('foo'));
             $this->assertSame(0, count(Config::getAll()));
        }
        
        public function testDeleteAll()
		{
             Config::set('foo', 'bar');
             Config::set('bar', 'foo');
             $this->assertSame(2, count(Config::getAll()));
             $this->assertNotNull(Config::get('foo'));
             $this->assertNotNull(Config::get('bar'));
           
             Config::deleteAll();
             $this->assertEmpty(Config::getAll());
             $this->assertSame(0, count(Config::getAll()));
             $this->assertNull(Config::get('foo'));
             $this->assertNull(Config::get('bar'));
        }
        
        

	}
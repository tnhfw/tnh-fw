<?php 
	
	class SessionTest extends TnhTestCase
	{
        
        protected function setUp()
        {
            parent::setUp();
            $_SESSION = array();
        }
		
		public function testGetValueWhenKeyNotExist()
		{
            $session = new Session();
			$value = $session->get('foo');
            $this->assertNull($value);
		}
        
        public function testGetValueWhenKeyNotExistUsingDefaultValue()
		{
            $session = new Session();
			$value = $session->get('foo', 'bar');
            $this->assertSame($value, 'bar');
		}
        
        public function testGetValueWhenKeyExist()
		{
            $session = new Session();
			$value = 1234567890;
			$session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
		}
        
        public function testSetValue()
		{
            $session = new Session();
            //string
			$value = 'bar';
			$session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
            
            //int
            $value = 1234;
            $session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
            
             //double
            $value = 1234.001;
            $session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
            
            //boolean
            $value = false;
            $session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
            $this->assertFalse($session->get('foo'));
            
            //array 1
            $value = array();
            $session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
            $this->assertEmpty($session->get('foo'));
            
            //array 2
            $value = array('bar');
            $session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
            $this->assertSame(1, count($session->get('foo')));
            $this->assertNotEmpty($session->get('foo'));
            $this->assertContains('bar', $session->get('foo'));
            
            //array 3
            $value = array('key1' => 'value', 'key2' => true);
            $session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
            $this->assertSame(2, count($session->get('foo')));
            $this->assertNotEmpty($session->get('foo'));
            $this->assertArrayHasKey('key1', $session->get('foo'));
            $this->assertArrayHasKey('key2', $session->get('foo'));
            
            //object 1
            $value = new stdClass();
            $session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
            $this->assertInstanceOf('stdClass', $session->get('foo'));
            
            //object 2
            $value = new stdClass();
            $value->foo = 'bar';
            $session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
            $this->assertSame('bar', $session->get('foo')->foo);
            $this->assertInstanceOf('stdClass', $session->get('foo'));
		}
 
        public function testGetFlashValueWhenKeyNotExist()
		{
            $session = new Session();
			$value = $session->getFlash('foo');
            $this->assertNull($value);
		}
        
        public function testGetFlashValueWhenKeyNotExistUsingDefaultValue()
		{
            $session = new Session();
			$value = $session->getFlash('foo', 'bar');
            $this->assertSame($value, 'bar');
		}
        
        public function testGetFlashValueWhenKeyExist()
		{
            $session = new Session();
			$value = 1234567890;
			$session->setFlash('foo', $value);
            $this->assertSame($value, $session->getFlash('foo'));
            //after get session flash data the value will be deleted
            $this->assertNull($session->getFlash('foo'));
		}
        
        public function testSetFlashValue()
		{
            $session = new Session();
            //string
			$value = 'bar';
			$session->setFlash('foo', $value);
            $this->assertSame($value, $session->getFlash('foo'));
            $this->assertNull($session->getFlash('foo'));
            
            //int
            $value = 1234;
            $session->setFlash('foo', $value);
            $this->assertSame($value, $session->getFlash('foo'));
            $this->assertNull($session->getFlash('foo'));
            
             //double
            $value = 1234.001;
            $session->setFlash('foo', $value);
            $this->assertSame($value, $session->getFlash('foo'));
            $this->assertNull($session->getFlash('foo'));
            
            //boolean
            $value = false;
            $session->setFlash('foo', $value);
            $this->assertSame($value, $session->getFlash('foo'));
            $this->assertNull($session->getFlash('foo'));
            
            //array 1
            $value = array();
            $session->setFlash('foo', $value);
            $this->assertSame($value, $session->getFlash('foo'));
            $this->assertNull($session->getFlash('foo'));
            
            //array 2
            $value = array('bar');
            $session->setFlash('foo', $value);
            $this->assertSame($value, $session->getFlash('foo'));
            $this->assertNull($session->getFlash('foo'));
            
            //array 3
            $value = array('key1' => 'value', 'key2' => true);
            $session->setFlash('foo', $value);
            $this->assertSame($value, $session->getFlash('foo'));
            $this->assertNull($session->getFlash('foo'));
            
            //object 1
            $value = new stdClass();
            $session->setFlash('foo', $value);
            $this->assertSame($value, $session->getFlash('foo'));
            $this->assertNull($session->getFlash('foo'));
            
            //object 2
            $value = new stdClass();
            $value->foo = 'bar';
            $session->setFlash('foo', $value);
            $this->assertSame($value, $session->getFlash('foo'));
            $this->assertNull($session->getFlash('foo'));
		}
        
        public function testHasFlashValue()
		{
            $session = new Session();
            $value = 'bar';
			$session->setFlash('foo', $value);
            $this->assertTrue($session->hasFlash('foo'));
            $this->assertFalse($session->hasFlash('foobar'));
            $this->assertNull($session->getFlash('foobar'));
        }
        
        public function testClearKeyDoesNotExist()
		{
            $session = new Session();
            $this->assertNull($session->get('foo'));
            $this->assertFalse($session->clear('foo'));  
        }
        
        public function testClearKeyExist()
		{
            $session = new Session();
           
			$value = 'bar';
			$session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
            $this->assertTrue($session->clear('foo'));
            $this->assertNull($session->get('foo'));
        }
        
        
        public function testClearFlashKeyDoesNotExist()
		{
            $session = new Session();
            $this->assertNull($session->getFlash('foo'));
            $this->assertFalse($session->clearFlash('foo'));  
        }
        
        public function testClearFlashKeyExist()
		{
            $session = new Session();
           
			$value = 'bar';
			$session->setFlash('foo', $value);
            $this->assertTrue($session->hasFlash('foo'));
            $this->assertTrue($session->clearFlash('foo'));
            $this->assertNull($session->getFlash('foo'));
            $this->assertFalse($session->hasFlash('foo'));
        }
        
        public function testExistsKeyDoesNotExist()
		{
            $session = new Session();
            $this->assertNull($session->get('foo'));
            $this->assertFalse($session->exists('foo'));  
        }
        
        public function testExistsKeyExist()
		{
            $session = new Session();
           
			$value = 'bar';
			$session->set('foo', $value);
            $this->assertSame($value, $session->get('foo'));
            $this->assertTrue($session->exists('foo'));
        }
        
        public function testClearAll()
		{
            $session = new Session();
            //Can not test because function "session_unset" and "session_destroy" are not availble in CLI
            $session->clearAll();
        }
	}
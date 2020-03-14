<?php 

	/**
     * Config class tests
     *
     * @group core
     * @group core_classes
     */
	class ConfigTest extends TnhTestCase {	
	
		
		public function testConstructor() {
            //Don't init
            $c = new Config(false);
            $this->assertEmpty($this->config->getAll());
            
            //init configuration
            $c = new Config(true);
            $this->assertNotEmpty($this->config->getAll());
		}
        
        public function testGetValueWhenKeyNotExist() {
            $value = $this->config->get('foo');
            $this->assertNull($value);
		}
        
        public function testGetValueWhenKeyNotExistUsingDefaultValue() {
			$value = $this->config->get('foo', 'bar');
            $this->assertSame($value, 'bar');
		}
        
        public function testGetValueWhenKeyExist() {
			$value = 1234567890;
			$this->config->set('foo', $value);
            $this->assertSame($value, $this->config->get('foo'));
		}
        
        public function testSetValue() {
            //string
			$value = 'bar';
			$this->config->set('foo', $value);
            $this->assertSame($value, $this->config->get('foo'));
            
            //int
            $value = 1234;
            $this->config->set('foo', $value);
            $this->assertSame($value, $this->config->get('foo'));
            
             //double
            $value = 1234.001;
            $this->config->set('foo', $value);
            $this->assertSame($value, $this->config->get('foo'));
            
            //boolean
            $value = false;
            $this->config->set('foo', $value);
            $this->assertSame($value, $this->config->get('foo'));
            $this->assertFalse($this->config->get('foo'));
            
            //array 1
            $value = array();
            $this->config->set('foo', $value);
            $this->assertSame($value, $this->config->get('foo'));
            $this->assertEmpty($this->config->get('foo'));
            
            //array 2
            $value = array('bar');
            $this->config->set('foo', $value);
            $this->assertSame($value, $this->config->get('foo'));
            $this->assertSame(1, count($this->config->get('foo')));
            $this->assertNotEmpty($this->config->get('foo'));
            $this->assertContains('bar', $this->config->get('foo'));
            
            //array 3
            $value = array('key1' => 'value', 'key2' => true);
            $this->config->set('foo', $value);
            $this->assertSame($value, $this->config->get('foo'));
            $this->assertSame(2, count($this->config->get('foo')));
            $this->assertNotEmpty($this->config->get('foo'));
            $this->assertArrayHasKey('key1', $this->config->get('foo'));
            $this->assertArrayHasKey('key2', $this->config->get('foo'));
            
            //object 1
            $value = new stdClass();
            $this->config->set('foo', $value);
            $this->assertSame($value, $this->config->get('foo'));
            $this->assertInstanceOf('stdClass', $this->config->get('foo'));
            
            //object 2
            $value = new stdClass();
            $value->foo = 'bar';
            $this->config->set('foo', $value);
            $this->assertSame($value, $this->config->get('foo'));
            $this->assertSame('bar', $this->config->get('foo')->foo);
            $this->assertInstanceOf('stdClass', $this->config->get('foo'));
		}
        
        public function testGetAll() {
             $this->assertEmpty($this->config->getAll());
             $this->config->set('foo', 'bar');
             $this->assertNotEmpty($this->config->getAll());
             
        }
        
        public function testSetAll() {
             $this->assertEmpty($this->config->getAll());
             $this->config->set('foo', 'bar');
             $this->config->setAll(array('bar' => 'foo'));
             $this->assertNotEmpty($this->config->getAll());
             $this->assertSame(2, count($this->config->getAll()));  
        }
        
        public function testSetAllArgumentIsEmpty() {
             $this->assertEmpty($this->config->getAll());
             $this->config->set('foo', 'bar');
             $this->config->setAll(array());
             $this->assertNotEmpty($this->config->getAll());
             $this->assertSame(1, count($this->config->getAll()));
        }
        
        public function testDeleteWhenKeyNotExist() {
             $result = $this->config->delete('foo');
             $this->assertFalse($result); 
        }
        
         public function testDeleteWhenKeyExist() {
             $this->config->set('foo', 'bar');
             $this->assertSame(1, count($this->config->getAll()));
             $result = $this->config->delete('foo');
             $this->assertTrue($result); 
             $this->assertEmpty($this->config->getAll());
             $this->assertNull($this->config->get('foo'));
             $this->assertSame(0, count($this->config->getAll()));
        }
        
        public function testDeleteAll() {
             $this->config->set('foo', 'bar');
             $this->config->set('bar', 'foo');
             $this->assertSame(2, count($this->config->getAll()));
             $this->assertNotNull($this->config->get('foo'));
             $this->assertNotNull($this->config->get('bar'));
           
             $this->config->deleteAll();
             $this->assertEmpty($this->config->getAll());
             $this->assertSame(0, count($this->config->getAll()));
             $this->assertNull($this->config->get('foo'));
             $this->assertNull($this->config->get('bar'));
        }
        
        

	}
<?php 

	/**
     * Cookie library class tests
     *
     * @group core
     * @group libraries
     */
	class CookieTest extends TnhTestCase {	
		
		public function testGetValueWhenKeyNotExist() {
            $c = new Cookie();
			$value = $c->get('foo');
            $this->assertNull($value);
		}
        
        public function testGetValueWhenKeyNotExistUsingDefaultValue() {
            $c = new Cookie();
			$value = $c->get('foo', 'bar');
            $this->assertSame($value, 'bar');
		}
        
        public function testGetValueWhenKeyExist() {
            $value = 1234567890;
            $_COOKIE['foo'] = $value;
            
            $c = new Cookie();
            $this->assertSame($value, $c->get('foo'));
		}
        
        public function testSetValue() {
            //Note: setcookie not work in cli mode, so all assertion below is the fuck
            $c = new Cookie();
            
            //string
			$value = 'bar';
			$c->set('foo', $value);
            $this->assertNull($c->get('foo'));
            
            $c->set('foo', $value, 1000);
            $this->assertNull($c->get('foo'));
        }
        
        public function testDeleteWhenKeyNotExist() {
             $c = new Cookie();
             $this->assertFalse($c->delete('foo')); 
        }
        
        public function testDeleteWhenKeyExist() {
              $value = 1234567890;
              $_COOKIE['foo'] = $value;
              
              $c = new Cookie();
              $this->assertSame($value, $c->get('foo'));
              
              $this->assertTrue($c->delete('foo')); 
              $this->assertNull($c->get('foo'));
        }
        
        public function testExistsKeyDoesNotExist() {
            $c = new Cookie();
            $this->assertNull($c->get('foo'));
            $this->assertFalse($c->exists('foo'));  
        }
        
        public function testExistsKeyExist() {
            $value = 1234567890;
            $_COOKIE['foo'] = $value;
          
            $c = new Cookie();
            $this->assertTrue($c->exists('foo'));
        }

	}
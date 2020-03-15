<?php 

	/**
     * BaseClass class tests
     *
     * @group core
     * @group core_classes
     * @group base
     */
     class BaseClassTest extends TnhTestCase {	
		
	public function testSetGetLogger() {
            $o = new BaseClass();
            $o->setLogger(null);
	    $this->assertNull($o->getLogger());
            $o->setLogger(new Log());
            $this->assertInstanceOf('Log', $o->logger);
	}
        
               
        
        public function testSetDependency() {
            $o = new BaseClass();
            $o = $this->runPrivateProtectedMethod($o, 'setDependency', array('foo', 'Benchmark', 'libraries'));
            $this->assertInstanceOf('Benchmark', $o->foo);
	}

     }

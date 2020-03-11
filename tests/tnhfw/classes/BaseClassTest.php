<?php 

	/**
     * BaseClass class tests
     *
     * @group core
     * @group core_classes
     * @group base
     */
	class BaseClassTest extends TnhTestCase {	
		
		public function testSetLoggerSimple() {
            $o = new BaseClass();
            $o->setLogger(null);
			$this->assertNull($o->getLogger());
		}
        
        public function testSetLoggerFromParamOrCreate() {
            $o = new BaseClass();
            $o = $this->runPrivateProtectedMethod($o, 'setLoggerFromParamOrCreate', array(null));
            $this->assertInstanceOf('Log', $o->getLogger());
		}
        
        
        public function testSetDependenciesInstanceIsNull() {
            $o = new BaseClass();
            $o = $this->runPrivateProtectedMethod($o, 'setDependencyInstanceFromParamOrCreate', array('foo', null, 'Benchmark', 'libraries'));
            $this->assertInstanceOf('Benchmark', $o->foo);
		}
        
        public function testSetDependenciesInstanceIsNotNull() {
            $o = new BaseClass();//here logger already set to Class::BaseClass
            $o = $this->runPrivateProtectedMethod($o, 'setDependencyInstanceFromParamOrCreate', array('fooLogger', new Log()));
            $this->assertInstanceOf('Log', $o->fooLogger);
            $this->assertEquals('Class::BaseClass', $o->getLogger()->getLogger());
		}

	}
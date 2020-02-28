<?php 

	use PHPUnit\Framework\TestCase;

	class BaseClassTest extends TestCase
	{	
		
		public function testSetLoggerSimple()
		{
            $o = new BaseClass();
            $o->setLogger(null);
			$this->assertNull($o->getLogger());
		}
        
        public function testSetLoggerFromParamOrCreate()
		{
            $o = new BaseClass();
            $o = run_private_protected_method($o, 'setLoggerFromParamOrCreate', array(null));
            $this->assertInstanceOf('Log', $o->getLogger());
		}
        
        public function testSetDependenciesInstanceIsNull()
		{
            $o = new BaseClass();
            $o = run_private_protected_method($o, 'setDependencyInstanceFromParamOrCreate', array('foo', null, 'Request'));
            $this->assertInstanceOf('Request', $o->foo);
		}
        
        public function testSetDependenciesInstanceIsNotNull()
		{
            $o = new BaseClass();//here logger already set to Class::BaseClass
            $o = run_private_protected_method($o, 'setDependencyInstanceFromParamOrCreate', array('fooLogger', new Log()));
            $this->assertInstanceOf('Log', $o->fooLogger);
            $this->assertEquals('Class::BaseClass', $o->getLogger()->getLogger());
		}

	}
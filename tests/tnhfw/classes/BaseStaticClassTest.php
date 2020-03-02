<?php 

	
	class BaseStaticClassTest extends TnhTestCase
	{	
		
		public function testGetLoggerDefault()
		{
            $logger = BaseStaticClass::getLogger();
			$this->assertInstanceOf('Log', $logger);
            $this->assertEquals('Class::BaseStaticClass', $logger->getLogger());
		}
        
        public function testGetLoggerWhenIsNull()
		{
            BaseStaticClass::setLogger(null);
            $logger = BaseStaticClass::getLogger();
            $this->assertInstanceOf('Log', $logger);
            $this->assertEquals('Class::BaseStaticClass', $logger->getLogger());
		}
        
        public function testSetLogger()
		{
            $logger = BaseStaticClass::setLogger(new Log());
			$this->assertInstanceOf('Log', $logger);
            $this->assertEquals('ROOT_LOGGER', $logger->getLogger());
		}
        
        public function testSetLoggerUsingLogInstanceWithLoggerNameSet()
		{
            $log = new Log();
            $log->setLogger('FOO');
            $logger = BaseStaticClass::setLogger($log);
			$this->assertInstanceOf('Log', $logger);
            $this->assertEquals('FOO', $logger->getLogger());
            $this->assertEquals('FOO', $log->getLogger());
		}

	}
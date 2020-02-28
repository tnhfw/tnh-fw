<?php 

	use PHPUnit\Framework\TestCase;

	class ControllerTest extends TestCase
	{	
		
		public function testGetInstance()
		{
			$c = new Controller();
            $this->assertInstanceOf('Controller', $c);
            $this->assertNotNull(Controller::get_instance());
            $this->assertInstanceOf('Controller', Controller::get_instance());
		}
        
        public function testSetModuleNameUsingRouter()
		{
            $router = $this->getMockBuilder('Router')->getMock();
			$router->expects($this->any())
                    ->method('getModule')
                    ->will($this->returnValue('fooModule'));
            
			$c = new Controller();
            $this->assertNull($c->moduleName);
            $c->router = $router;
            run_private_protected_method($c, 'setModuleNameFromRouter', array(null));
            $this->assertSame('fooModule', $c->moduleName);
            $this->assertNotNull($c->moduleName);
		}
        
        public function testSetCacheFromParamOrConfigParamIsNotNull()
		{
            $cache = $this->getMockBuilder('FileCache')->getMock();
            $cache->expects($this->any())
                    ->method('isSupported')
                    ->will($this->returnValue(true));
            //enable cache feature
            Config::init();
            Config::set('cache_enable', true);
            
			$c = new Controller();
            run_private_protected_method($c, 'setCacheFromParamOrConfig', array($cache));
            $this->assertInstanceOf('FileCache', $c->cache);
		}
        
        public function testSetCacheFromParamOrConfigParamIsNull()
		{
            $cache = $this->getMockBuilder('ApcCache')->getMock();
            $cache->expects($this->any())
                    ->method('isSupported')
                    ->will($this->returnValue(true));
            //enable cache feature
            Config::init();
            Config::set('cache_enable', true);
            Config::set('cache_handler', 'ApcCache');
            
            $c = new Controller();
            //assign manually the instance name will be changed to cache in setCacheFromParamOrConfig()
            $c->apccache = $cache;
			
            run_private_protected_method($c, 'setCacheFromParamOrConfig', array(null));
            $this->assertInstanceOf('ApcCache', $c->cache);
		}
        
        

	}
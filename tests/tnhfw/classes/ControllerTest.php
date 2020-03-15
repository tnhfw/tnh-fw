<?php 

	/**
     * Controller class tests
     *
     * @group core
     * @group core_classes
     */
	class ControllerTest extends TnhTestCase {	
		
		public function testGetInstance() {
			$c = new Controller();
            $this->assertInstanceOf('Controller', $c);
            $this->assertNotNull(Controller::getInstance());
            $this->assertInstanceOf('Controller', Controller::getInstance());
		}
        
        public function testSetAppSupportedLanguages() {
            $this->config->set('default_language', 'en');
            $this->config->set('languages', array('en'=>'english'));
            
			$c = new Controller();
            $this->assertInstanceOf('Controller', $c);
            $this->assertNotNull(Controller::getInstance());
            $this->assertInstanceOf('Controller', Controller::getInstance());
            $this->runPrivateProtectedMethod($c, 'setAppSupportedLanguages', array());
            $this->assertNotEmpty($c->lang->getSupported());
            
		}
        
        public function testSetModuleNameUsingRouter() {
            $router = $this->getMockBuilder('Router')
                            ->disableOriginalConstructor()
                            ->getMock();
                            
			$router->expects($this->any())
                    ->method('getModule')
                    ->will($this->returnValue('fooModule'));
            
			$c = new Controller();
            $this->assertNull($c->moduleName);
            $c->router = $router;
            $this->runPrivateProtectedMethod($c, 'setModuleNameFromRouter', array(null));
            $this->assertSame('fooModule', $c->moduleName);
            $this->assertNotNull($c->moduleName);
		}
        
  
        
        public function testSetCacheInstance() {
            $cache = $this->getMockBuilder('ApcCache')->getMock();
            $cache->expects($this->any())
                    ->method('isSupported')
                    ->will($this->returnValue(true));
            //enable cache feature
            $this->config->set('cache_enable', true);
            $this->config->set('cache_handler', 'ApcCache');
            
            $c = new Controller();
            //assign manually the instance name will be changed to cache in setCacheFromParamOrConfig()
            $c->apccache = $cache;
			
            $this->runPrivateProtectedMethod($c, 'setCacheIfEnabled', array());
            $this->assertInstanceOf('ApcCache', $c->cache);
            $this->assertObjectHasAttribute('cache', $c);
            $this->assertAttributeInstanceOf('ApcCache', 'cache', $c);
		}
        
        

	}

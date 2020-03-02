<?php 

	
	class RouterTest extends TnhTestCase
	{	
	
		public static function setUpBeforeClass()
		{
		
		}
		
		public static function tearDownAfterClass()
		{
			
		}
		
		protected function setUp()
		{
		}

		protected function tearDown()
		{
		}
		
		public function testAutoUri()
		{
            //when application run in CLI the first argument will be used as route URI
            $_SERVER['argv'][1] = '';
            
            $r = new Router();
            //remove all all config
            $r->setRouteConfiguration(array(), false)
              ->setRouteUri()
              ->setRouteSegments()
              ->determineRouteParamsInformation();

            $this->assertNull($r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testCustomUri()
		{
            $r = new Router();
            $r->setRouteUri('users/profile/34/54')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('users', $r->getController());
			$this->assertSame('profile', $r->getMethod());
			$this->assertSame(2, count($r->getArgs()));
		}
        
        public function testWithCustomConfigControllerMethod()
		{
            $r = new Router();
            $r->add('/foo/bar', 'fooController@fooMethod')
              ->setRouteUri('/foo/bar')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('fooController', $r->getController());
			$this->assertSame('fooMethod', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
			$this->assertNull($r->getModule());
		}
        
        public function testWithCustomConfigModuleControllerMethod()
		{
            $r = new Router();
            $r->add('/foo/bar', 'fooModule#fooController@fooMethod')
              ->setRouteUri('/foo/bar')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('fooController', $r->getController());
			$this->assertSame('fooMethod', $r->getMethod());
			$this->assertSame('fooModule', $r->getModule());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testWithCustomConfigUsingAnyPattern()
		{
            $r = new Router();
            $r->add('/foo/(:any)', 'fooController@fooMethod')
              ->setRouteUri('/foo/bar123-baz')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('fooController', $r->getController());
			$this->assertSame('fooMethod', $r->getMethod());
			$this->assertSame(1, count($r->getArgs()));
            $args = $r->getArgs();
            $this->assertSame('bar123-baz', $args[0]);
		}
        
         public function testWithCustomConfigUsingNumericPattern()
		{
            $r = new Router();
            $r->add('/foo/(:num)', 'fooController@fooMethod')
              ->setRouteUri('/foo/34')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('fooController', $r->getController());
			$this->assertSame('fooMethod', $r->getMethod());
			$this->assertSame(1, count($r->getArgs()));
            $args = $r->getArgs();
            $this->assertSame('34', $args[0]);
		}
        
        public function testWithCustomConfigUsingAlphaPattern()
		{
            $r = new Router();
            $r->add('/foo/(:alpha)', 'fooController@fooMethod')
              ->setRouteUri('/foo/baz')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('fooController', $r->getController());
			$this->assertSame('fooMethod', $r->getMethod());
			$this->assertSame(1, count($r->getArgs()));
            $args = $r->getArgs();
            $this->assertSame('baz', $args[0]);
		}
        
        public function testWithCustomConfigUsingAlphaNumericPattern()
		{
            $r = new Router();
            $r->add('/foo/(:alnum)', 'fooController@fooMethod')
              ->setRouteUri('/foo/baz123')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('fooController', $r->getController());
			$this->assertSame('fooMethod', $r->getMethod());
			$this->assertSame(1, count($r->getArgs()));
            $args = $r->getArgs();
            $this->assertSame('baz123', $args[0]);
		}
        
        public function testWithCustomConfigUsingMultiplePattern()
		{
            $r = new Router();
            $r->add('/foo/(:alpha)/(:num)', 'fooController@fooMethod')
              ->setRouteUri('/foo/baz/123')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('fooController', $r->getController());
			$this->assertSame('fooMethod', $r->getMethod());
			$this->assertSame(2, count($r->getArgs()));
            $args = $r->getArgs();
            $this->assertSame('baz', $args[0]);
            $this->assertSame('123', $args[1]);
		}

	}
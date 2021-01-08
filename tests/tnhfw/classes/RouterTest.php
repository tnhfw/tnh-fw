<?php 

	/**
     * Router class tests
     *
     * @group core
     * @group core_classes
     */
	class RouterTest extends TnhTestCase {	
    
        //the Instance of module to use
        private $module = null;
        
        public function setUp(){
            parent::setUp();
            $this->module = new Module();
        }
        
        
        
        public function testConstructor() {
            $r = new Router($this->module);
            $rPattern = $this->getPrivateProtectedAttribute('Router', 'pattern');
            $rCallback = $this->getPrivateProtectedAttribute('Router', 'callback');
            $this->assertNotEmpty($rPattern->getValue($r));
            $this->assertNotEmpty($rCallback->getValue($r));
		}
        
        public function testModuleInstance() {
            $r = new Router($this->module);            
            $this->assertNotEmpty($r->getModuleInstance());
            $this->assertInstanceOf('Module', $r->getModuleInstance());
		}
		
        
        
		public function testAutoUri() {
            //when application run in CLI the first argument will be used as route URI
            $_SERVER['argv'][1] = '';
            $r = new Router($this->module);
            //remove all all config
            $r->setRouteConfiguration(array(), false)
              ->setRouteUri()
              ->setRouteSegments()
              ->determineRouteParamsInformation();

            $this->assertNull($r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testCustomUri() {
            $r = new Router($this->module);
            $r->setRouteUri('/users/profile/34/54')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('users', $r->getController());
			$this->assertSame('profile', $r->getMethod());
			$this->assertSame(2, count($r->getArgs()));
		}
        
        public function testCustomUriUsingRequestUri() {
            $_SERVER['REQUEST_URI'] = '/server/request/uri';
            $r = new Router($this->module);
            $r->setRouteUri()
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('server', $r->getController());
			$this->assertSame('request', $r->getMethod());
			$this->assertSame(1, count($r->getArgs()));
		}
        
        public function testCustomUriUsingModule() {
            $_SERVER['REQUEST_URI'] = '/testmodule';
            $r = new Router($this->module);
            $r->setRouteUri()
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('testmodule', $r->getModule());
            $this->assertSame('testmodule', $r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testCustomUriUsingModuleAndController() {
            $_SERVER['REQUEST_URI'] = '/testmodule/TestModuleController';
            $r = new Router($this->module);
            $r->setRouteUri()
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('testmodule', $r->getModule());
            $this->assertSame('TestModuleController', $r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testCustomUriUsingModuleAndControllerAndMethod() {
            $_SERVER['REQUEST_URI'] = '/testmodule/TestModuleController/test';
            $r = new Router($this->module);
            $r->setRouteUri()
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('testmodule', $r->getModule());
            $this->assertSame('TestModuleController', $r->getController());
			$this->assertSame('test', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        
        public function testFindControllerFullPathUsingCurrentModuleControllerNotExist() {
            $_SERVER['REQUEST_URI'] = '/testmodule/foo/89';
            $r = new Router($this->module);
            
            $r->setRouteUri()
               ->setRouteSegments()
              ->determineRouteParamsInformation();
          
            $this->assertSame('testmodule', $r->getModule());
            $this->assertSame('testmodule', $r->getController());
			$this->assertSame('foo', $r->getMethod());
			$this->assertSame(1, count($r->getArgs()));
		}
        
        public function testSetRouteParamsUsingPredefinedConfigControllerIsNotSet() {
            $r = new Router($this->module);
            
            $r->add('/foo', 'testmodule')
              ->setRouteUri('/foo')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
          
            $this->assertSame('testmodule', $r->getModule());
            $this->assertSame('testmodule', $r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testSetHttpMethodMatch() {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $r = new Router($this->module);
            
            $r->add('/foo', array('POST' => 'testmodule'))
              ->setRouteUri('/foo')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
          
            $this->assertSame('testmodule', $r->getModule());
            $this->assertSame('testmodule', $r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testSetHttpMethodNotMatch() {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $r = new Router($this->module);
            
            $r->add('/foo', array('POST' => 'testmodule'))
              ->setRouteUri('/foo')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
          
            $this->assertNull($r->getModule());
            $this->assertNull($r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        
        
        public function testDetermineRouteParamsFromRequestUriWhenNoModule() {
            $_SERVER['REQUEST_URI'] = '/TestController';
            $this->module->removeAll();
            $r = new Router($this->module);
            $r->setRouteUri()
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertNull($r->getModule());
            $this->assertSame('TestController', $r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testProcessRequest() {
            $_SERVER['REQUEST_URI'] = '/TestController/foo';
           
            $r = new Router($this->module);
            $r->processRequest();
            $this->assertNull($r->getModule());
            $this->assertSame('TestController', $r->getController());
			$this->assertSame('foo', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testProcessRequestControllerClassExistsButMethodNot() {
            $_SERVER['REQUEST_URI'] = '/TestController/foobar';
           
            $r = new Router($this->module);
            $r->processRequest();
            $this->assertNull($r->getModule());
            $this->assertSame('TestController', $r->getController());
			$this->assertSame('foobar', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testProcessRequestControllerNotExist() {
            $_SERVER['REQUEST_URI'] = '/TestControllers/foobar';
           
            $r = new Router($this->module);
            $r->processRequest();
            $this->assertNull($r->getModule());
            $this->assertSame('TestControllers', $r->getController());
			$this->assertSame('foobar', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
            $this->assertTrue($r->is404());
		}
        
        public function testProcessRequestControllerFileExistButClassNameNotSame() {
            $_SERVER['REQUEST_URI'] = '/ClassDiffFileNameController';
           
            $r = new Router($this->module);
            $r->processRequest();
            $this->assertNull($r->getModule());
            $this->assertSame('ClassDiffFileNameController', $r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
            $this->assertTrue($r->is404());
		}
        
        public function testCustomSegments() {
            $r = new Router($this->module);
            $r->setRouteSegments(array('bar', 'foo','args'))
              ->determineRouteParamsInformation();
            $this->assertSame('bar', $r->getController());
			$this->assertSame('foo', $r->getMethod());
			$this->assertSame(1, count($r->getArgs()));
		}
        
        public function testGetRouteConfiguration() {
            $r = new Router($this->module);
            $r->removeAllRoute();
            $r->setRouteConfiguration(array('/bar' => 'TestController'), false)
              ->setRouteUri('/bar')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
              
            $this->assertNotEmpty($r->getRouteConfiguration());
            $this->assertSame('TestController', $r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testWithCustomConfigControllerMethod() {
            $r = new Router($this->module);
            $r->add('/foo/bar', 'fooController@fooMethod')
              ->setRouteUri('/foo/bar')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('fooController', $r->getController());
			$this->assertSame('fooMethod', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
			$this->assertNull($r->getModule());
		}
        
        public function testGetControllerPath() {
            $r = new Router($this->module);
            $r->add('/foo/bar', 'TestController')
              ->setRouteUri('/foo/bar')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('TestController', $r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
			$this->assertSame(APPS_CONTROLLER_PATH . 'TestController.php', $r->getControllerPath());
            
            $this->assertNull($r->getModule());
		}
        
        
        public function testRemoveDocumentRootFrontControllerFromSegments() {
            //NOTE: currently the value of constant SELF is "bootstrap.php"
            //because this the first file executed
            //hack of server variable
            $_SERVER['SCRIPT_FILENAME'] = TESTS_PATH . 'bootstrap.php';
            $_SERVER['SCRIPT_NAME'] = '/app/bootstrap.php';
            $this->config->set('base_url', 'http://localhost/app/');
            $r = new Router($this->module);
            $r->add('/foo/bar', 'TestController')
              ->setRouteUri('/app/bootstrap.php/foo/bar')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('TestController', $r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        
        public function testRemoveSuffixAndQueryStringFromUri() {
            $this->config->set('url_suffix', '.html');
            $r = new Router($this->module);
            $r->add('/foo/bar', 'TestController')
              ->setRouteUri('/foo/bar.html?a=b&b=c')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('TestController', $r->getController());
			$this->assertSame('index', $r->getMethod());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        
        public function testSetControllerFilePathWhenParamNotNull() {
            $r = new Router($this->module);
            $this->runPrivateProtectedMethod($r, 'setControllerFilePath', array(APPS_CONTROLLER_PATH . 'TestController.php'));
			$this->assertSame(APPS_CONTROLLER_PATH . 'TestController.php', $r->getControllerPath());
		}
        
        public function testSetControllerFilePathUsingModule() {
            $r = new Router($this->module);
            $r->add('/foo/bar', 'testmodule#TestModuleController')
              ->setRouteUri('/foo/bar')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame(MODULE_PATH . 'testmodule' . DS . 'controllers' . DS . 'TestModuleController.php', $r->getControllerPath());
		}
        
        public function testGetSegments() {
            $r = new Router($this->module);
            $r->add('/foo/bar', 'TestController')
              ->setRouteUri('/foo/bar')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame(2, count($r->getSegments()));
		}
        
        public function testAddDuplicate() {
            $this->vfsRoot = vfsStream::setup();
            $this->vfsLogPath = vfsStream::newDirectory('logs')->at($this->vfsRoot);
            $this->config->set('log_save_path', $this->vfsLogPath->url() . '/');
            $this->config->set('log_level', 'WARNING');
            $log = new Log();
            
            $r = new Router($this->module);
            $rPattern = $this->getPrivateProtectedAttribute('Router', 'pattern');
            $r->setLogger($log);
             //First remove all route to prevent duplication
            $r->removeAllRoute();
            $this->assertSame(0, count($rPattern->getValue($r)));
            $r->add('/foo/bar', 'TestController');
            $this->assertSame(1, count($rPattern->getValue($r)));
            $r->add('/foo/bar', 'FooController');
            
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('already added, may be adding again can have route conflict', $content);
		}
        
        public function testRemoveRoute() {
            
            $r = new Router($this->module);
            $rPattern = $this->getPrivateProtectedAttribute('Router', 'pattern');
            //First remove all route to prevent duplication
            $r->removeAllRoute();
            
            $this->assertSame(0, count($rPattern->getValue($r)));
            $r->add('/foo/bar', 'TestController');
            $this->assertSame(1, count($rPattern->getValue($r)));
            $r->add('/bar/foo', 'FooController');
            $this->assertSame(2, count($rPattern->getValue($r)));
            $r->removeRoute('/foo/bar');
            $this->assertSame(1, count($rPattern->getValue($r)));
            $r->add('/', 'HomeController');
            $this->assertSame(2, count($rPattern->getValue($r)));
            $r->removeRoute('/');
            $this->assertSame(1, count($rPattern->getValue($r)));
             
		}
        
         public function testGetRouteUri() {
            $r = new Router($this->module);
            $r->setRouteUri('/foo/bar');
            $this->assertSame('foo/bar', $r->getRouteUri());
		}
        
        public function testWithCustomConfigModuleControllerMethod() {
            $r = new Router($this->module);
            $r->add('/foo/bar', 'fooModule#fooController@fooMethod')
              ->setRouteUri('/foo/bar')
              ->setRouteSegments()
              ->determineRouteParamsInformation();
            $this->assertSame('fooController', $r->getController());
			$this->assertSame('fooMethod', $r->getMethod());
			$this->assertSame('fooModule', $r->getModule());
			$this->assertSame(0, count($r->getArgs()));
		}
        
        public function testWithCustomConfigUsingAnyPattern() {
            $r = new Router($this->module);
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
        
         public function testWithCustomConfigUsingNumericPattern() {
            $r = new Router($this->module);
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
        
        public function testWithCustomConfigUsingAlphaPattern() {
            $r = new Router($this->module);
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
        
        public function testWithCustomConfigUsingAlphaNumericPattern() {
            $r = new Router($this->module);
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
        
        public function testWithCustomConfigUsingMultiplePattern() {
            $r = new Router($this->module);
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
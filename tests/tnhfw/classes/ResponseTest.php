<?php 

	/**
     * Response class tests
     *
     * @group core
     * @group core_classes
     * @group http
     */
     class ResponseTest extends TnhTestCase {	
    
        protected $currentUrl = null;
        protected $currentUrlCacheKey = null;
        protected $canCompressOutput = null;
        
        
        public function __construct(){
            parent::__construct();
            
            $this->currentUrl = new ReflectionProperty('Response', 'currentUrl');
            $this->currentUrl->setAccessible(true);
            
            $this->currentUrlCacheKey = new ReflectionProperty('Response', 'currentUrlCacheKey');
            $this->currentUrlCacheKey->setAccessible(true);
            
            $this->canCompressOutput = new ReflectionProperty('Response', 'canCompressOutput');
            $this->canCompressOutput->setAccessible(true);
        }
        
        public static function setUpBeforeClass() {
            //Used in ResponseTest::testRenderFinalPageWhenEventListenerReturnEmptyContent()
            require_once TESTS_PATH . 'include/listeners_event_dispatcher_test.php';
        }
       
		
        public function testConstructor() {
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $r = new Response();
            $this->assertSame('/foo/bar?a=b&b=c', $this->currentUrl->getValue($r));
			$this->assertSame(md5('/foo/bar?a=b&b=c'), $this->currentUrlCacheKey->getValue($r));
			$this->assertFalse($this->canCompressOutput->getValue($r));
		}
        
        public function testSendHeaders() {
            $r = new Response();
            $r->setHeader('foo', 'bar');
            $r->sendHeaders();
			$this->assertSame('bar', $r->getHeader('foo'));
		}
        
        public function testGetStatusDefault() {
            $r = new Response();
            $this->assertEquals(200, $r->getStatus());
		}
        
        public function testSetStatus() {
            $r = new Response();
            $r->setStatus(345);
            $this->assertEquals(345, $r->getStatus());
		}
        
        public function testGetHeaders() {
            $r = new Response();
            $r->setHeader('foo', 'bar');
            $this->assertSame('bar', $r->getHeader('foo'));
            $this->assertNotEmpty($r->getHeaders());
		}
        
        public function testGetHeaderKeyNotExists() {
            $r = new Response();
            $this->assertNull($r->getHeader('unknow'));
     	}
        
        public function testGetHeaderKeyExists() {
            $r = new Response();
            $r->setHeader('foo', 'bar');
            $this->assertSame('bar', $r->getHeader('foo'));
     	}
        
        public function testRender() {
            $r = new Response();
            $r->render('testview');
            $this->assertNotEmpty($r->getOutput());
            $this->assertSame('foo', $r->getOutput());
     	}
        
        public function testRenderModule() {
            $r = new Response();
            $r->render('testmodule/module_view');
            $this->assertNotEmpty($r->getOutput());
            $this->assertSame('foo_module', $r->getOutput());
     	}
        
        public function testRenderUsingCurrentControllerModule() {
            $obj = & get_instance();
            $obj->moduleName= 'testmodule';
            $r = new Response();
            $r->render('module_view');
            $this->assertNotEmpty($r->getOutput());
            $this->assertSame('foo_module', $r->getOutput());
     	}
        
        public function testRenderReturnedContent() {
            $r = new Response();
            $content = $r->render('testview', array(), true);
            $this->assertEmpty($r->getOutput());
            $this->assertSame('foo', $content);
     	}
        
        public function testRenderViewNotFound() {
            $r = new Response();
            $r->render('unkownview');
            $this->assertEmpty($r->getOutput());
     	}
        
        public function testRenderFinalPage() {
            $obj = &get_instance();
            $obj->benchmark = $this->getMockBuilder('Benchmark')->getMock();
            $r = new Response();
            $r->render('testview');
            $this->assertNotEmpty($r->getOutput());
            $this->assertSame('foo', $r->getOutput());
            $r->renderFinalPage();
            $this->assertSame('foo', $r->getOutput());
     	}
        
        public function testsetOutput() {
            $r = new Response();
            $r->render('testview');
            $this->assertNotEmpty($r->getOutput());
            $r->setOutput(null);
            $this->assertNull($r->getOutput());
            $r->setOutput('bar');
            $this->assertSame('bar', $r->getOutput());
     	}
        
        public function testRenderFinalPageWhenContentIsEmpty() {
            $r = new Response();
            $this->assertEmpty($r->getOutput());
            $r->renderFinalPage();
            $this->assertEmpty($r->getOutput());
     	}
        
        
        public function testRenderFinalPageWhenEventListenerReturnEmptyContent() {
            $listener = new ListenersEventDispatcherTest();
            $obj = &get_instance();
            $obj->eventdispatcher->addListener('FINAL_VIEW_READY', array($listener, 'responseTestListener'));
            $r = new Response();
            $r->render('testview');
            $this->assertNotEmpty($r->getOutput());
            $this->assertSame('foo', $r->getOutput());
            $r->renderFinalPage();
            $this->assertEmpty($r->getOutput());
            $obj->eventdispatcher->removeListener('FINAL_VIEW_READY', array($listener, 'responseTestListener'));
     	}
        
        public function testRenderFinalPageWhenCacheStatusIsEnabled() {
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
            $cache = new FileCache($this->vfsFileCachePath->url() . DS);
            $cacheKey = md5('/foo/bar?a=b&b=c');
            $rCompressCacheData = $this->getPrivateProtectedAttribute('FileCache', 'compressCacheData');
            $rCompressCacheData->setValue($cache, false);
            $cache->set($cacheKey, 'cacheview', 3000);
    
            
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            
            $benchmark = $this->getMockBuilder('Benchmark')->getMock();
            $this->config->set('cache_enable', true);
            $this->config->set('cache_handler', 'FileCache');
            $obj = &get_instance();
            $obj->cache = $cache;
            $obj->benchmark = $benchmark;
            
            $obj->view_cache_enable = true;
            $obj->view_cache_ttl = 300;
            $r = new Response();
            $r->render('testview');
            $this->assertNotEmpty($r->getOutput());
            $r->renderFinalPage();
            $this->assertNotEmpty($r->getOutput());
     	}
        
        public function testRenderFinalPageWhenCompressionIsAvailable() {
            $this->config->set('compress_output', true);
            $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
            
           $r = new Response();
           $this->assertTrue($this->canCompressOutput->getValue($r)); 
           $r->render('testview');
           $this->assertNotEmpty($r->getOutput());
           $r->renderFinalPage();
           $this->assertNotEmpty($r->getOutput());
     	}
        
        public function testRenderFinalPageFromCache() {
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
            $cache = new FileCache($this->vfsFileCachePath->url() . DS);
            $cacheKey = md5('/foo/bar?a=b&b=c');
            $rCompressCacheData = $this->getPrivateProtectedAttribute('FileCache', 'compressCacheData');
            $rCompressCacheData->setValue($cache, false);
            $cache->set($cacheKey, 'cacheview', 3000);
    
            
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $r = new Response();
            $this->assertTrue($r->renderFinalPageFromCache($cache));
     	}
        
        public function testRenderFinalPageFromCacheIsExpired() {
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
            $cache = new FileCache($this->vfsFileCachePath->url() . DS);
            $cacheKey = md5('/foo/bar?a=b&b=c');
            $rCompressCacheData = $this->getPrivateProtectedAttribute('FileCache', 'compressCacheData');
            $rCompressCacheData->setValue($cache, false);
            $cache->set($cacheKey, 'cacheview', -99999999);
    
            
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $r = new Response();
            $this->assertFalse($r->renderFinalPageFromCache($cache));
     	}
        
        public function testRenderFinalPageFromCacheIsNotExpired() {
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
            $cache = new FileCache($this->vfsFileCachePath->url() . DS);
            $cacheKey = md5('/foo/bar?a=b&b=c');
            $rCompressCacheData = $this->getPrivateProtectedAttribute('FileCache', 'compressCacheData');
            $rCompressCacheData->setValue($cache, false);
            $cache->set($cacheKey, 'cacheview', 3000);
    
            $_SERVER['HTTP_IF_MODIFIED_SINCE'] = date('Y-m-d', time() + 99999);
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $r = new Response();
            $this->assertTrue($r->renderFinalPageFromCache($cache));
     	}
        
        public function testRenderFinalPageFromCacheIsNotExpiredAndCompressionIsAvailable() {
            $this->config->set('compress_output', true);
            $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
            
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
            $cache = new FileCache($this->vfsFileCachePath->url() . DS);
            $cacheKey = md5('/foo/bar?a=b&b=c');
            $rCompressCacheData = $this->getPrivateProtectedAttribute('FileCache', 'compressCacheData');
            $rCompressCacheData->setValue($cache, false);
            $cache->set($cacheKey, 'cacheview', 8888);
    
            
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $r = new Response();
            $this->assertTrue($r->renderFinalPageFromCache($cache));
     	}
        
        
        public function testRenderFinalPageFromCacheDataIsNotValidOrExpired() {
            $obj = &get_instance();
            $obj->benchmark = $this->getMockBuilder('Benchmark')->getMock();
            
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
            $cache = new FileCache($this->vfsFileCachePath->url() . DS);
            $cacheKey = md5('/foo/bar?a=b&b=c');
            $rCompressCacheData = $this->getPrivateProtectedAttribute('FileCache', 'compressCacheData');
            $rCompressCacheData->setValue($cache, false);
            $cache->set($cacheKey, 'cacheview', 100);
            
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            
            
            //Mock method "sendCacheNotYetExpireInfoToBrowser" to allow 
            //call method "sendCachePageContentToBrowser"  
            $r = $this->getMockBuilder('Response')
                              ->setMethods(array('sendCacheNotYetExpireInfoToBrowser'))
                              ->getMock();
            
             $r->expects($this->any())
                 ->method('sendCacheNotYetExpireInfoToBrowser')
                 ->will($this->returnValue(false));
            $this->assertTrue($r->renderFinalPageFromCache($cache));
     	}
        
        
        
        public function testSend404(){
            $r = new Response();
            $r->render('404');
            $this->assertNotEmpty($r->getOutput());
            $r->send404();
            $this->assertNotEmpty($r->getOutput());
        }
        
        public function testSend404WhenContentIsEmpty(){
            $r = new Response();
            $this->assertEmpty($r->getOutput());
            $r->send404();
            $this->assertEmpty($r->getOutput());
        }
        
        public function testSend404WhenCompressionIsAvailable(){
            $this->config->set('compress_output', true);
            $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
            
            $r = new Response();
            $r->render('404');
            $this->assertNotEmpty($r->getOutput());
            $r->send404();
            $this->assertNotEmpty($r->getOutput());
        }
        
        public function testSend404WhenCacheIsEnabled(){
            $cache = $this->getMockBuilder('FileCache')->getMock();
            $this->config->set('cache_enable', true);
            $this->config->set('cache_handler', 'FileCache');
            
            $obj = &get_instance();
            $this->runPrivateProtectedMethod($obj, 'setCacheIfEnabled', array());
			
            $obj->view_cache_enable = true;
            
            $r = new Response();
            $r->render('404');
            $this->assertNotEmpty($r->getOutput());
            $r->send404();
            $this->assertNotEmpty($r->getOutput());
        }
        
        public function testSendError(){
            $data['title'] = 'error title';
            $data['error'] = 'error message';
            $r = new Response();
            $r->sendError($data);
            $this->assertContains('error message', $r->getOutput());
        }
                
        public function testSendErrorWhenCompressionIsAvailable(){
            $this->config->set('compress_output', true);
            $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
            $data['title'] = 'error title';
            $data['error'] = 'error message';
            $r = new Response();
            $r->sendError($data);
            $this->assertContains('error message', $r->getOutput());
        }
        
	}

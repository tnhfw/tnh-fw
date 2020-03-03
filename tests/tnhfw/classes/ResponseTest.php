<?php 

	
	class ResponseTest extends TnhTestCase
	{	
        protected $currentUrl = null;
        protected $currentUrlCacheKey = null;
        protected $canCompressOutput = null;
        
        
		public function __construct(){
            parent::__construct();
            
            $this->currentUrl = new ReflectionProperty('Response', '_currentUrl');
            $this->currentUrl->setAccessible(true);
            
            $this->currentUrlCacheKey = new ReflectionProperty('Response', '_currentUrlCacheKey');
            $this->currentUrlCacheKey->setAccessible(true);
            
            $this->canCompressOutput = new ReflectionProperty('Response', '_canCompressOutput');
            $this->canCompressOutput->setAccessible(true);
        }
		
		public function testConstructor()
		{
            
        
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $r = new Response();
			$this->assertSame('/foo/bar?a=b&b=c', $this->currentUrl->getValue($r));
			$this->assertSame(md5('/foo/bar?a=b&b=c'), $this->currentUrlCacheKey->getValue($r));
			$this->assertFalse($this->canCompressOutput->getValue($r));
		}
        
        public function testSendHeaders()
		{
            $r = new Response();
            $r->setHeader('foo', 'bar');
            $r->sendHeaders();
			$this->assertSame('bar', $r->getHeader('foo'));
		}
        
        public function testGetHeaders()
		{
            $r = new Response();
            $r->setHeader('foo', 'bar');
            $this->assertSame('bar', $r->getHeader('foo'));
            $this->assertNotEmpty($r->getHeaders());
		}
        
        public function testGetHeaderKeyNotExists()
		{
            $r = new Response();
            $this->assertNull($r->getHeader('unknow'));
     	}
        
        public function testGetHeaderKeyExists()
		{
            $r = new Response();
            $r->setHeader('foo', 'bar');
            $this->assertSame('bar', $r->getHeader('foo'));
     	}
        
        public function testRender()
		{
            $r = new Response();
            $r->render('testview');
            $this->assertNotEmpty($r->getFinalPageRendered());
            $this->assertSame('foo', $r->getFinalPageRendered());
     	}
        
        public function testRenderModule()
		{
            $m = new Module();
            $m->init();
            $r = new Response();
            $r->render('testmodule/module_view');
            $this->assertNotEmpty($r->getFinalPageRendered());
            $this->assertSame('foo_module', $r->getFinalPageRendered());
     	}
        
        public function testRenderUsingCurrentControllerModule()
		{
            $obj = & get_instance();
            $obj->moduleName= 'testmodule';
            $m = new Module();
            $m->init();
            $r = new Response();
            $r->render('module_view');
            $this->assertNotEmpty($r->getFinalPageRendered());
            $this->assertSame('foo_module', $r->getFinalPageRendered());
     	}
        
        public function testRenderReturnedContent()
		{
            $r = new Response();
            $content = $r->render('testview', array(), true);
            $this->assertEmpty($r->getFinalPageRendered());
            $this->assertSame('foo', $content);
     	}
        
        public function testRenderViewNotFound()
		{
            $r = new Response();
            $r->render('unkownview');
            $this->assertEmpty($r->getFinalPageRendered());
     	}
        
        public function testRenderFinalPage()
		{
            $r = new Response();
            $r->render('testview');
            $this->assertNotEmpty($r->getFinalPageRendered());
            $this->assertSame('foo', $r->getFinalPageRendered());
            $r->renderFinalPage();
            $this->assertSame('foo', $r->getFinalPageRendered());
     	}
        
        public function testRenderFinalPageWhenContentIsempty()
		{
            $r = new Response();
            $this->assertEmpty($r->getFinalPageRendered());
            $r->renderFinalPage();
            $this->assertEmpty($r->getFinalPageRendered());
     	}
        
        
        public function testRenderFinalPageWhenEventListenerReturnEmptyContent()
		{
            $listener = function($e){
                $e->payload = null;
                return null;
            };
            $obj = &get_instance();
            $obj->eventdispatcher->addListener('FINAL_VIEW_READY', $listener);
            $r = new Response();
            $r->render('testview');
            $this->assertNotEmpty($r->getFinalPageRendered());
            $this->assertSame('foo', $r->getFinalPageRendered());
            $r->renderFinalPage();
            $this->assertEmpty($r->getFinalPageRendered());
            $obj->eventdispatcher->removeListener('FINAL_VIEW_READY', $listener);
     	}
        
        
        public function testRenderFinalPageWhenCacheStatusIsEnabled()
		{
            $cache = $this->getMockBuilder('FileCache')->getMock();
            $this->config->set('cache_enable', true);
            $this->config->set('cache_handler', 'FileCache');
            
            $obj = &get_instance();
            $this->runPrivateProtectedMethod($obj, 'setCacheFromParamOrConfig', array($cache));
			
            $obj->view_cache_enable = true;
            
                    
            $r = new Response();
            $r->render('testview');
            $this->assertNotEmpty($r->getFinalPageRendered());
            $r->renderFinalPage();
            $this->assertNotEmpty($r->getFinalPageRendered());
     	}
        
        public function testRenderFinalPageWhenCompressionIsAvailable()
		{
            $this->config->set('compress_output', true);
            $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
            
           $r = new Response();
           $this->assertTrue($this->canCompressOutput->getValue($r)); 
           $r->render('testview');
           $this->assertNotEmpty($r->getFinalPageRendered());
           $r->renderFinalPage();
           $this->assertNotEmpty($r->getFinalPageRendered());
     	}
        
        public function testRenderFinalPageFromCache()
		{
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
            $cache = new FileCache($this->vfsFileCachePath->url() . DS);
            $cacheKey = md5('/foo/bar?a=b&b=c');
            $cache->setCompressCacheData(false);
            $cache->set($cacheKey, 'cacheview', 3000);
    
            
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $r = new Response();
			
            $this->assertTrue($r->renderFinalPageFromCache($cache));
     	}
        
        public function testRenderFinalPageFromCacheIsExpired()
		{
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
            $cache = new FileCache($this->vfsFileCachePath->url() . DS);
            $cacheKey = md5('/foo/bar?a=b&b=c');
            $cache->setCompressCacheData(false);
            $cache->set($cacheKey, 'cacheview', -99999999);
    
            
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $r = new Response();
			
            $this->assertFalse($r->renderFinalPageFromCache($cache));
     	}
        
        public function testRenderFinalPageFromCacheIsNotExpired()
		{
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
            $cache = new FileCache($this->vfsFileCachePath->url() . DS);
            $cacheKey = md5('/foo/bar?a=b&b=c');
            $cache->setCompressCacheData(false);
            $cache->set($cacheKey, 'cacheview', 3000);
    
            $_SERVER['HTTP_IF_MODIFIED_SINCE'] = date('Y-m-d', time() + 99999);
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $r = new Response();
			
            $this->assertTrue($r->renderFinalPageFromCache($cache));
     	}
        
        public function testRenderFinalPageFromCacheIsNotExpiredAndCompressionIsAvailable()
		{
            $this->config->set('compress_output', true);
            $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
            
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
            $cache = new FileCache($this->vfsFileCachePath->url() . DS);
            $cacheKey = md5('/foo/bar?a=b&b=c');
            $cache->setCompressCacheData(false);
            $cache->set($cacheKey, 'cacheview', 8888);
    
            
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $r = new Response();
			
            $this->assertTrue($r->renderFinalPageFromCache($cache));
     	}
        
        public function testRenderFinalPageFromCacheDataIsNotValidOrExpired()
		{
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
            $cache = new FileCache($this->vfsFileCachePath->url() . DS);
            $cacheKey = md5('/foo/bar?a=b&b=c');
            $cache->setCompressCacheData(false);
            $cache->set($cacheKey, 'cacheview', 1);
    
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $r = new Response();
			
            $this->assertTrue($r->renderFinalPageFromCache($cache));
     	}
        
        public function testSend404(){
            $r = new Response();
            $r->render('404');
            $this->assertNotEmpty($r->getFinalPageRendered());
            $r->send404();
            $this->assertNotEmpty($r->getFinalPageRendered());
        }
        
        public function testSend404WhenContentIsEmpty(){
            $r = new Response();
            $this->assertEmpty($r->getFinalPageRendered());
            $r->send404();
            $this->assertEmpty($r->getFinalPageRendered());
        }
        
        public function testSend404WhenCompressionIsAvailable(){
            $this->config->set('compress_output', true);
            $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
            
            $r = new Response();
            $r->render('404');
            $this->assertNotEmpty($r->getFinalPageRendered());
            $r->send404();
            $this->assertNotEmpty($r->getFinalPageRendered());
        }
        
        public function testSendError(){
            $data['title'] = 'error title';
            $data['error'] = 'error message';
            $r = new Response();
            $r->render('errors', $data);
            $r->sendError();
        }
        
        public function testSendErrorWhenContentIsEmpty(){
            $data['title'] = 'error title';
            $data['error'] = 'error message';
            $r = new Response();
            $this->assertEmpty($r->getFinalPageRendered());
            $r->sendError();
        }
        
        public function testSendErrorWhenCompressionIsAvailable(){
            $this->config->set('compress_output', true);
            $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
            $data['title'] = 'error title';
            $data['error'] = 'error message';
            $r = new Response();
            $r->render('errors', $data);
            $this->assertNotEmpty($r->getFinalPageRendered());
            $r->sendError();
            $this->assertContains('error message', $r->getFinalPageRendered());
        }

	}
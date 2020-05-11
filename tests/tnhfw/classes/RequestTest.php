<?php 

	/**
     * Request class tests
     *
     * @group core
     * @group core_classes
     * @group http
     */
	class RequestTest extends TnhTestCase {	
		
       
		public function testGetMethod() {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $r = new Request();
			$this->assertSame('GET', $r->method());
            
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $r = new Request();
			$this->assertSame('POST', $r->method());
		}
        
        public function testGetRequestUri() {
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $r = new Request();
			$this->assertSame('/foo/bar', $r->requestUri());
		}
        
        public function testQuery() {
            $_REQUEST['foo'] = 'bar';
            $r = new Request();
			$this->assertSame('bar', $r->query('foo'));
		}
        
        public function testGet() {
            $_GET['foo'] = 'bar';
            $r = new Request();
			$this->assertSame('bar', $r->get('foo'));
		}
        
        public function testPost() {
            $_POST['foo'] = 'bar';
            $r = new Request();
			$this->assertSame('bar', $r->post('foo'));
		}
        
        public function testServer() {
            $_SERVER['foo'] = 'baz';
            $r = new Request();
			$this->assertSame('baz', $r->server('foo'));
		}
        
        public function testCookie() {
            $_COOKIE['foo'] = 'barbaz';
            $r = new Request();
			$this->assertSame('barbaz', $r->cookie('foo'));
		}
        
        public function testHeader() {
            $r = new Request();
            $r->setHeader('fooheader', 'foobar');
			$this->assertSame('foobar', $r->header('fooheader'));
            
            $r = new Request();
            $r->setHeader('foo', 'bar');
			$this->assertSame('bar', $r->header('foo'));
            
            $headers = array('bar' => 'foo');
            $r->setHeader($headers);
            $this->assertNotEmpty($r->header());
			$this->assertArrayHasKey('bar', $r->header());
			$this->assertContains('foo', $r->header('bar'));
		}
        
        public function testFile() {
            get_instance()->globalvar->setFiles('foofile', array('name' => 'bar'));
            $r = new Request();
			$this->assertNotEmpty($r->file('foofile'));
			$this->assertArrayHasKey('name', $r->file('foofile'));
			$this->assertContains('bar', $r->file('foofile'));
		}
        
        public function testGetWhenKeyIsNull() {
            $_GET['foo'] = 'bar';
            $_GET['bar'] = 'foo';
            $r = new Request();
            $this->assertSame(2, count($r->get(null)));
		}
	}
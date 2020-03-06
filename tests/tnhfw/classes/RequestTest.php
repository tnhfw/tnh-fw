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
		}
        
        public function testFile() {
            $r = new Request();
            $r->setFile('foofile', array('name' => 'bar'));
			$this->assertNotEmpty($r->file('foofile'));
			$this->assertArrayHasKey('name', $r->file('foofile'));
			$this->assertContains('bar', $r->file('foofile'));
		}
        
        public function testSession() {
            $session = new Session();
            $session->set('foo', 'bar');
            
            $r = new Request();
            $r->setSession($session);
            
            $this->assertNotEmpty($r->session('foo'));
			$this->assertSame('bar', $r->session('foo'));
		}
        
        public function testSetQuery() {
            $r = new Request();
            $r->setQuery('foo', 'bar');
			$this->assertSame('bar', $r->query('foo'));
		}
        
        public function testSetGet() {
            $r = new Request();
            $r->setGet('foo', 'bar');
			$this->assertSame('bar', $r->get('foo'));
		}
        
        public function testSetPost() {
            $r = new Request();
            $r->setPost('foo', 'bar');
			$this->assertSame('bar', $r->post('foo'));
		}
        
        public function testSetServer() {
            $r = new Request();
            $r->setServer('foo', 'bar');
			$this->assertSame('bar', $r->server('foo'));
		}
        
        public function testSetCookie() {
            $r = new Request();
            $r->setCookie('foo', 'bar');
			$this->assertSame('bar', $r->cookie('foo'));
		}
        
        public function testSetHeader() {
            $r = new Request();
            $r->setHeader('foo', 'bar');
			$this->assertSame('bar', $r->header('foo'));
		}
        
        public function testSetFile() {
            $r = new Request();
            $r->setFile('foo', 'bar');
			$this->assertSame('bar', $r->file('foo'));
		}
        
        public function testSetSession() {
            $r = new Request();
            $r->setSession(null);
			$this->assertNull($r->getSession());
		}
        
        public function testGetWhenKeyIsNull() {
            $_GET['foo'] = 'bar';
            $_GET['bar'] = 'foo';
            $r = new Request();
            $this->assertSame(2, count($r->get(null)));
		}
        
        public function testSetWhenKeyIsAnArray() {
            $a = array(
                'foo' => 'bar',
                'bar' => 'foo',
            );
            $r = new Request();
            $r->setGet($a);
            $this->assertSame(2, count($r->get(null)));
		}


	}
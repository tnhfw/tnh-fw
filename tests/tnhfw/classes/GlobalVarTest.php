<?php 

	/**
     * GlobalVar class tests
     *
     * @group core
     * @group core_classes
     * @group base
     */
	class GlobalVarTest extends TnhTestCase {	
        
        public function testRequest() {
            $_REQUEST['foo'] = 'bar';
            $g = new GlobalVar();
			$this->assertSame('bar', $g->request('foo'));
            
            $g = new GlobalVar();
            $g->setRequest('foo', 'bar');
			$this->assertSame('bar', $g->request('foo'));
            
            $g->removeRequest('foo');
            $this->assertEmpty($g->request('foo'));
		}
        
        public function testGet() {
            $_GET['foo'] = 'bar';
            $g = new GlobalVar();
			$this->assertSame('bar', $g->get('foo'));
            
            $g = new GlobalVar();
            $g->setGet('foo', 'bar');
			$this->assertSame('bar', $g->get('foo'));
            
            $g->removeGet('foo');
            $this->assertEmpty($g->get('foo'));
		}
        
        public function testPost() {
            $_POST['foo'] = 'bar';
            $g = new GlobalVar();
			$this->assertSame('bar', $g->post('foo'));
            
            $g = new GlobalVar();
            $g->setPost('foo', 'bar');
			$this->assertSame('bar', $g->post('foo'));
            
            $g->removePost('foo');
            $this->assertEmpty($g->post('foo'));
		}
        
        public function testServer() {
            $_SERVER['foo'] = 'baz';
            $g = new GlobalVar();
			$this->assertSame('baz', $g->server('foo'));
            
            $g = new GlobalVar();
            $g->setServer('foo', 'bar');
			$this->assertSame('bar', $g->server('foo'));
            
            $g->removeServer('foo');
            $this->assertEmpty($g->server('foo'));
		}
        
        public function testCookie() {
            $_COOKIE['foo'] = 'barbaz';
            $g = new GlobalVar();
			$this->assertSame('barbaz', $g->cookie('foo'));
            
            $g = new GlobalVar();
            $g->setCookie('foo', 'bar');
			$this->assertSame('bar', $g->cookie('foo'));
            
            $g->removeCookie('foo');
            $this->assertEmpty($g->cookie('foo'));
		}
        
        public function testEnv() {
            $_ENV['foo'] = 'barbaz';
            $g = new GlobalVar();
			$this->assertSame('barbaz', $g->env('foo'));
            
            $g = new GlobalVar();
            $g->setEnv('foo', 'bar');
			$this->assertSame('bar', $g->env('foo'));
            
            $g->removeEnv('foo');
            $this->assertEmpty($g->env('foo'));
		}
        
        public function testGlobals() {
            $GLOBALS['foo'] = 'barbaz';
            $g = new GlobalVar();
			$this->assertSame('barbaz', $g->globals('foo'));
            
            $g = new GlobalVar();
            $g->setGlobals('foo', 'bar');
			$this->assertSame('bar', $g->globals('foo'));
            
            $g->removeGlobals('foo');
            $this->assertEmpty($g->globals('foo'));
		}
        
        
        public function testFile() {
            $g = new GlobalVar();
            $g->setFiles('foofile', array('name' => 'bar'));
			$this->assertNotEmpty($g->files('foofile'));
			$this->assertArrayHasKey('name', $g->files('foofile'));
			$this->assertContains('bar', $g->files('foofile'));
            
            $g->removeFiles('foo');
            $this->assertEmpty($g->files('foo'));
		}
        
        public function testSession() {
            $g = new GlobalVar();
            $g->setSession('foosession', array('name' => 'bar'));
			$this->assertNotEmpty($g->session('foosession'));
			$this->assertArrayHasKey('name', $g->session('foosession'));
			$this->assertContains('bar', $g->session('foosession'));
            
            $g->removeSession('foo');
            $this->assertEmpty($g->session('foo'));
		}
        
        
        public function testGetWhenKeyIsNull() {
            $_GET['foo'] = 'bar';
            $_GET['bar'] = 'foo';
            $g = new GlobalVar();
            $this->assertSame(2, count($g->get(null)));
		}
        
        public function testSetWhenKeyIsAnArray() {
            $a = array(
                'foo' => 'bar',
                'bar' => 'foo',
            );
            $g = new GlobalVar();
            $g->setGet($a);
            $this->assertSame(2, count($g->get(null)));
		}
        
         public function testRemoveWhenKeyNotExists() {
            $_GET['foo'] = 'bar';
            $_GET['bar'] = 'foo';
            $g = new GlobalVar();
            $this->assertSame(2, count($g->get(null)));
            $this->assertFalse($g->removeGet('baz'));
            $this->assertSame(2, count($g->get(null)));
		}


	}
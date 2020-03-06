<?php 

	/**
     * Lang class tests
     *
     * @group core
     * @group core_classes
     */
	class LangTest extends TnhTestCase {	
		
		public function testGetAll() {
            $l = new Lang();
			$this->assertEmpty($l->getAll());
            $l->set('name', 'Your name');
            $this->assertNotEmpty($l->getAll());
            $this->assertSame(1, count($l->getAll()));
		}
        
        public function testSet() {
            $l = new Lang();
			$this->assertEmpty($l->getAll());
            $l->set('name', 'Your name');
            $this->assertNotEmpty($l->getAll());
            $this->assertSame('Your name', $l->get('name'));
            $this->assertSame(1, count($l->getAll()));
		}
        
        public function testGet() {
            $l = new Lang();
			$this->assertSame('LANGUAGE_ERROR', $l->get('unknowKey'));
            $this->assertSame('default', $l->get('unknowKey', 'default'));
            $l->set('foo', 'bar');
            $this->assertSame('bar', $l->get('foo'));
		}
        
        public function testIsValid() {
            $l = new Lang();
			$this->assertFalse($l->isValid('unknow'));
			$this->assertTrue($l->isValid('en'));
		}
        
        public function testAddLang() {
            $l = new Lang();
            $l->addLang('foo', 'Foo lang');
            $this->assertEmpty($l->getSupported());
            $this->assertArrayNotHasKey('foo', $l->getSupported());
            $l->addLang('en', 'Foo lang');
            $this->assertSame('en', $l->getCurrent());
            $this->assertArrayHasKey('en', $l->getSupported());
            $this->assertNotEmpty($l->getSupported());
            //already exist (nothing to do)
            $l->addLang('en', 'Bar lang');
            $this->assertArrayHasKey('en', $l->getSupported());
            $this->assertContains('Foo lang', $l->getSupported());
		}
        
        public function testAddLangMessages() {
            $l = new Lang();
            $this->assertEmpty($l->getAll());
            $l->addLangMessages(array(
                                        'name' => 'Your name',
                                        'foo' => 'bar',
                                        'bar' => 'foo'
                                 ));
            $this->assertNotEmpty($l->getAll());
            $this->assertSame('Your name', $l->get('name'));
            $this->assertSame('foo', $l->get('bar'));
            $this->assertSame('bar', $l->get('foo'));
            $this->assertSame(3, count($l->getAll()));
		}
        
        public function testAppLangUsingCookieValue() {
            $_COOKIE['clang'] = 'en';
            
            $this->config->set('language_cookie_name', 'clang');
            $this->config->set('default_language', 'fr');
            $l = new Lang();
            $this->assertSame('en', $l->getCurrent());
		}
        
	}
<?php 

	/**
     * Languages functions tests
     *
     * @group core
     * @group functions
     */
	class FunctionLangTest extends TnhTestCase {	
	
        public static function setUpBeforeClass() {
            require_once CORE_FUNCTIONS_PATH . 'function_lang.php';
		}
		
		public function testGet() {
            $l = new Lang();
			$this->assertEmpty($l->getAll());
            $l->set('name', 'Your name');
            $this->assertNotEmpty($l->getAll());
            $this->assertSame(1, count($l->getAll()));
            $this->assertSame('LANGUAGE_ERROR', __tr('unknowKey'));
			$this->assertSame('default', __tr('unknowKey', 'default'));
            $obj = & get_instance();
            $obj->lang = $l;
            $this->assertSame('Your name', __tr('name'));
		}
        
        public function testGetLanguages() {
            $l = new Lang();
            $obj = & get_instance();
            $obj->lang = $l;
            
            $l->addLang('foo', 'Foo lang');
            $this->assertEmpty(get_languages());
            $this->assertArrayNotHasKey('foo', get_languages());
            $l->addLang('en', 'Foo lang');
            $this->assertArrayHasKey('en', get_languages());
            $this->assertNotEmpty(get_languages());
		}

	}
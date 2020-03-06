<?php 

	/**
     * Assets library class tests
     *
     * @group core
     * @group libraries
     * @group html
     */
	class AssetsTest extends TnhTestCase {	
		
		public function testPathFileExists() {
            $a = new Assets();
            
            $expected = ASSETS_PATH . 'examplepath/example.css';
			$this->assertSame($expected, $a->path('examplepath/example.css'));
            
            //with base_url set
            $base_url = 'http://localhost';
            $this->config->set('base_url', $base_url);
            
            $expected = $base_url . ASSETS_PATH . 'examplepath/example.css';
            $this->assertSame($expected, $a->path('examplepath/example.css'));
		}
        
        public function testPathFileNotExist() {
            $a = new Assets();
            $this->assertNull($a->path('foopath/foofile.ext'));
		}
        
        public function testCssFileExists() {
            $a = new Assets();
            
            $expected = ASSETS_PATH . 'css/test.css';
			$this->assertSame($expected, $a->css('test'));
            
            //with extension 
			$this->assertSame($expected, $a->css('test.css'));
            
            //with base_url set
            $base_url = 'http://localhost';
            $this->config->set('base_url', $base_url);
            
            $expected = $base_url . ASSETS_PATH . 'css/test.css';
            $this->assertSame($expected, $a->css('test.css'));
		}
        
        public function testCssFileNotExist() {
            $a = new Assets();
            $this->assertNull($a->css('foofile.css'));
            $this->assertNull($a->css('foofile'));
		}
        
        public function testJsFileExists() {
            $a = new Assets();
            
            $expected = ASSETS_PATH . 'js/test.js';
			$this->assertSame($expected, $a->js('test'));
            
            //with extension 
			$this->assertSame($expected, $a->js('test.js'));
            
            //with base_url set
            $base_url = 'http://localhost';
            $this->config->set('base_url', $base_url);
            
            $expected = $base_url . ASSETS_PATH . 'js/test.js';
            $this->assertSame($expected, $a->js('test.js'));
		}
        
        public function testJsFileNotExist() {
            $a = new Assets();
            $this->assertNull($a->js('foofile.js'));
            $this->assertNull($a->js('foofile'));
		}
        
        public function testImageFileExists() {
            $a = new Assets();
            
            $expected = ASSETS_PATH . 'images/logo.png';
			$this->assertSame($expected, $a->img('logo.png'));
            
            //with base_url set
            $base_url = 'http://localhost';
            $this->config->set('base_url', $base_url);
            
            $expected = $base_url . ASSETS_PATH . 'images/logo.png';
            $this->assertSame($expected, $a->img('logo.png'));
		}
        
        public function testImageFileNotExist() {
            $a = new Assets();
            $this->assertNull($a->img('foofile.jpg'));
		}


	}
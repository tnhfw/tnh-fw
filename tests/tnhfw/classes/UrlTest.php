<?php 

    /**
     * Url class tests
     *
     * @group core
     * @group core_classes
     * @group http
     */
	class UrlTest extends TnhTestCase {	
		public function testBaseUrl() {
            $url = new Url();
			$this->assertEmpty($url->base_url());
            
            $base_url = 'http://localhost/';
            $this->config->set('base_url', $base_url);
            $this->assertNotEmpty($url->base_url());
            $this->assertSame($base_url, $url->base_url());
            
            $this->assertSame($base_url . 'foo', $url->base_url('foo'));
		}
        
        public function testBaseUrlWhenParamIsAbsoluteUrl() {
            $url = new Url();
			$link = 'http://www.example.com';
            $this->assertSame($link, $url->base_url($link));
		}
        
        public function testSiteUrlWhenParamIsAbsoluteUrl() {
            $url = new Url();
			$link = 'http://www.example.com';
            $this->assertSame($link, $url->site_url($link));
		}
        
        public function testSiteUrlWhenFrontControllerConfigIsNotEmpty() {
            $url = new Url();
            $base_url = 'http://localhost/';
            $this->config->set('base_url', $base_url);
            $this->config->set('front_controller', 'foo.php');
            $expected = $base_url . 'foo.php/controller';
            
			$this->assertSame($expected, $url->site_url('controller'));
		}
        
        public function testSiteUrlWhenUrlSuffixIsSetInConfig() {
            $url = new Url();
            $base_url = 'http://localhost/';
            $this->config->set('base_url', $base_url);
            $this->config->set('url_suffix', '.html');
            $expected = $base_url . 'controller.html';
            
			$this->assertSame($expected, $url->site_url('controller'));
		}
        
        public function testSiteUrlWhenUrlSuffixIsSetInConfigAndRequestHasQueryStringValue() {
            $url = new Url();
            $base_url = 'http://localhost/';
            $this->config->set('base_url', $base_url);
            $this->config->set('url_suffix', '.html');
            $expected = $base_url . 'controller.html?a=b&b=c';
            
			$this->assertSame($expected, $url->site_url('controller?a=b&b=c'));
		}
        
        public function testGetCurrentUrl() {
            $url = new Url();
            //as Url::current() use internally Url::domain() and domain method have http://localhost as 
            //default value and Url::current() method have "/" as default value of request uri
            $expected = 'http://localhost/';
			$this->assertSame($expected, $url->current());
		}
        
        public function testGetCurrentUrlWhenHaveRequestUri() {
            $_SERVER['REQUEST_URI'] = '/foo/bar';
            $obj = & get_instance();
            $obj->request = new Request();
            $url = new Url();
            $expected = 'http://localhost/foo/bar';
            
			$this->assertSame($expected, $url->current());
		}
        
        public function testGetUrlTitle() {
            $url = new Url();
            
            $link = 'foo bar';
            $expected = 'foo-bar';
			$this->assertSame($expected, $url->title($link));
            
            //special char
            $link = 'foo bar/$57^*-1';
            $expected = 'foo-bar-57-1';
			$this->assertSame($expected, $url->title($link));
            
            //using another separator char
            $link = 'foo bar/$57^*-1';
            $expected = 'foo@bar@57@1';
			$this->assertSame($expected, $url->title($link, '@'));
            
            //using uppercase char
            $link = 'Foo Bar%u';
            $expected = 'Foo-Bar-u';
			$this->assertSame($expected, $url->title($link, '-', false));
            
            //When last char equal to separator
            $link = 'foo bar -';
            $expected = 'foo-bar';
			$this->assertSame($expected, $url->title($link));
		}
        
        public function testGetDomainUsingDefaultValue() {
            $url = new Url();
            $expected = 'http://localhost';
			$this->assertSame($expected, $url->domain());
		}
        
        public function testGetDomainUsingServerVarHttpHost() {
            $_SERVER['HTTP_HOST'] = 'foo.fr';
            $obj = & get_instance();
            $obj->request = new Request();
            
            $url = new Url();
            $expected = 'http://foo.fr';
			$this->assertSame($expected, $url->domain());
		}
        
        public function testGetDomainUsingServerVarServerName() {
            $_SERVER['SERVER_NAME'] = 'bar.cf';
            $obj = & get_instance();
            $obj->request = new Request();
            
            $url = new Url();
            $expected = 'http://bar.cf';
			$this->assertSame($expected, $url->domain());
		}
        
        public function testGetDomainUsingServerVarServerAddr() {
            $_SERVER['SERVER_ADDR'] = 'example.com';
            $obj = & get_instance();
            $obj->request = new Request();
            
            $url = new Url();
            $expected = 'http://example.com';
			$this->assertSame($expected, $url->domain());
		}
        
        public function testGetDomainHttps() {
            $_SERVER['HTTPS'] = 'on';
            $_SERVER['SERVER_ADDR'] = 'example.com';
            $obj = & get_instance();
            $obj->request = new Request();
            
            $url = new Url();
            $expected = 'https://example.com';
			$this->assertSame($expected, $url->domain());
		}
        
        public function testGetDomainHttpsWhenNotDefaultPort() {
            $_SERVER['HTTPS'] = 'on';
            $_SERVER['SERVER_ADDR'] = 'example.com';
            $_SERVER['SERVER_PORT'] = 1234;
            $obj = & get_instance();
            $obj->request = new Request();
            
            $url = new Url();
            $expected = 'https://example.com:1234';
			$this->assertSame($expected, $url->domain());
		}
        
        public function testGetQueryString() {
            $_SERVER['QUERY_STRING'] = 'a=b&b=c';
            $obj = & get_instance();
            $obj->request = new Request();
            
            $url = new Url();
            $expected = 'a=b&b=c';
			$this->assertSame($expected, $url->queryString());
		}

	}
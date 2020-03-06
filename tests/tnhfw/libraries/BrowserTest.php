<?php 

	/**
     * Browser library class tests
     *
     * @group core
     * @group libraries
     */
	class BrowserTest extends TnhTestCase {	
		
		public function testConstructorWithAutoDetectUserAgent() {
            $userAgentString = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36';
            $_SERVER['HTTP_USER_AGENT'] = $userAgentString;
            
            $b = new Browser();
            $this->assertSame($userAgentString, $b->getUserAgent());
		}
        
        public function testConstructorWithUserAgentInParam() {
            $userAgentString = 'Mozilla/5.0 (Windows NT 6.1; rv:40.0) Gecko/20100101 Firefox/40.0';
            $_SERVER['HTTP_USER_AGENT'] = $userAgentString;
            
            $b = new Browser($userAgentString);
            $this->assertSame($userAgentString, $b->getUserAgent());
		}
        
        public function testGetPlatform() {
            $userAgentString = 'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko';
            $expected = 'Windows 7';
            $b = new Browser($userAgentString);
            $this->assertSame($expected, $b->getPlatform());
            
            $userAgentString = 'Mozilla/5.0 (iPad; CPU OS 8_4_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12H321 Safari/600.1.4';
            $b->setUserAgent($userAgentString);
            $expected = 'iPad';
            $this->assertSame($expected, $b->getPlatform());
            
            $userAgentString = 'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 10 Build/LMY48I) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.84 Safari/537.36';
            $b->setUserAgent($userAgentString);
            $expected = 'Android';
            $this->assertSame($expected, $b->getPlatform());
		}
        
        public function testGetVersion() {
            $userAgentString = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.115 Safari/537.36';
            $expected = '40.0.2214.115';
            $b = new Browser($userAgentString);
            $this->assertSame($expected, $b->getVersion());
            
            $userAgentString = 'Mozilla/5.0 (iPad; CPU OS 8_4_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12H321 Safari/600.1.4';
            $b->setUserAgent($userAgentString);
            $expected = '8.0';
            $this->assertSame($expected, $b->getVersion());
            
            $userAgentString = 'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 10 Build/LMY48I) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.84 Safari/537.36';
            $b->setUserAgent($userAgentString);
            $expected = '45.0.2454.84';
            $this->assertSame($expected, $b->getVersion());
		}
        
        public function testIsMobile() {
            $userAgentString = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.115 Safari/537.36';
            $b = new Browser($userAgentString);
            $this->assertFalse($b->isMobile());
            
            //Tablet is also a mobile
            $userAgentString = 'Mozilla/5.0 (Android; Tablet; rv:34.0) Gecko/34.0 Firefox/34.0';
            $b->setUserAgent($userAgentString);
            $this->assertTrue($b->isMobile());
            $this->assertTrue($b->isTablet());
            
            $userAgentString = 'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 10 Build/LMY48I) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.84 Safari/537.36';
            $b->setUserAgent($userAgentString);
            $this->assertTrue($b->isMobile());
		}
        
        public function testIsRobot() {
            $userAgentString = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.115 Safari/537.36';
            $b = new Browser($userAgentString);
            $this->assertFalse($b->isRobot());
            
            //Tablet is also a mobile
            $userAgentString = 'Mozilla/5.0 (Android; Tablet; rv:34.0) Gecko/34.0 Firefox/34.0';
            $b->setUserAgent($userAgentString);
            $this->assertFalse($b->isRobot());
            
            $userAgentString = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
            $b->setUserAgent($userAgentString);
            $this->assertTrue($b->isRobot());
		}
        
        public function testIsFacebook() {
            $userAgentString = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.115 Safari/537.36';
            $b = new Browser($userAgentString);
            $this->assertFalse($b->isFacebook());
            
            //Tablet is also a mobile
            $userAgentString = 'Mozilla/5.0 (Android; Tablet; rv:34.0) Gecko/34.0 Firefox/34.0';
            $b->setUserAgent($userAgentString);
            $this->assertFalse($b->isFacebook());
            
            $userAgentString = 'Mozilla/5.0 (iPad; CPU OS 8_4_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12H321
            [FBAN/FBIOS;FBAV/38.0.0.6.79;FBBV/14316658;FBDV/
            iPad4,1;FBMD/iPad;FBSN/iPhone OS;FBSV/8.4.1;FBSS/2; FBCR/;FBID/tablet;FBLC/en_US;FBOP/1]';
            $b->setUserAgent($userAgentString);
            $this->assertTrue($b->isFacebook());
            
            $userAgentString = 'facebookexternalhit/1.0 (+http://www.facebook.com/externalhit_uatext.php)';
            $b->setUserAgent($userAgentString);
            $this->assertTrue($b->isFacebook());
		}

	}
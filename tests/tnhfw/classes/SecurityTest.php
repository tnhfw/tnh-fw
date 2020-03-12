<?php 

	/**
     * Security class tests
     *
     * @group core
     * @group core_classes
     * @group security
     */
	class SecurityTest extends TnhTestCase {	
	
		
		protected function setUp()
        {
            parent::setUp();
           
            $this->config->set('csrf_enable', true);
			$this->config->set('csrf_key', 'kcsrf');
			$this->config->set('csrf_expire', 100);
        }
        
		public function testGenerateCsrf() {
            $s = new Security();
            $csrf = $s->generateCSRF();
            $this->assertNotEmpty($csrf);
		}
        
        
        public function testGenerateCsrfWhenValueAlreadyExists() {
            $exists = uniqid();
            $_SESSION['kcsrf'] =  $exists;
            $_SESSION['csrf_expire'] = time() + 600;
            $s = new Security();
            $csrf = $s->generateCSRF();
            $this->assertNotEmpty($csrf);
            $this->assertSame($csrf, $exists);
		}
        
        public function testValidateCsrfSessionValuesNotExists() {
            $_SESSION = array();
            $s = new Security();
            $this->assertFalse($s->validateCSRF());
		}
        
        public function testValidateCsrfInvalidCsrfValue()
        {
            $correct = uniqid();
            $_SESSION['kcsrf'] =  $correct;
            $_SESSION['csrf_expire'] = time() + 600;
            $s = new Security();
            $csrf = $s->generateCSRF();
            $this->assertNotEmpty($csrf);
            
            $_POST['kcsrf'] = 'foo';
            $this->assertFalse($s->validateCSRF());
        }
        
        public function testValidateCsrf() {
            $correct = uniqid();
            $_POST['kcsrf'] = $correct;
            
            $obj = & get_instance();
            $obj->request = new Request();
            
            $_SESSION['kcsrf'] =  $correct;
            $_SESSION['csrf_expire'] = time() + 600;
            $s = new Security();
            $csrf = $s->generateCSRF();
            $this->assertNotEmpty($csrf);
            $this->assertSame($csrf, $correct);
            $this->assertTrue($s->validateCSRF());
		}
        
        
        public function testCheckWhiteListIpAccessWhenNotEnabledInConfig()
        {
            $s = new Security();
            $this->config->set('white_list_ip_enable', false);
            $this->assertTrue($s->checkWhiteListIpAccess());
        }
        
        public function testCheckWhiteListIpAccessWhenEnabledInConfigButWhitelistIsEmpty()
        {
            $s = new Security();
            $this->config->set('white_list_ip_enable', true);
            $this->config->set('white_list_ip_addresses', array());
            
            $this->assertTrue($s->checkWhiteListIpAccess());
        }
        
        public function testCheckWhiteListIpAccessUsingFullWildcard()
        {
            $s = new Security();
            $this->config->set('white_list_ip_enable', true);
            $this->config->set('white_list_ip_addresses', array('*'));
            
            $this->assertTrue($s->checkWhiteListIpAccess());
        }
        
         public function testCheckWhiteListIpAccessUsingFullIpAddress()
        {
            $s = new Security();
            //This one is used by helper "get_ip";
            $_SERVER['REMOTE_ADDR'] = '23.56.23.9';
            $this->config->set('white_list_ip_enable', true);
            $this->config->set('white_list_ip_addresses', array('23.56.23.9'));
            
            $this->assertTrue($s->checkWhiteListIpAccess());
        }
        
        
        public function testCheckWhiteListIpAccessUsingPartialWildcard()
        {
            $s = new Security();
            $_SERVER['REMOTE_ADDR'] = '23.56.23.9';
            $this->config->set('white_list_ip_enable', true);
            $this->config->set('white_list_ip_addresses', array('23.56.23.*'));
            
            $this->assertTrue($s->checkWhiteListIpAccess());
        }
        
        public function testCheckWhiteListIpAccessCannotAccess()
        {
            $s = new Security();
            $_SERVER['REMOTE_ADDR'] = '23.56.23.9';
            $this->config->set('white_list_ip_enable', true);
            $this->config->set('white_list_ip_addresses', array('23.56.23.1'));
            
            $this->assertFalse($s->checkWhiteListIpAccess());
        }
        

	}
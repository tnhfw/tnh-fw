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
            $csrf = Security::generateCSRF();
            $this->assertNotEmpty($csrf);
		}
        
        
        public function testGenerateCsrfWhenValueAlreadyExists() {
            $exists = uniqid();
            $_SESSION['kcsrf'] =  $exists;
            $_SESSION['csrf_expire'] = time() + 600;
           
            $csrf = Security::generateCSRF();
            $this->assertNotEmpty($csrf);
            $this->assertSame($csrf, $exists);
		}
        
        public function testValidateCsrfSessionValuesNotExists() {
            $_SESSION = array();
            $this->assertFalse(Security::validateCSRF());
		}
        
        public function testValidateCsrfInvalidCsrfValue()
        {
            $correct = uniqid();
            $_SESSION['kcsrf'] =  $correct;
            $_SESSION['csrf_expire'] = time() + 600;
           
            $csrf = Security::generateCSRF();
            $this->assertNotEmpty($csrf);
            
            $_POST['kcsrf'] = 'foo';
            $this->assertFalse(Security::validateCSRF());
        }
        
        public function testValidateCsrf() {
            $correct = uniqid();
            $_POST['kcsrf'] = $correct;
            
            $obj = & get_instance();
            $obj->request = new Request();
            
            $_SESSION['kcsrf'] =  $correct;
            $_SESSION['csrf_expire'] = time() + 600;
           
            $csrf = Security::generateCSRF();
            $this->assertNotEmpty($csrf);
            $this->assertSame($csrf, $correct);
            $this->assertTrue(Security::validateCSRF());
		}
        
        
        public function testCheckWhiteListIpAccessWhenNotEnabledInConfig()
        {
            $this->config->set('white_list_ip_enable', false);
            $this->assertTrue(Security::checkWhiteListIpAccess());
        }
        
        public function testCheckWhiteListIpAccessWhenEnabledInConfigButWhitelistIsEmpty()
        {
            $this->config->set('white_list_ip_enable', true);
            $this->config->set('white_list_ip_addresses', array());
            
            $this->assertTrue(Security::checkWhiteListIpAccess());
        }
        
        public function testCheckWhiteListIpAccessUsingFullWildcard()
        {
            $this->config->set('white_list_ip_enable', true);
            $this->config->set('white_list_ip_addresses', array('*'));
            
            $this->assertTrue(Security::checkWhiteListIpAccess());
        }
        
         public function testCheckWhiteListIpAccessUsingFullIpAddress()
        {
            //This one is used by helper "get_ip";
            $_SERVER['REMOTE_ADDR'] = '23.56.23.9';
            $this->config->set('white_list_ip_enable', true);
            $this->config->set('white_list_ip_addresses', array('23.56.23.9'));
            
            $this->assertTrue(Security::checkWhiteListIpAccess());
        }
        
        
        public function testCheckWhiteListIpAccessUsingPartialWildcard()
        {
            $_SERVER['REMOTE_ADDR'] = '23.56.23.9';
            $this->config->set('white_list_ip_enable', true);
            $this->config->set('white_list_ip_addresses', array('23.56.23.*'));
            
            $this->assertTrue(Security::checkWhiteListIpAccess());
        }
        
        public function testCheckWhiteListIpAccessCannotAccess()
        {
            $_SERVER['REMOTE_ADDR'] = '23.56.23.9';
            $this->config->set('white_list_ip_enable', true);
            $this->config->set('white_list_ip_addresses', array('23.56.23.1'));
            
            $this->assertFalse(Security::checkWhiteListIpAccess());
        }
        

	}
<?php 

	/**
     * Email library class tests
     *
     * @group core
     * @group libraries
     */
	class EmailTest extends TnhTestCase {	
		
		public function testConstructor() {
            $e = new Email(); 
			$this->assertInstanceOf('Email', $e);
		}
        
        public function testSetFrom() {
            $email = 'foo@bar.com';
            $name = null;
            
            $e = new Email(); 
            $this->assertEmpty($e->getHeaders());
            
            $e->setFrom($email, $name);
			$this->assertNotEmpty($e->getHeaders());
			$this->assertSame(1, count($e->getHeaders()));
            
            $name = 'Foo Bar';
            $e->setFrom($email, $name);
			$this->assertNotEmpty($e->getHeaders());
            $this->assertSame(1, count($e->getHeaders()));
		}
        
        public function testSetTo() {
            $email = 'foo@bar.com';
            $name = null;
            
            $e = new Email(); 
            $this->assertEmpty($e->getTo());
            
            $e->setTo($email, $name);
			$this->assertNotEmpty($e->getTo());
			$this->assertSame(1, count($e->getTo()));
            
            $name = 'Foo Bar';
            $e->setTo($email, $name);
			$this->assertSame(2, count($e->getTo()));
		}

        public function testSetTos() {
            $emails = array('foo@bar.com', 'baz' => 'baz@foo.com');
            
            $e = new Email(); 
            $this->assertEmpty($e->getTo());
            
            $e->setTos($emails);
			$this->assertNotEmpty($e->getTo());
			$this->assertSame(2, count($e->getTo()));
		}
        
        public function testSetGetCc() {
            $emails = array('foo'=> 'foo@bar.com', 'baz' => 'baz@foo.com');
            
            $e = new Email(); 
            $this->assertEmpty($e->getHeaders());
            
            $e->setCc($emails);
			$this->assertNotEmpty($e->getHeaders());
			$this->assertSame(1, count($e->getHeaders()));
			$this->assertSame(2, count($e->getCc()));
		}
        
        public function testSetGetBcc() {
            $emails = array('foo@bar.com', 'baz' => 'baz@foo.com');
            
            $e = new Email(); 
            $this->assertEmpty($e->getHeaders());
            
            $e->setBcc($emails);
			$this->assertNotEmpty($e->getHeaders());
			$this->assertSame(1, count($e->getHeaders()));
			$this->assertSame(2, count($e->getBcc()));
            
            //empty value
            $e->setBcc(array());
            $this->assertSame(1, count($e->getHeaders()));
            $this->assertSame(0, count($e->getBcc()));
		}
        
        public function testSetGetSmtpProtocol() {
           $e = new Email(); 
            //Default is mail
            $this->assertSame('mail', $e->getProtocol());
            
            $e->useMail();
            $this->assertSame('mail', $e->getProtocol());
            
            $e->useSmtp();
            $this->assertSame('smtp', $e->getProtocol());
		}
        
        public function testSetGetSmtpConfig() {
            $e = new Email(); 
            //Default configuration
            $this->assertSame('plain', $e->getSmtpConfig('transport'));
            $this->assertSame('localhost', $e->getSmtpConfig('hostname'));
            $this->assertSame(25, $e->getSmtpConfig('port'));
            $this->assertNull($e->getSmtpConfig('username'));
            $this->assertNull($e->getSmtpConfig('password'));
            $this->assertSame(30, $e->getSmtpConfig('connection_timeout'));
            $this->assertSame(10, $e->getSmtpConfig('response_timeout'));
            
            //Custom configuration
            $smtpConfig = array(
                'transport'          => 'tls',
                'hostname'           => 'my.smtpserver.com',
                'port'               => 2525,
                'username'           => 'foo',
                'password'           => 'bar',
                'connection_timeout' => 5,
                'response_timeout'   => 2
            );   
            $e->setSmtpConfig($smtpConfig);
            $this->assertNotEmpty($e->getSmtpConfigs());
            $this->assertSame(7, count($e->getSmtpConfigs()));
            
            $this->assertSame('tls', $e->getSmtpConfig('transport'));
            $this->assertSame('my.smtpserver.com', $e->getSmtpConfig('hostname'));
            $this->assertSame(2525, $e->getSmtpConfig('port'));
            $this->assertSame('foo', $e->getSmtpConfig('username'));
            $this->assertSame('bar', $e->getSmtpConfig('password'));
            $this->assertSame(5, $e->getSmtpConfig('connection_timeout'));
            $this->assertSame(2, $e->getSmtpConfig('response_timeout'));
            
            $this->assertNotEmpty($e->getSmtpConfigs());
            $this->assertSame(7, count($e->getSmtpConfigs()));
 		}
                
        public function testSetReplyTo() {
            $email = 'foo@bar.com';
            $name = null;
            
            $e = new Email(); 
            $this->assertEmpty($e->getTo());
            
            $e->setReplyTo($email, $name);
			$this->assertNotEmpty($e->getHeaders());
			$this->assertSame(1, count($e->getHeaders()));
            
            $name = 'Foo Bar';
            $e->setReplyTo($email, $name);
			$this->assertSame(1, count($e->getHeaders()));
		}
        
        public function testSetHtml() {
            $e = new Email(); 
            $this->assertEmpty($e->getHeaders());
            
            $e->setHtml();
			$this->assertNotEmpty($e->getHeaders());
			$this->assertSame(1, count($e->getHeaders()));
		}
        
        public function testSubject() {
            $e = new Email(); 
            $this->assertEmpty($e->getSubject());
            
            $e->setSubject('foo subject');
			$this->assertNotEmpty($e->getSubject());
		}
        
        public function testMessage() {
            $e = new Email(); 
            $this->assertEmpty($e->getMessage());
            
            $e->setMessage('foo message');
			$this->assertNotEmpty($e->getMessage());
		}
        
        public function testAddAttachmentFileNotExist() {
            $e = new Email(); 
            $this->assertFalse($e->hasAttachments());
            
            $e->addAttachment('attachment.ext');
			$this->assertFalse($e->hasAttachments());
		}
        
        public function testAddAttachmentFileExists() {
            $e = new Email(); 
            $this->assertFalse($e->hasAttachments());
            
            $e->addAttachment(TESTS_PATH . 'assets/attachment.txt');
			$this->assertTrue($e->hasAttachments());
		}
        
        public function testParameters() {
            $e = new Email(); 
            $this->assertEmpty($e->getParameters());
            $param = '-f foo@bar.com';
            $e->setParameters($param);
			$this->assertSame($param, $e->getParameters());
		}
        
        public function testWrap() {
            $e = new Email(); 
            //default value is 78
            $this->assertSame(78, $e->getWrap());
            
            //negative value
            $wrap = -1;
            $e->setWrap($wrap);
			$this->assertSame(78, $e->getWrap());
            
            //negative value
            $wrap = 100;
            $e->setWrap($wrap);
			$this->assertSame(100, $e->getWrap());
		}
        
        public function testSendNoDestinataireOrSender() {
            //can not test
            $e = new Email();
            $this->assertFalse($e->send());
            
            $e = new Email();
            $e->setTo('baz@foo.fr');
            $this->assertFalse($e->send());
        }
        
        public function testSend() {
            //make test to fail
            ini_set('SMTP', 'fooooooooobarrrrr');
            ini_set('smtp_port', '23456765');
            
            //using mail protocol
            $e = new Email();
            $e->setFrom('foo@bar.com')
              ->setTo('baz@foo.fr')
              ->useMail();
            $this->assertFalse($e->send());
            $this->assertNotEmpty($e->getError());
            
            //using smtp protocol
            $e = new Email();
            $e->setFrom('foo@bar.com')
              ->setTo('baz@foo.fr')
              ->useSmtp()
              ->setSmtpConfig(array('hostname' => 'ffffffffffffffffffffffffff'));
            $this->assertFalse($e->send());
            
             //using wrong protocol
             $proto = new ReflectionProperty('Email', 'protocol');
             $proto->setAccessible(true);
            
            $e = new Email();
            $proto->setValue($e, 'fooprotocol');
            
            $e->setFrom('foo@bar.com')
              ->setTo('baz@foo.fr');
            $this->assertFalse($e->send());
        }
        
        public function testSendWithAttachment() {
            //make test to fail
            ini_set('SMTP', 'fooooooooobarrrrr');
            ini_set('smtp_port', '23456765');
            
            //using mail protocol
            $e = new Email();
            $e->setFrom('foo@bar.com')
              ->setTo('baz@foo.fr')
              ->addAttachment(TESTS_PATH . 'assets/attachment.txt');
            $this->assertFalse($e->send());
            
            $e = new Email();
            $this->assertFalse($e->send());
        }
        
        public function testSendMail() {
            //make test to fail
            ini_set('SMTP', 'fooooooooobarrrrr');
            ini_set('smtp_port', '23456765');
            
            //using mail protocol
            $e = new Email();
            $e->setFrom('foo@bar.com')
              ->setTo('baz@foo.fr')
              ->useMail();
            $this->assertFalse($e->send());
            $this->assertNotEmpty($e->getError());
        }
        
        
        public function testSendSmtpConnectionError() {
            $e = new Email();
            $e->setFrom('foo@bar.com')
              ->setTo('baz@foo.fr')
              ->useSmtp()
              ->setSmtpConfig(array('hostname' => 'ffffffffffffffffffffffffff'));
            $this->assertFalse($e->send());
        }
        
        public function testSendSmtp() {
            //Mock method smtpConnection to return true
            $e = $this->getMockBuilder('Email')
                              ->setMethods(array('smtpConnection'))
                              ->getMock();
            
             $e->expects($this->any())
                 ->method('smtpConnection')
                 ->will($this->returnValue(true));
                 
            $e->setFrom('foo@bar.com')
              ->setTo('baz@foo.fr')
              ->setCc(array('foo' => 'foo@bar.com', 'bar@foo.fr'))
              ->setBcc(array('foo' => 'foo@bar.com', 'bar@foo.fr'))
              ->useSmtp()
              ->setSmtpConfig(array('transport' => 'tls'));
            $this->assertFalse($e->send());
            $this->assertNotEmpty($e->getLogs());
        }

	}
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
            $rTo = $this->getPrivateProtectedAttribute('Email', 'to');
            $this->assertEmpty($rTo->getValue($e));
            
            $e->setTo($email, $name);
			$this->assertNotEmpty($rTo->getValue($e));
			$this->assertSame(1, count($rTo->getValue($e)));
            
            $name = 'Foo Bar';
            $e->setTo($email, $name);
			$this->assertSame(2, count($rTo->getValue($e)));
		}

        public function testSetTos() {
            $emails = array('foo@bar.com', 'baz' => 'baz@foo.com');
            
            $e = new Email(); 
            $rTo = $this->getPrivateProtectedAttribute('Email', 'to');
            $this->assertEmpty($rTo->getValue($e));
            
            $e->setTos($emails);
			$this->assertNotEmpty($rTo->getValue($e));
			$this->assertSame(2, count($rTo->getValue($e)));
		}
        
        public function testSetGetCc() {
            $emails = array('foo'=> 'foo@bar.com', 'baz' => 'baz@foo.com');
            
            $e = new Email(); 
            $rCc = $this->getPrivateProtectedAttribute('Email', 'cc');
            $this->assertEmpty($e->getHeaders());
            $e->setCc($emails);
            $this->assertNotEmpty($rCc->getValue($e));
			$this->assertNotEmpty($e->getHeaders());
			$this->assertSame(1, count($e->getHeaders()));
		}
        
        public function testSetGetBcc() {
            $emails = array('foo@bar.com', 'baz' => 'baz@foo.com');
            
            $e = new Email(); 
            $this->assertEmpty($e->getHeaders());
            $rBcc = $this->getPrivateProtectedAttribute('Email', 'bcc');
            
            
            $e->setBcc($emails);
            $this->assertNotEmpty($rBcc->getValue($e));
			$this->assertNotEmpty($e->getHeaders());
			$this->assertSame(1, count($e->getHeaders()));
			
            //empty value
            $e->setBcc(array());
            $this->assertEmpty($rBcc->getValue($e));
            $this->assertSame(1, count($e->getHeaders()));
		}
        
        public function testSetGetSmtpProtocol() {
           $e = new Email(); 
           $rProto = $this->getPrivateProtectedAttribute('Email', 'protocol');
            //Default is mail
            $this->assertSame('mail', $rProto->getValue($e));
            
            $e->useMail();
            $this->assertSame('mail', $rProto->getValue($e));
            
            $e->useSmtp();
            $this->assertSame('smtp', $rProto->getValue($e));
		}
        
        public function testSetGetSmtpConfig() {
            $e = new Email(); 
            $rSmtpConfig = $this->getPrivateProtectedAttribute('Email', 'smtpConfig');
            //Default configuration
            $configs = $rSmtpConfig->getValue($e);
            $this->assertSame('plain', $configs['transport']);
            $this->assertSame('localhost', $configs['hostname']);
            $this->assertSame(25, $configs['port']);
            $this->assertNull($configs['username']);
            $this->assertNull($configs['password']);
            $this->assertSame(30, $configs['connection_timeout']);
            $this->assertSame(10, $configs['response_timeout']);
            
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
            $configs = $rSmtpConfig->getValue($e);
            $this->assertNotEmpty($configs);
            $this->assertSame(7, count($configs));
            
            $this->assertSame('tls', $configs['transport']);
            $this->assertSame('my.smtpserver.com', $configs['hostname']);
            $this->assertSame(2525, $configs['port']);
            $this->assertSame('foo', $configs['username']);
            $this->assertSame('bar', $configs['password']);
            $this->assertSame(5, $configs['connection_timeout']);
            $this->assertSame(2, $configs['response_timeout']);
            
            $this->assertNotEmpty($configs);
            $this->assertSame(7, count($configs));
 		}
                
        public function testSetReplyTo() {
            $email = 'foo@bar.com';
            $name = null;
            
            $e = new Email(); 
            $rReplyTo = $this->getPrivateProtectedAttribute('Email', 'replyTo');
            $e->setReplyTo($email, $name);
			$this->assertNotEmpty($rReplyTo->getValue($e));
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
            $e->setSubject('foo subject');
            $rSubject = $this->getPrivateProtectedAttribute('Email', 'subject');
            $this->assertNotEmpty($rSubject->getValue($e));
		}
        
        public function testMessage() {
            $e = new Email(); 
            $rMessage = $this->getPrivateProtectedAttribute('Email', 'message');
            $this->assertEmpty($rMessage->getValue($e));
            
            $e->setMessage('foo message');
			$this->assertNotEmpty($rMessage->getValue($e));
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
            $rWrap = $this->getPrivateProtectedAttribute('Email', 'wrap');
            
            //default value is 78
            $this->assertSame(78, $rWrap->getValue($e));
            
            //negative value
            $wrap = -1;
            $e->setWrap($wrap);
			$this->assertSame(78, $rWrap->getValue($e));
            
            //negative value
            $wrap = 100;
            $e->setWrap($wrap);
			$this->assertSame(100, $rWrap->getValue($e));
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
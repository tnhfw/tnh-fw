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
            
            $e->setProtocolMail();
            $this->assertSame('mail', $e->getProtocol());
            
            $e->setProtocolSmtp();
            $this->assertSame('smtp', $e->getProtocol());
		}
        
        public function testSetGetSmtpTransport() {
            $e = new Email(); 
            //Default is plain
            $this->assertSame('plain', $e->getTransport());
            
            $e->setTransportTls();
            $this->assertSame('tls', $e->getTransport());
            
            $e->setTransportPlain();
            $this->assertSame('plain', $e->getTransport());
		}
        
        public function testSetGetSmtpHostname() {
            $e = new Email(); 
            //Default is localhost
            $this->assertSame('localhost', $e->getSmtpHostname());
            
            $e->setSmtpHostname('foo.com');
            $this->assertSame('foo.com', $e->getSmtpHostname());
            
            $e->setSmtpHostname('bar.foo');
            $this->assertSame('bar.foo', $e->getSmtpHostname());
		}
        
        public function testSetGetSmtpPort() {
            $e = new Email(); 
            //Default is 25
            $this->assertSame(25, $e->getSmtpPort());
            
            $e->setSmtpPort(2525);
            $this->assertSame(2525, $e->getSmtpPort());
            
            $e->setSmtpPort(587);
            $this->assertSame(587, $e->getSmtpPort());
		}
        
        public function testSetGetSmtpUsername() {
            $e = new Email(); 
            $e->setSmtpUsername('foo');
            $this->assertSame('foo', $e->getSmtpUsername());
            
            $e->setSmtpUsername('bar');
            $this->assertSame('bar', $e->getSmtpUsername());
		}
        
        public function testSetGetSmtpPassword() {
            $e = new Email(); 
            $e->setSmtpPassword('foopwd');
            $this->assertSame('foopwd', $e->getSmtpPassword());
            
            $e->setSmtpPassword('pwdbar');
            $this->assertSame('pwdbar', $e->getSmtpPassword());
		}
        
        public function testSetGetSmtpConnectionTimeout() {
            $e = new Email(); 
            //Default is 30
            $this->assertSame(30, $e->getSmtpConnectionTimeout());
            
            $e->setSmtpConnectionTimeout(60);
            $this->assertSame(60, $e->getSmtpConnectionTimeout());
            
            $e->setSmtpConnectionTimeout(10);
            $this->assertSame(10, $e->getSmtpConnectionTimeout());
		}
        
        public function testSetGetSmtpResponseTimeout() {
            $e = new Email(); 
            //Default is 10
            $this->assertSame(10, $e->getSmtpResponseTimeout());
            
            $e->setSmtpResponseTimeout(20);
            $this->assertSame(20, $e->getSmtpResponseTimeout());
            
            $e->setSmtpResponseTimeout(5);
            $this->assertSame(5, $e->getSmtpResponseTimeout());
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
              ->setProtocolMail();
            $this->assertFalse($e->send());
            $this->assertNotEmpty($e->getError());
            
            //using smtp protocol
            $e = new Email();
            $e->setFrom('foo@bar.com')
              ->setTo('baz@foo.fr')
              ->setProtocolSmtp()
              ->setSmtpHostname('ffffffffffffffffffffffffff');
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
              ->setProtocolMail();
            $this->assertFalse($e->send());
            $this->assertNotEmpty($e->getError());
        }
        
        
        public function testSendSmtpConnectionError() {
            $e = new Email();
            $e->setFrom('foo@bar.com')
              ->setTo('baz@foo.fr')
              ->setProtocolSmtp()
              ->setSmtpHostname('ffffffffffffffffffffffffff');
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
              ->setProtocolSmtp()
              ->setTransportTls();
            $this->assertFalse($e->send());
            $this->assertNotEmpty($e->getLogs());
        }

	}
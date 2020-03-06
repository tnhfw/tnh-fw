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
            $this->assertSame(2, count($e->getHeaders()));
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
        
        public function testSetCc() {
            $emails = array('foo'=> 'foo@bar.com', 'baz' => 'baz@foo.com');
            
            $e = new Email(); 
            $this->assertEmpty($e->getHeaders());
            
            $e->setCc($emails);
			$this->assertNotEmpty($e->getHeaders());
			$this->assertSame(1, count($e->getHeaders()));
		}
        
        public function testSetBcc() {
            $emails = array('foo@bar.com', 'baz' => 'baz@foo.com');
            
            $e = new Email(); 
            $this->assertEmpty($e->getHeaders());
            
            $e->setBcc($emails);
			$this->assertNotEmpty($e->getHeaders());
			$this->assertSame(1, count($e->getHeaders()));
            
            //empty value
            $e->setBcc(array());
            $this->assertSame(1, count($e->getHeaders()));
            
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
			$this->assertSame(2, count($e->getHeaders()));
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
        
        public function testGetAttachmentDataFileNotExist() {
            $e = new Email(); 
            $this->assertFalse($e->getAttachmentData('attachment.ext'));
		}
        
        public function testGetAttachmentDataFileExists() {
            $e = new Email(); 
            $expected_content = 'attachment';
            $data = $e->getAttachmentData(TESTS_PATH . 'assets/attachment.txt');
            $this->assertSame($expected_content, $data);
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
        
        public function testSend() {
            //make test to fail
            ini_set('SMTP', 'fooooooooobarrrrr');
            ini_set('smtp_port', '23456765');
            
            //can not test
            $e = new Email();
            $e->setFrom('foo@bar.com')
              ->setTo('baz@foo.fr')
              ->addAttachment(TESTS_PATH . 'assets/attachment.txt');
            $this->assertFalse($e->send());
            
            $e = new Email();
            $this->assertFalse($e->send());
        }

	}
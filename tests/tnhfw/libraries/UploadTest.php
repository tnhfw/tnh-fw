<?php 

	/**
     * Upload library class tests
     *
     * @group core
     * @group libraries
     */
	class UploadTest extends TnhTestCase {	
	
		public function testConstructor() {
            $u = new Upload();
			$this->assertTrue(true);
		}
        
        public function testSetInput() {
            $u = new Upload();
			$this->assertEmpty($u->getInput());
            $u->setInput('foo');
			$this->assertSame('foo', $u->getInput());
		}
        
        public function testSetUploadedFileData() {
            $u = new Upload();
			$this->assertEmpty($u->getUploadedFileData());
            $files = array(
                'name' => 'foo',
                'tmp_name' => '/foo/bar',
                'error' => 0,
                'size' => 344
            );
            $u->setUploadedFileData($files);
			$this->assertSame($files, $u->getUploadedFileData());
		}
        
        public function testSetFilename() {
            $u = new Upload();
			$this->assertEmpty($u->getFilename());
            $u->setFilename('foo');
			$this->assertSame('foo', $u->getFilename());
		}
        
        public function testSetAutoFilename() {
            $u = new Upload();
			$this->assertEmpty($u->getFilename());
            
            $u->setAutoFilename();
			$this->assertNotEmpty($u->getFilename());
		}
        
        public function testSetMaxFileSize() {
            $u = new Upload();
			$this->assertSame(0.0, $u->getMaxFileSize());
            
            $sizeHuman = '1M';
            $sizeByte = 1048576.0;
            $u->setMaxFileSize($sizeHuman);
			$this->assertSame($sizeByte, $u->getMaxFileSize());
            
            $u->setMaxFileSize($sizeByte);
			$this->assertSame($sizeByte, $u->getMaxFileSize());
		}
        
         public function testSetAllowedMimeTypes() {
             $u = new Upload();
			 $this->assertEmpty($u->getAllowMimeType());
            
            //empty array
            $u->setAllowedMimeTypes(array());
            $this->assertEmpty($u->getAllowMimeType());
            
            $u->setAllowedMimeTypes(array('image/jpg', 'application/pdf'));
            $this->assertNotEmpty($u->getAllowMimeType());
            $this->assertContains('image/jpg', $u->getAllowMimeType());
         }
         
         public function testSetMimeHelping() {
             $u = new Upload();
			 $this->assertEmpty($u->getAllowMimeType());
            
            //invalid name
            $u->setMimeHelping('ffffffffffff');
            $this->assertEmpty($u->getAllowMimeType());
            
            $u->setMimeHelping('image');
            $this->assertNotEmpty($u->getAllowMimeType());
            $this->assertSame(5, count($u->getAllowMimeType()));
         }
         
         public function testClearAllowedMimeTypes() {
             $u = new Upload();
			 $this->assertEmpty($u->getAllowMimeType());
             
             $u->setMimeHelping('document');
             $this->assertNotEmpty($u->getAllowMimeType());
             $this->assertSame(8, count($u->getAllowMimeType()));
             
             $u->clearAllowedMimeTypes();
             $this->assertEmpty($u->getAllowMimeType());
         }
         
         
         public function testSetCallbackInput() {
             $u = new Upload();
			 $this->assertEmpty($u->getCallbacks());
             $this->assertArrayNotHasKey('input', $u->getCallbacks());
            //not an callable
            $u->setCallbackInput('fooooooooooooxxxxxxxx');
            $this->assertEmpty($u->getCallbacks());
            
            //using an example of PHP function
            $u->setCallbackInput('trim');
            $this->assertNotEmpty($u->getCallbacks());
            $this->assertArrayHasKey('input', $u->getCallbacks());
            $values = $u->getCallbacks();
            $this->assertSame('trim', $values['input']);
         }
         
         public function testSetCallbackOutput() {
             $u = new Upload();
			 $this->assertEmpty($u->getCallbacks());
             $this->assertArrayNotHasKey('output', $u->getCallbacks());
            //not an callable
            $u->setCallbackOutput('bazbaroooooxxxxxxxx');
            $this->assertEmpty($u->getCallbacks());
            
            //using an example of PHP function
            $u->setCallbackOutput('trim');
            $this->assertNotEmpty($u->getCallbacks());
            $this->assertArrayHasKey('output', $u->getCallbacks());
            $values = $u->getCallbacks();
            $this->assertSame('trim', $values['output']);
         }
         
         public function testSetUploadFunction() {
             $u = new Upload();
			 $this->assertSame('move_uploaded_file', $u->getUploadFunction());
            
            //is not an callable
            $u->setUploadFunction('ffffffffffff');
            $this->assertSame('move_uploaded_file', $u->getUploadFunction());
            
            $u->setUploadFunction('copy');
            $this->assertSame('copy', $u->getUploadFunction());
         }
         
         public function testSetDestinationDirectory() {
             //Can not test because vfsStream does not support for function "realpath", "chdir"
             $u = new Upload();
             $u->setDestinationDirectory('foo');
             $u->setDestinationDirectory('bar', true); //create if not exist
             $this->assertNotEmpty($u->getDestinationDirectory());
         }
         
         public function testAllowOverwriting() {
             $u = new Upload();
             $this->assertFalse($u->isAllowOverwriting());
             $u->allowOverwriting();
             $this->assertTrue($u->isAllowOverwriting());
         }
         
         public function testIsUploaded() {
             //Can not test because vfsStream does not support for function "is_uploaded_file"
             $u = new Upload();
             $this->assertFalse($u->isUploaded());
         }
         
         public function testSizeFormat() {
             $u = new Upload();
             $size = 1024;
             $sizeFormat = $this->runPrivateProtectedMethod($u, 'sizeFormat', array($size));
             $this->assertSame('1K', $sizeFormat);
             $sizeFormat = $this->runPrivateProtectedMethod($u, 'sizeFormat', array(-9997));
             $this->assertNull($sizeFormat);
         }
         
         
         public function testSave() {
             $u = $this->getUploadMockInstance(true);
             
             $files['image'] = array(
                'name' => 'foo.ext',
                'tmp_name' => '/foo/bar',
                'error' => 0,
                'size' => 344,
                'type' => 'image/jpg'
            );
             $u->setUploadedFileData($files);
             $u->setInput('image');
             $u->setAllowMimeType('image/jpg');
             $u->setUploadFunction('copy');
             $this->assertFalse($u->save());
             
             //No mime validation
             $u = $this->getUploadMockInstance(true);
             $u->setInput('image');
             $u->setUploadFunction('copy');
             $this->assertFalse($u->save());
             
             $u = $this->getUploadMockInstance(true);
             
             //using custom filename
             $u->setFilename('my_image');
             $u->setUploadedFileData($files);
             $u->setInput('image');
             $u->setUploadFunction('copy');
             $this->assertFalse($u->save());
             $this->assertSame('my_image.ext', $u->getFilename());
             
             
             $u = $this->getUploadMockInstance(true);
             //upload file contains error
             $files['image'] = array(
                'name' => 'foo',
                'tmp_name' => '/foo/bar',
                'error' => 1,
                'size' => 344,
                'type' => 'image/jpg'
            );
            $u->setUploadedFileData($files);
            $u->setInput('image');
            $u->setUploadFunction('copy');
            $u->save();
            $this->assertFalse($u->getStatus());
            $this->assertNotEmpty($u->getError());
            $this->assertNotEmpty($u->getInfo());
            $this->assertInstanceOf('stdClass', $u->getInfo());
            
            //Using input/output callbacks
            $u = $this->getUploadMockInstance(true);
             $files['image'] = array(
                'name' => 'foo',
                'tmp_name' => '/foo/bar',
                'error' => 0,
                'size' => 344,
                'type' => 'image/jpg'
            );
            $u->setUploadedFileData($files);
            $u->setInput('image');
            $u->setCallbackInput('trim');
            $u->setCallbackOutput('rtrim');
            $u->save();
            $this->assertFalse($u->getStatus());
            
            //invalide mime type
            $u = $this->getUploadMockInstance(true);
             $files['image'] = array(
                'name' => 'foo',
                'tmp_name' => '/foo/bar',
                'error' => 0,
                'size' => 13,
                'type' => 'image/jpg'
            );
            $u->setUploadedFileData($files);
            $u->setInput('image');
            $u->setAllowMimeType('foobar');
            $u->save();
            $this->assertFalse($u->getStatus());
            
            
            //upload file too big
            $u = $this->getUploadMockInstance(true);
             $files['image'] = array(
                'name' => 'foo',
                'tmp_name' => '/foo/bar',
                'error' => 0,
                'size' => 349997969694,
                'type' => 'image/jpg'
            );
            $u->setUploadedFileData($files);
            $u->setInput('image');
            $u->setMaxFileSize('1K');
            $u->save();
            $this->assertFalse($u->getStatus());
            
            //No uploaded file
             $u = $this->getUploadMockInstance(false);
             $u->setInput('image');
             $u->setUploadFunction('copy');
             $this->assertFalse($u->save());
            
         }
         
         /**
         * Get upload instance for test with mocking isUploaded() method
         */
         private function getUploadMockInstance($isUploadedStatus = true) {
             $upload = $this->getMockBuilder('Upload')
                              ->setMethods(array('isUploaded'))
                              ->getMock();
            
             $upload->expects($this->any())
                 ->method('isUploaded')
                 ->will($this->returnValue($isUploadedStatus));
              return $upload;
         }

	}
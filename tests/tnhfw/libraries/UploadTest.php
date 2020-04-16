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
            $rInput = $this->getPrivateProtectedAttribute('Upload', 'input');
            $this->assertEmpty($rInput->getValue($u));
            $u->setInput('foo');
            $this->assertSame('foo', $rInput->getValue($u));
        }
        
        public function testSetFilename() {
            $u = new Upload();
            $rFilename = $this->getPrivateProtectedAttribute('Upload', 'filename');
            $this->assertEmpty($rFilename->getValue($u));
            $u->setFilename('foo');
            $this->assertSame('foo', $rFilename->getValue($u));
        }
        
        public function testSetAutoFilename() {
            $u = new Upload();
            $rFilename = $this->getPrivateProtectedAttribute('Upload', 'filename');
            $this->assertEmpty($rFilename->getValue($u));
            
            $u->setAutoFilename();
            $this->assertNotEmpty($rFilename->getValue($u));
        }
        
        public function testSetMaxFileSize() {
            $u = new Upload();
            $rMaxSize = $this->getPrivateProtectedAttribute('Upload', 'maxFileSize');
            $this->assertSame(0.0, $rMaxSize->getValue($u));
            
            $sizeHuman = '1M';
            $sizeByte = 1048576.0;
            $u->setMaxFileSize($sizeHuman);
            $this->assertSame($sizeByte, $rMaxSize->getValue($u));
            
            $u->setMaxFileSize($sizeByte);
            $this->assertSame($sizeByte, $rMaxSize->getValue($u));
        }
        
         public function testsetAllowMimeTypes() {
             $u = new Upload();
             $rAllowedMimeType = $this->getPrivateProtectedAttribute('Upload', 'allowedMimeTypes');
             $this->assertEmpty($rAllowedMimeType->getValue($u));
            
            //empty array
            $u->setAllowMimeTypes(array());
            $this->assertEmpty($rAllowedMimeType->getValue($u));
            
            $u->setAllowMimeTypes(array('image/jpg', 'application/pdf'));
            $this->assertNotEmpty($rAllowedMimeType->getValue($u));
            $this->assertContains('image/jpg', $rAllowedMimeType->getValue($u));
         }
         
         public function testSetMimeHelping() {
             $u = new Upload();
             $rAllowedMimeType = $this->getPrivateProtectedAttribute('Upload', 'allowedMimeTypes');
             $this->assertEmpty($rAllowedMimeType->getValue($u));
            
            //invalid name
            $u->setMimeHelping('ffffffffffff');
            $this->assertEmpty($rAllowedMimeType->getValue($u));
            
            $u->setMimeHelping('image');
            $this->assertNotEmpty($rAllowedMimeType->getValue($u));
            $this->assertSame(5, count($rAllowedMimeType->getValue($u)));
         }
         
         public function testClearAllowedMimeTypes() {
             $u = new Upload();
             $rAllowedMimeType = $this->getPrivateProtectedAttribute('Upload', 'allowedMimeTypes');
             $this->assertEmpty($rAllowedMimeType->getValue($u));
             
             $u->setMimeHelping('document');
             $this->assertNotEmpty($rAllowedMimeType->getValue($u));
             $this->assertSame(8, count($rAllowedMimeType->getValue($u)));
             
             $u->clearAllowedMimeTypes();
             $this->assertEmpty($rAllowedMimeType->getValue($u));
         }
         
         
         public function testSetCallbackInput() {
             $u = new Upload();
             $rCallbacks = $this->getPrivateProtectedAttribute('Upload', 'callbacks');
             $this->assertEmpty($rCallbacks->getValue($u));
             $this->assertArrayNotHasKey('input', $rCallbacks->getValue($u));
            //not an callable
            $u->setCallbackInput('fooooooooooooxxxxxxxx');
            $this->assertEmpty($rCallbacks->getValue($u));
            
            //using an example of PHP function
            $u->setCallbackInput('trim');
            $this->assertNotEmpty($rCallbacks->getValue($u));
            $this->assertArrayHasKey('input', $rCallbacks->getValue($u));
            $values = $rCallbacks->getValue($u);
            $this->assertSame('trim', $values['input']);
         }
         
         public function testSetCallbackOutput() {
             $u = new Upload();
             $rCallbacks = $this->getPrivateProtectedAttribute('Upload', 'callbacks');
             $this->assertEmpty($rCallbacks->getValue($u));
             $this->assertArrayNotHasKey('output', $rCallbacks->getValue($u));
            //not an callable
            $u->setCallbackOutput('bazbaroooooxxxxxxxx');
            $this->assertEmpty($rCallbacks->getValue($u));
            
            //using an example of PHP function
            $u->setCallbackOutput('trim');
            $this->assertNotEmpty($rCallbacks->getValue($u));
            $this->assertArrayHasKey('output', $rCallbacks->getValue($u));
            $values = $rCallbacks->getValue($u);
            $this->assertSame('trim', $values['output']);
         }
         
         public function testSetUploadFunction() {
             $u = new Upload();
             $rUploadFunction = $this->getPrivateProtectedAttribute('Upload', 'uploadFunction');
             $this->assertSame('move_uploaded_file', $rUploadFunction->getValue($u));
            
            //is not an callable
            $u->setUploadFunction('ffffffffffff');
            $this->assertSame('move_uploaded_file', $rUploadFunction->getValue($u));
            
            $u->setUploadFunction('copy');
            $this->assertSame('copy', $rUploadFunction->getValue($u));
         }
         
         public function testSetDestinationDirectory() {
             //Can not test because vfsStream does not support for function "realpath", "chdir"
             $u = new Upload();
             $rDestinationDirectory = $this->getPrivateProtectedAttribute('Upload', 'destinationDirectory');
             $u->setDestinationDirectory('foo');
             $u->setDestinationDirectory('bar'); //create if not exist
             $this->assertNotEmpty($rDestinationDirectory->getValue($u));
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
             $rUploadedFileData = $this->getPrivateProtectedAttribute('Upload', 'uploadedFileData');
             $files['image'] = array(
                'name' => 'foo.ext',
                'tmp_name' => '/foo/bar',
                'error' => 0,
                'size' => 344,
                'type' => 'image/jpg'
            );
             $rUploadedFileData->setValue($u, $files);
             $u->setInput('image');
             $u->setAllowMimeType('image/jpg');
             $u->setUploadFunction('copy');
             $this->assertFalse($u->save());
             
             //No mime validation
             $u = $this->getUploadMockInstance(true);
             $u->setInput('image');
             $u->setUploadFunction('copy');
             $this->assertFalse($u->save());
             
             
             //using custom filename
             $u = $this->getUploadMockInstance(true);
             $rUploadedFileData->setValue($u, $files);
             $u->setInput('image');
             $u->setUploadFunction('copy');
             $this->assertFalse($u->save());
             $rFilename = $this->getPrivateProtectedAttribute('Upload', 'filename');
             $this->assertSame('foo.ext', $rFilename->getValue($u));
             
             
             $u = $this->getUploadMockInstance(true);
             //upload file contains error
             $files['image'] = array(
                'name' => 'foo',
                'tmp_name' => '/foo/bar',
                'error' => 1,
                'size' => 344,
                'type' => 'image/jpg'
            );
            $rUploadedFileData->setValue($u, $files);
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
            
            $rUploadedFileData->setValue($u, $files);
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
            $rUploadedFileData->setValue($u, $files);
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
            $rUploadedFileData->setValue($u, $files);
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
         private function getUploadMockInstance($isUploadedMethodMock = true) {
             $upload = $this->getMockBuilder('Upload')
                              ->setMethods(array('isUploaded'))
                              ->getMock();
            
             $upload->expects($this->any())
                 ->method('isUploaded')
                 ->will($this->returnValue($isUploadedMethodMock));
              return $upload;
         }

    }
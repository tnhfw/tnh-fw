<?php 

	/**
     * PDF library class tests
     *
     * @group core
     * @group libraries
     */
	class PDFTest extends TnhTestCase {	
	
        protected function setUp() {
            parent::setUp();
            //need setup for each test
            $this->vfsRoot = vfsStream::setup();
            $this->vfsLogPath = vfsStream::newDirectory('pdf')->at($this->vfsRoot);
            $this->config->set('log_save_path', $this->vfsLogPath->url() . '/');
            if($this->vfsLogPath->hasChild($this->logFilename)){
               $this->vfsLogPath->removeChild($this->logFilename);
            }
		}
        
		public function testConstructor() {
            $p = new PDF();
			$this->assertInstanceOf('Dompdf', $p->getDompdf());
		}
        
        public function testDefault() {
            //Default
            $p = new PDF();
            $rFilename = $this->getPrivateProtectedAttribute('PDF', 'filename');
            $rPaper = $this->getPrivateProtectedAttribute('PDF', 'paper');
            $rOrientation = $this->getPrivateProtectedAttribute('PDF', 'orientation');
            
            $this->assertSame('output.pdf', $rFilename->getValue($p));
            $this->assertSame('portrait', $rOrientation->getValue($p));
            $this->assertSame('A4', $rPaper->getValue($p));
        }
        
        public function testSetProperties() {
            //Default
            $p = new PDF();
            $rFilename = $this->getPrivateProtectedAttribute('PDF', 'filename');
            $rPaper = $this->getPrivateProtectedAttribute('PDF', 'paper');
            $rOrientation = $this->getPrivateProtectedAttribute('PDF', 'orientation');
            $rHtml = $this->getPrivateProtectedAttribute('PDF', 'html');
            
            //Filename without extension
            $p->setFilename('foo');
            $this->assertSame('foo.pdf', $rFilename->getValue($p));
            
             //Filename with extension
            $p->setFilename('bar.pdf');
            $this->assertSame('bar.pdf', $rFilename->getValue($p));
            
            //Paper
            $p->setPaper('A1');
            $this->assertSame('A1', $rPaper->getValue($p));
            
            //Orientation
            $p->portrait();
            $this->assertSame('portrait', $rOrientation->getValue($p));
            
            $p->landscape();
            $this->assertSame('landscape', $rOrientation->getValue($p));
        }
        
        public function testCanvas() {
            //Default before rendered
            $p = new PDF();
            $this->assertNull($p->getCanvas());
            
            $filename = 'output.pdf';
            $vfsRoot = vfsStream::setup();
            $vfsFilePath = vfsStream::newDirectory('pdf')->at($vfsRoot);
            $p = new PDF();
            $p->setHtml('foo')
               ->setFilename($vfsFilePath->url() . '/' . $filename)
               ->save();            
            $this->assertNotNull($p->getCanvas());
        }
        
        
        public function testSave() {
            $filename = 'output.pdf';
            $vfsRoot = vfsStream::setup();
            $vfsFilePath = vfsStream::newDirectory('pdf')->at($vfsRoot);
            if($vfsFilePath->hasChild($filename)){
               $vfsFilePath->removeChild($filename);
            }
            $p = new PDF();
            $p->setHtml('foo')
               ->setFilename($vfsFilePath->url() . '/' . $filename)
               ->save();
            $this->assertTrue($vfsFilePath->hasChild($filename));
        }
        
        public function testSaveFilenameWithoutExtension() {
            //Filename without ".pdf" extension
            $filename = 'mytnh';
            $vfsRoot = vfsStream::setup();
            $vfsFilePath = vfsStream::newDirectory('pdf')->at($vfsRoot);
            if($vfsFilePath->hasChild($filename)){
               $vfsFilePath->removeChild($filename);
            }
            $p = new PDF();
            $p->setHtml('foo')
               ->setFilename($vfsFilePath->url() . '/' . $filename)
               ->save();
            $this->assertTrue($vfsFilePath->hasChild($filename . '.pdf'));
        }
        

	}
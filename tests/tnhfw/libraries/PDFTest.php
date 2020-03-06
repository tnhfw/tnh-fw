<?php 

	/**
     * PDF library class tests
     *
     * @group core
     * @group libraries
     */
	class PDFTest extends TnhTestCase {	
	
		public function testConstructor() {
            $p = new PDF();
			$this->assertInstanceOf('Dompdf', $p->getDompdf());
		}
        
        public function testGenerate() {
            //Default
            $p = new PDF();
            $content = '<p>Foo</p>';
            $filename = 'test.pdf';
            
            $pdf = $p->generate($content, $filename, $stream = false, $paper = 'A4', $orientation = 'portrait');
            $this->assertNotEmpty($pdf);
        }

	}
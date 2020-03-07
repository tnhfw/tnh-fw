<?php 

	/**
     * DatabaseQueryResult class tests
     *
     * @group core
     * @group database
     */
	class DatabaseQueryResultTest extends TnhTestCase {	
	
		
		public function testConstructor() {
            //Default
            $r = new DatabaseQueryResult();
            $this->assertNull($r->getResult());
            $this->assertSame(0, $r->getNumRows());
            
            //Using parameters
            $result = array('foo');
            $numRows = 34;
            $r = new DatabaseQueryResult($result, $numRows);
            $this->assertSame($result, $r->getResult());
            $this->assertSame($numRows, $r->getNumRows());
		}
        
        public function testSetResult() {
            //Default
            $r = new DatabaseQueryResult();
            $this->assertNull($r->getResult());
            
            $result = new stdClass();
            $r->setResult($result);
            $this->assertSame($result, $r->getResult());
		}
        
        public function testSetNumRows() {
            //Default
            $r = new DatabaseQueryResult();
            $this->assertSame(0, $r->getNumRows());
            
            $numRows = 3555;
            $r->setNumRows($numRows);
            $this->assertSame($numRows, $r->getNumRows());
		}

	}
<?php 

	/**
     * Benchmark library class tests
     *
     * @group core
     * @group libraries
     */
	class BenchmarkTest extends TnhTestCase {	
        protected $markersTime = null;
        protected $markersMemory = null;
        
        public function __construct(){
            parent::__construct();
            
            $this->markersTime = new ReflectionProperty('Benchmark', 'markersTime');
            $this->markersTime->setAccessible(true);
            
            $this->markersMemory = new ReflectionProperty('Benchmark', 'markersMemory');
            $this->markersMemory->setAccessible(true);
        }
        
		
		public function testMark() {
            $key = 'foo';
            $b = new Benchmark();
            
            $this->assertEmpty($this->markersTime->getValue($b));
			$this->assertEmpty($this->markersMemory->getValue($b));
            
            $b->mark($key);
            
            $this->assertNotEmpty($this->markersTime->getValue($b));
			$this->assertNotEmpty($this->markersMemory->getValue($b));
            
			$this->assertArrayHasKey($key, $this->markersTime->getValue($b));
			$this->assertArrayHasKey($key, $this->markersMemory->getValue($b));
		}
        
        public function testElapsedTimeMarkerNotExistsOrNull() {
            $b = new Benchmark();
            $this->assertSame(0, $b->elapsedTime('unknow_marker'));
            $this->assertSame(0, $b->elapsedTime(null));
		}
        
        public function testElapsedTimeEndMarkerNotSetBefore() {
            $key1 = 'foo';
            $key2 = 'bar';
            $b = new Benchmark();
            
            $this->assertEmpty($this->markersTime->getValue($b));
			
            $b->mark($key1);
            
            $this->assertNotEmpty($b->elapsedTime($key1, $key2));
		}
        
        
        public function testElapsedTimeUsingDecimalParam() {
            $key1 = 'foo';
            $key2 = 'bar';
            $b = new Benchmark();
            
            $this->assertEmpty($this->markersTime->getValue($b));
			
            $b->mark($key1);
            //some code
            $b->mark($key2);
            
            $markers[$key1] = 1583399249.5873;
            $markers[$key2] = 1583399249.5876;
            
            $this->markersTime->setValue($b, $markers);
            
            $this->assertArrayHasKey($key1, $this->markersTime->getValue($b));
			$this->assertArrayHasKey($key2, $this->markersTime->getValue($b));
            
            $this->assertNotEmpty($b->elapsedTime($key1, $key2));
            //default precision is 6
            $this->assertEquals(0.000300, $b->elapsedTime($key1, $key2));
            $this->assertEquals(0.000300, $b->elapsedTime($key1, $key2, 6));
            $this->assertEquals(0.00030, $b->elapsedTime($key1, $key2, 5));
            $this->assertEquals(0.0003, $b->elapsedTime($key1, $key2, 4));
            $this->assertEquals(0.000, $b->elapsedTime($key1, $key2, 3));
            $this->assertEquals(0.00, $b->elapsedTime($key1, $key2, 2));
            $this->assertEquals(0.0, $b->elapsedTime($key1, $key2, 1));
            $this->assertEquals(0, $b->elapsedTime($key1, $key2, 0));
            
            $markers[$key1] = 1;
            $markers[$key2] = 2;
            
            $this->markersTime->setValue($b, $markers);
            
            $this->assertArrayHasKey($key1, $this->markersTime->getValue($b));
			$this->assertArrayHasKey($key2, $this->markersTime->getValue($b));
            
            $this->assertNotEmpty($b->elapsedTime($key1, $key2));
            //default precision is 6
            $this->assertEquals(1.000000, $b->elapsedTime($key1, $key2));
            $this->assertEquals(1.000000, $b->elapsedTime($key1, $key2, 6));
            $this->assertEquals(1.00000, $b->elapsedTime($key1, $key2, 5));
            $this->assertEquals(1.0000, $b->elapsedTime($key1, $key2, 4));
            $this->assertEquals(1.000, $b->elapsedTime($key1, $key2, 3));
            $this->assertEquals(1.00, $b->elapsedTime($key1, $key2, 2));
            $this->assertEquals(1.0, $b->elapsedTime($key1, $key2, 1));
            $this->assertEquals(1, $b->elapsedTime($key1, $key2, 0));
		}
        
        
        public function testElapsedTimeManyMarkers() {
            $key1 = 'foo';
            $key2 = 'bar';
            $key3 = 'baz';
            $b = new Benchmark();
            
            $this->assertEmpty($this->markersTime->getValue($b));
			
            $b->mark($key1);
            //some code
            $b->mark($key2);
            //some code
            $b->mark($key3);
            
            $this->assertNotEmpty($b->elapsedTime($key1, $key2));
            $this->assertNotEmpty($b->elapsedTime($key1, $key3));
            $this->assertNotEmpty($b->elapsedTime($key2, $key3));
            
            $markers[$key1] = 12;
            $markers[$key2] = 15;
            $markers[$key3] = 27;
            
            $this->markersTime->setValue($b, $markers);
            
            $this->assertArrayHasKey($key1, $this->markersTime->getValue($b));
			$this->assertArrayHasKey($key2, $this->markersTime->getValue($b));
			$this->assertArrayHasKey($key3, $this->markersTime->getValue($b));
            
           $this->assertNotEmpty($b->elapsedTime($key1, $key2));
            $this->assertNotEmpty($b->elapsedTime($key1, $key3));
            $this->assertNotEmpty($b->elapsedTime($key2, $key3));
            
            //default precision is 6
            $this->assertEquals(3.000000, $b->elapsedTime($key1, $key2));
            $this->assertEquals(15.000000, $b->elapsedTime($key1, $key3));
            $this->assertEquals(12.000000, $b->elapsedTime($key2, $key3));
		}
        
        
        public function testMemoryUsageMarkerNotExistsOrNull() {
            $b = new Benchmark();
            $this->assertSame(0, $b->memoryUsage('unknow_marker'));
            $this->assertSame(0, $b->memoryUsage(null));
		}
        
        public function testMemoryUsageEndMarkerNotSetBefore() {
            $key1 = 'foo';
            $key2 = 'bar';
            $b = new Benchmark();
            
            $this->assertEmpty($this->markersMemory->getValue($b));
			
            $b->mark($key1);
            
            $this->assertNotEmpty($b->memoryUsage($key1, $key2));
		}
        
        
        public function testMemoryUsageUsingDecimalParam() {
            $key1 = 'foo';
            $key2 = 'bar';
            $b = new Benchmark();
            
            $this->assertEmpty($this->markersMemory->getValue($b));
			
            $b->mark($key1);
            //some code
            $b->mark($key2);
            
            $markers[$key1] = 1583400108.9306;
            $markers[$key2] = 1583400108.941;
            
            $this->markersMemory->setValue($b, $markers);
            
            $this->assertArrayHasKey($key1, $this->markersMemory->getValue($b));
			$this->assertArrayHasKey($key2, $this->markersMemory->getValue($b));
            
            $this->assertNotEmpty($b->memoryUsage($key1, $key2));
            //default precision is 6
            $this->assertEquals(0.010400, $b->memoryUsage($key1, $key2));
            $this->assertEquals(0.010400, $b->memoryUsage($key1, $key2, 6));
            $this->assertEquals(0.01040, $b->memoryUsage($key1, $key2, 5));
            $this->assertEquals(0.0104, $b->memoryUsage($key1, $key2, 4));
            $this->assertEquals(0.010, $b->memoryUsage($key1, $key2, 3));
            $this->assertEquals(0.01, $b->memoryUsage($key1, $key2, 2));
            $this->assertEquals(0.0, $b->memoryUsage($key1, $key2, 1));
            $this->assertEquals(0, $b->memoryUsage($key1, $key2, 0));
            
            $markers[$key1] = 10.8;
            $markers[$key2] = 26.1;
            
            $this->markersMemory->setValue($b, $markers);
            
            $this->assertArrayHasKey($key1, $this->markersMemory->getValue($b));
			$this->assertArrayHasKey($key2, $this->markersMemory->getValue($b));
            
            $this->assertNotEmpty($b->memoryUsage($key1, $key2));
            //default precision is 6
            $this->assertEquals(15.300000, $b->memoryUsage($key1, $key2));
            $this->assertEquals(15.300000, $b->memoryUsage($key1, $key2, 6));
            $this->assertEquals(15.30000, $b->memoryUsage($key1, $key2, 5));
            $this->assertEquals(15.3000, $b->memoryUsage($key1, $key2, 4));
            $this->assertEquals(15.300, $b->memoryUsage($key1, $key2, 3));
            $this->assertEquals(15.30, $b->memoryUsage($key1, $key2, 2));
            $this->assertEquals(15.3, $b->memoryUsage($key1, $key2, 1));
            $this->assertEquals(15, $b->memoryUsage($key1, $key2, 0));
		}
        
        
        public function testMemoryUsageManyMarkers() {
            $key1 = 'foo';
            $key2 = 'bar';
            $key3 = 'baz';
            $b = new Benchmark();
            
            $this->assertEmpty($this->markersMemory->getValue($b));
			
            $b->mark($key1);
            //some code
            $b->mark($key2);
            //some code
            $b->mark($key3);
            
            $this->assertNotEmpty($b->memoryUsage($key1, $key2));
            $this->assertNotEmpty($b->memoryUsage($key1, $key3));
            $this->assertNotEmpty($b->memoryUsage($key2, $key3));
            
            $markers[$key1] = 12;
            $markers[$key2] = 15;
            $markers[$key3] = 27;
            
            $this->markersMemory->setValue($b, $markers);
            
            $this->assertArrayHasKey($key1, $this->markersMemory->getValue($b));
			$this->assertArrayHasKey($key2, $this->markersMemory->getValue($b));
			$this->assertArrayHasKey($key3, $this->markersMemory->getValue($b));
            
           $this->assertNotEmpty($b->memoryUsage($key1, $key2));
            $this->assertNotEmpty($b->memoryUsage($key1, $key3));
            $this->assertNotEmpty($b->memoryUsage($key2, $key3));
            
            //default precision is 6
            $this->assertEquals(3.000000, $b->memoryUsage($key1, $key2));
            $this->assertEquals(15.000000, $b->memoryUsage($key1, $key3));
            $this->assertEquals(12.000000, $b->memoryUsage($key2, $key3));
		}

	}
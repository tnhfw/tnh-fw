<?php 

	/**
     * FileCache class tests
     *
     * @group core
     * @group cache
     */
	class FileCacheTest extends TnhTestCase {	
	
		public static function setUpBeforeClass() {
		
		}
		
		public static function tearDownAfterClass() {
			
		}
		
		protected function setUp()
        {
            parent::setUp();
            $this->vfsRoot = vfsStream::setup();
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
        }

		protected function tearDown() {
		}
		
		public function testConstructor() {
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
			$this->assertTrue($fc->isSupported());
			$this->assertFalse((new FileCache())->isSupported());
		}
        
        public function testSet() {
            $key = __FUNCTION__;
            $value = 'bar';
            $filename = md5($key) . '.cache';
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
			$this->assertTrue($fc->set($key, $value, 200));
            $this->assertTrue($this->vfsFileCachePath->hasChild($filename));
		}
        
        public function testGetKeyNotExist() {
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
			$this->assertFalse($fc->get('foo'));
		}
        
        public function testGetKeyExist() {
            $key = __FUNCTION__;
            $value = 'bar';
            $filename = md5($key) . '.cache';
            $this->vfsFileCachePath->removeChild($filename);
            
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
			$this->assertTrue($fc->set($key, $value, 200));
            $this->assertTrue($this->vfsFileCachePath->hasChild($filename));
            
            $this->assertSame($value, $fc->get($key));
		}
        
        public function testGetKeyExistButExpired() {
            $key = __FUNCTION__;
            $value = 'bar';
            $filename = md5($key) . '.cache';
            $this->vfsFileCachePath->removeChild($filename);
            
            
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
			$this->assertTrue($fc->set($key, $value, -200));
            $this->assertTrue($this->vfsFileCachePath->hasChild($filename));
            
            $this->assertFalse($fc->get($key));
		}
        
        public function testGetKeyExistButDataCorrupted() {
            $key = __FUNCTION__;
            $value = 'bar';
            $filename = md5($key) . '.cache';
            $this->vfsFileCachePath->removeChild($filename);
            $filepath = $this->vfsFileCachePath->url() . DS . $filename;
            
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
			$this->assertTrue($fc->set($key, $value, 200));
            $this->assertTrue($this->vfsFileCachePath->hasChild($filename));
            
            //simulate corrupted data
            file_put_contents($filepath, 'unserialize data');
            $this->assertFalse($fc->get($key));
		}
        
        public function testDeleteKeyExists() {
            $key = __FUNCTION__;
            $value = 'bar';
            $filename = md5($key) . '.cache';
            $this->vfsFileCachePath->removeChild($filename);
            
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
			$this->assertTrue($fc->set($key, $value, 200));
            $this->assertTrue($this->vfsFileCachePath->hasChild($filename));
            
            $this->assertSame($value, $fc->get($key));
            $this->assertTrue($fc->delete($key));
            $this->assertFalse($fc->get($key));
            $this->assertFalse($this->vfsFileCachePath->hasChild($filename));
		}
        
        public function testDeleteKeyNotExist() {
            $key = __FUNCTION__;
            $value = 'bar';
            $filename = md5($key) . '.cache';
            $this->vfsFileCachePath->removeChild($filename);
            
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
			$this->assertTrue($fc->set($key, $value, 200));
            $this->assertTrue($this->vfsFileCachePath->hasChild($filename));
            
            $this->assertSame($value, $fc->get($key)); 
            $this->assertFalse($fc->delete('foobar'));
            
            $this->assertSame($value, $fc->get($key)); 
            $this->assertTrue($this->vfsFileCachePath->hasChild($filename));
		}
        
        public function testGetInfoKeyExists() {
            $key = __FUNCTION__;
            $value = 'bar';
            $filename = md5($key) . '.cache';
            $this->vfsFileCachePath->removeChild($filename);
            
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
			$this->assertTrue($fc->set($key, $value, 200));
            $this->assertTrue($this->vfsFileCachePath->hasChild($filename));
            
            $info = $fc->getInfo($key);
            $this->assertNotEmpty($info);
            $this->assertArrayHasKey('mtime', $info);
            $this->assertArrayHasKey('expire', $info);
            $this->assertArrayHasKey('ttl', $info);
            $this->assertSame(200, $info['ttl']); 
		}
        
        public function testGetInfoKeyExistsButDataCorrupted() {
            $key = __FUNCTION__;
            $value = 'bar';
            $filename = md5($key) . '.cache';
            $filepath = $this->vfsFileCachePath->url() . DS . $filename;
            
            $this->vfsFileCachePath->removeChild($filename);
            
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
			$this->assertTrue($fc->set($key, $value, 200));
            $this->assertTrue($this->vfsFileCachePath->hasChild($filename));
            
            //simulate corrupted data
            file_put_contents($filepath, 'unserialize data');
            $this->assertFalse($fc->getInfo($key));
		}
        
        public function testGetInfoKeyExistsButExpired() {
            $key = __FUNCTION__;
            $value = 'bar';
            $filename = md5($key) . '.cache';
            $filepath = $this->vfsFileCachePath->url() . DS . $filename;
            
            $this->vfsFileCachePath->removeChild($filename);
            
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
			$this->assertTrue($fc->set($key, $value, -200));
            $this->assertTrue($this->vfsFileCachePath->hasChild($filename));
            
            $this->assertFalse($fc->getInfo($key));
		}
        
        public function testGetInfoKeyNotExist() {
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
            $this->assertFalse($fc->getInfo('foobar'));
		}
        
        public function testDeleteExpiredCacheNoCacheData() {
            $fc = new FileCache($this->vfsFileCachePath->url() . DS);
            $fc->deleteExpiredCache();
            $this->assertTrue(true);
		}
        
        public function testDeleteExpiredCacheDataFound() {
            $this->markTestSkipped('vfsStream not support for function glob');
		}
        
        public function testClean() {
            $this->markTestSkipped('vfsStream not support for function glob');
		}
        
        public function testSetCompressCacheData() {
            $fc = new FileCache();
            $fc->setCompressCacheData(false);
            $this->assertFalse($fc->isCompressCacheData());
            //when extension zlib loaded and param is true
            $fc->setCompressCacheData(true);
            if (extension_loaded('zlib')) {
                $this->assertTrue($fc->isCompressCacheData());
                
            } else {
                $this->assertFalse($fc->isCompressCacheData());
            }   
		}
        
        public function testSetCacheFilePathWhenParamIsNotNull() {
            $fc = new FileCache();
            //will append DIRECTORY_SEPARATOR
            $fc->setCacheFilePath('foo');
            
            $key = __FUNCTION__;
            $expected = 'foo' . DS . $filename = md5($key) . '.cache';
            $ofilepath = $this->runPrivateProtectedMethod($fc, 'getFilePath', array($key));
            $this->assertSame($expected, $ofilepath);
		}
        
        

	}
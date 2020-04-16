<?php 

	/**
     * DatabaseCache class tests
     *
     * @group core
     * @group database
     */
	class DatabaseCacheTest extends TnhTestCase {	
		
		public function testGetSetQuery() {
            $dc = new DatabaseCache();
            $this->assertNull($dc->getQuery());
             
            $query = 'SELECT foo FROM bar';  
            $dc->setQuery($query);
            $this->assertSame($query, $dc->getQuery());
		}
        
        public function testSetReturnType() {
            $returnType = new ReflectionProperty('DatabaseCache', 'returnAsList');
            $returnType->setAccessible(true);
            
            $dc = new DatabaseCache();
            $this->assertTrue($returnType->getValue($dc));
             
            $dc->setReturnType(false);
            $this->assertFalse($returnType->getValue($dc));
		}
        
        public function testSetReturnAsArray() {
            $returnAsArray = new ReflectionProperty('DatabaseCache', 'returnAsArray');
            $returnAsArray->setAccessible(true);
            
            $dc = new DatabaseCache();
            $this->assertTrue($returnAsArray->getValue($dc));
             
            $dc->setReturnAsArray(false);
            $this->assertFalse($returnAsArray->getValue($dc));
		}
        
        public function testSetCacheTimeToLive() {
            $cacheTtl = new ReflectionProperty('DatabaseCache', 'cacheTtl');
            $cacheTtl->setAccessible(true);
            
            $dc = new DatabaseCache();
            $this->assertSame(0, $cacheTtl->getValue($dc));
             
            $dc->setCacheTtl(600);
            $this->assertSame(600, $cacheTtl->getValue($dc));
		}
        
        public function testGetSetCache() {
            $dc = new DatabaseCache();
            $this->assertNull($dc->getCache());
             
            $cache = $this->getMockBuilder('FileCache')
                          ->getMock();
                        
            $dc->setCache($cache);
            $this->assertNotNull($dc->getCache());
            $this->assertInstanceOf('FileCache', $dc->getCache());
		}
        
        public function testGetCacheContentCacheFeatureIsNotEnabled() {
            $query = 'SELECT foo FROM bar';  
            $dc = new DatabaseCache();
            $dc->setQuery($query);
            $this->assertNull($dc->getCacheContent());
		}
        
        public function testGetCacheContentQueryIsNotAnSelect() {
            $query = 'UPDATE foo SET bar = 2';  
            $dc = new DatabaseCache();
            $dc->setQuery($query);
            $this->assertNull($dc->getCacheContent());
		}
        
        public function testGetCacheContentValueIsNull() {
            $query = 'SELECT foo FROM bar';  
            $cache = $this->getMockBuilder('FileCache')
                          ->getMock();
            $cache->expects($this->any())
                 ->method('get')
                 ->will($this->returnValue(null));   
                         
            $dc = new DatabaseCache();
            $dc->setCache($cache);
            $dc->setQuery($query);
            $this->assertNull($dc->getCacheContent());
		}
        
        public function testGetCacheContent() {
            $query = 'SELECT foo FROM bar';  
            $cache = $this->getMockBuilder('FileCache')
                          ->getMock();
                          
            $cache->expects($this->any())
                 ->method('get')
                 ->will($this->returnValue(array('foo')));   
                         
            $dc = new DatabaseCache();
            $dc->setCache($cache);
            
            //enable cache feature
            $this->config->set('cache_enable', true);
            $dc->setCacheTtl(100);
            
            $dc->setQuery($query);
            $this->assertNotEmpty($dc->getCacheContent());
            $this->assertContains('foo', $dc->getCacheContent());
		}
        
        public function testGetCacheContentUsingCacheInstanceFromSuperController() {
            $query = 'SELECT foo FROM bar';  
            $cache = $this->getMockBuilder('FileCache')
                          ->getMock();
                          
            $cache->expects($this->any())
                 ->method('get')
                 ->will($this->returnValue(array('foo'))); 
            $obj = & get_instance();
            $obj->cache = $cache;
                         
            $dc = new DatabaseCache();
            
            //enable cache feature
            $this->config->set('cache_enable', true);
            $dc->setCacheTtl(100);
            
            $dc->setQuery($query);
            $this->assertNotEmpty($dc->getCacheContent());
            $this->assertContains('foo', $dc->getCacheContent());
		}
        
        public function testsetCacheContentCacheFeatureIsNotEnabled() {
            $query = 'SELECT foo FROM bar';  
            $queryResult = array('foo');
            $dc = new DatabaseCache();
            $dc->setQuery($query);
            $this->assertNull($dc->setCacheContent($queryResult));
		}
        
        public function testsetCacheContentQueryIsNotAnSelect() {
            $query = 'UPDATE foo SET bar = 2';  
            $queryResult = array('foo');
            $dc = new DatabaseCache();
            $dc->setQuery($query);
            $this->assertNull($dc->setCacheContent($queryResult));
		}
       
        
        public function testsetCacheContent() {
            $query = 'SELECT foo FROM bar';  
            $queryResult = array('foo');
            $cache = $this->getMockBuilder('FileCache')
                          ->getMock();
                          
            $cache->expects($this->any())
                 ->method('set')
                 ->will($this->returnValue(true));   
                         
            $dc = new DatabaseCache();
            $dc->setCache($cache);
            
            //enable cache feature
            $this->config->set('cache_enable', true);
            $dc->setCacheTtl(100);
            
            $dc->setQuery($query);
            $this->assertTrue($dc->setCacheContent($queryResult));
		}
        
        
	}
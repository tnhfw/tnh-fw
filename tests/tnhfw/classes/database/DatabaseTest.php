<?php 

	/**
     * Database class tests
     *
     * @group core
     * @group database
     */
	class DatabaseTest extends TnhTestCase {	
		
		public function testConstructor() {
            //connection param is null;
            $db = new Database();
            $this->assertNull($db->getConnection());
             
            //Using connection
            $connection = $this->getMockBuilder('DatabaseConnection')
                                ->disableOriginalConstructor()
                                ->getMock();
                        
            $db = new Database($connection);
            $this->assertNotNull($db->getConnection());
            $this->assertInstanceOf('DatabaseConnection', $db->getConnection());
		}
        
        public function testGetSetCacheTimeToLive() {
            $db = new Database();
            
            $cacheTtl  = new ReflectionProperty('Database', 'cacheTtl');
            $cacheTtl ->setAccessible(true);
            
            $temporaryCacheTtl  = new ReflectionProperty('Database', 'temporaryCacheTtl');
            $temporaryCacheTtl ->setAccessible(true);
            
            $this->assertSame(0, $cacheTtl->getValue($db));
            $this->assertSame(0, $temporaryCacheTtl->getValue($db));
            
            $db->setCacheTimeToLive(300);
            
            $this->assertSame(300, $cacheTtl->getValue($db));
            $this->assertSame(300, $temporaryCacheTtl->getValue($db));
            
            $db->cached(100);
            $this->assertSame(300, $cacheTtl->getValue($db));
            $this->assertSame(100, $temporaryCacheTtl->getValue($db));
		}
        
        public function testGetSetConnection() {
            $db = new Database();
            $this->assertNull($db->getConnection());
             
            $connection = $this->getMockBuilder('DatabaseConnection')
                        ->disableOriginalConstructor()
                        ->getMock();
                        
            $db->setConnection($connection);
            $this->assertNotNull($db->getConnection());
            $this->assertInstanceOf('DatabaseConnection', $db->getConnection());
		}
        
        public function testGetsetCache() {
            $db = new Database();
            $this->assertNull($db->getCache());
             
            $cache = $this->getMockBuilder('DatabaseCache')
                          ->getMock();
                        
            $db->setCache($cache);
            $this->assertNotNull($db->getCache());
            $this->assertInstanceOf('DatabaseCache', $db->getCache());
		}
        
        public function testGetSetQueryBuilder() {
            $db = new Database();
            $this->assertNull($db->getQueryBuilder());
             
            $qb = $this->getMockBuilder('DatabaseQueryBuilder')
                          ->getMock();
                        
            $db->setQueryBuilder($qb);
            $this->assertNotNull($db->getQueryBuilder());
            $this->assertInstanceOf('DatabaseQueryBuilder', $db->getQueryBuilder());
		}
        
        public function testGetSetQueryRunner() {
            $db = new Database();
            $this->assertNull($db->getQueryRunner());
             
            $qr = $this->getMockBuilder('DatabaseQueryRunner')
                          ->getMock();
                        
            $db->setQueryRunner($qr);
            $this->assertNotNull($db->getQueryRunner());
            $this->assertInstanceOf('DatabaseQueryRunner', $db->getQueryRunner());
		}
        
        public function testGetSetData() {
            $db = new Database();
            $this->assertEmpty($db->getData());
            
            $pdo = $this->getMockBuilder('PDOMock')
                         ->getMock();
                        
             $pdo->expects($this->any())
                 ->method('quote')
                 ->will($this->returnValue(true));
              
             $connection = $this->getMockBuilder('DatabaseConnection')
                                ->disableOriginalConstructor()
                                ->setMethods(array())
                                ->getMock();
             $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
                        
              $db->setConnection($connection);
                        
             
           //Key is an array
           $data = array('foo' => 'bar', 'bar' => 'foo');
           $db->setData($data);
           $this->assertSame(2, count($db->getData()));
           
            $db = new Database($connection);
           //Key is not an array
           $db->setData('foo', 'bar');
           $this->assertSame(1, count($db->getData()));
		}
        
        public function testGetUsingCache() {
            $result = array('foo' => 'bar');
            $db = new Database();
            
            $cache = $this->getMockBuilder('DatabaseCache')
                          ->setMethods(array('getCacheContent'))
                          ->getMock();
                          
            $cache->expects($this->any())
                 ->method('getCacheContent')
                 ->will($this->returnValue($result)); 
                 
            $qb = $this->getMockBuilder('DatabaseQueryBuilder')
                          ->setMethods(null)
                          ->getMock();
                 
            $db->setCache($cache);
            $db->setQueryBuilder($qb);
              
           $this->assertNotEmpty($db->get());
           $this->assertArrayHasKey('foo', $db->get());
		}
        
        public function testGetUsingRealDatabase() {
            $result = array('foo' => 'bar');
            $db = new Database();
            
            $connection = $this->getMockBuilder('DatabaseConnection')
                        ->disableOriginalConstructor()
                        ->getMock();
            $connection->expects($this->any())
                         ->method('getPrefix')
                         ->will($this->returnValue('')); 
            
            $qb = $this->getMockBuilder('DatabaseQueryBuilder')
                          ->setMethods(null)
                          ->getMock();
                          
            $qb->setConnection($connection);
                        
            $db->setQueryBuilder($qb);
            
            $cache = $this->getMockBuilder('DatabaseCache')
                          ->setMethods(array('getCacheContent'))
                          ->getMock();
                          
            $cache->expects($this->any())
                 ->method('getCacheContent')
                 ->will($this->returnValue(null)); 
                 
            $db->setCache($cache);
            
            $qresult = $this->getMockBuilder('DatabaseQueryResult')
                          ->setMethods(array('getResult', 'getNumRows'))
                          ->getMock();
                          
            $qresult->expects($this->any())
                     ->method('getResult')
                     ->will($this->returnValue($result));

             $qresult->expects($this->any())
                     ->method('getNumRows')
                     ->will($this->returnValue(2));
            
            
            $qr = $this->getMockBuilder('DatabaseQueryRunner')
                      ->setMethods(array('execute'))
                      ->getMock();                          
            $qr->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue($qresult)); 
            $qr->setQueryResult($qresult);
            
            $db->setQueryRunner($qr);
            
            $db->getQueryBuilder()->from('foo');
            
                 
           $this->assertNotEmpty($db->get());
           $this->assertSame('SELECT * FROM foo LIMIT 1', $db->getQuery());
           $this->assertArrayHasKey('foo', $db->get());
           $this->assertNotEmpty($db->get(true));
           $this->assertSame(2, $db->numRows());
        }
        
       
        public function testGetAll() {
            $result = array(array('foo' => 'bar'), array('bar' => 'foo'));
            $db = new Database();
            
            $connection = $this->getMockBuilder('DatabaseConnection')
                        ->disableOriginalConstructor()
                        ->getMock();
            $connection->expects($this->any())
                         ->method('getPrefix')
                         ->will($this->returnValue('')); 
            
            $qb = $this->getMockBuilder('DatabaseQueryBuilder')
                          ->setMethods(null)
                          ->getMock();
                          
            $qb->setConnection($connection);
                        
            $db->setQueryBuilder($qb);
            
            $cache = $this->getMockBuilder('DatabaseCache')
                          ->setMethods(array('getCacheContent'))
                          ->getMock();
                          
            $cache->expects($this->any())
                 ->method('getCacheContent')
                 ->will($this->returnValue(null)); 
                 
            $db->setCache($cache);
            
            $qresult = $this->getMockBuilder('DatabaseQueryResult')
                          ->setMethods(array('getResult', 'getNumRows'))
                          ->getMock();
                          
            $qresult->expects($this->any())
                     ->method('getResult')
                     ->will($this->returnValue($result));

             $qresult->expects($this->any())
                     ->method('getNumRows')
                     ->will($this->returnValue(24));
            
            
            $qr = $this->getMockBuilder('DatabaseQueryRunner')
                      ->setMethods(array('execute'))
                      ->getMock();                          
            $qr->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue($qresult)); 
            $qr->setQueryResult($qresult);
            
            $db->setQueryRunner($qr);
            
            $db->getQueryBuilder()->from('foo');
            
                 
           $this->assertNotEmpty($db->getAll());
           $this->assertSame('SELECT * FROM foo', $db->getQuery());
           $this->assertSame(2, count($db->getAll()));
           $this->assertNotEmpty($db->getAll());
           $this->assertSame(24, $db->numRows());  
		}
        
        
        
        public function testInsert() {
            $data = array('foo' => 'bar');
            
            $db = new Database();
            
            $pdo = $this->getMockBuilder('PDOMock')
                         ->getMock();
                        
             $pdo->expects($this->any())
                 ->method('lastInsertId')
                 ->will($this->returnValue(17));
                 
            $connection = $this->getMockBuilder('DatabaseConnection')
                                ->getMock();
           
            $connection->expects($this->any())
                        ->method('escape')
                        ->will($this->returnValue("'bar'"));
                        
            $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
            
            $qb = $this->getMockBuilder('DatabaseQueryBuilder')
                          ->setMethods(null)
                          ->getMock();
                          
            $qb->setConnection($connection);
                        
            $db->setQueryBuilder($qb);
            
            $db->setConnection($connection);
            
            
            $qresult = $this->getMockBuilder('DatabaseQueryResult')
                              ->setMethods(array('getResult', 'getNumRows'))
                              ->getMock();
                          
            $qresult->expects($this->any())
                     ->method('getResult')
                     ->will($this->returnValue(17));

             $qresult->expects($this->any())
                     ->method('getNumRows')
                     ->will($this->returnValue(1991));
            
            
            $qr = $this->getMockBuilder('DatabaseQueryRunner')
                      ->setMethods(array('execute'))
                      ->getMock();                          
            $qr->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue($qresult)); 
            $qr->setQueryResult($qresult);
            
            $db->setQueryRunner($qr);

            $db->setData($data);
            
            $cache = $this->getMockCacheInstanceReturnNull();
                        
            $db->setCache($cache);
           
            $db->getQueryBuilder()->from('foo');
            $this->assertSame(17, $db->insert());  
            $this->assertSame(17, $db->insertId());  
            $this->assertSame(1, $db->queryCount());  
		}
        
        
        public function testInsertNoInsertId() {
            $data = array('foo' => 'bar');
            
            $db = new Database();
            
            $pdo = $this->getMockBuilder('PDOMock')
                         ->getMock();
                        
             $pdo->expects($this->any())
                 ->method('lastInsertId')
                 ->will($this->returnValue(null));
                 
            $connection = $this->getMockBuilder('DatabaseConnection')
                                ->getMock();
           
            $connection->expects($this->any())
                        ->method('escape')
                        ->will($this->returnValue("'bar'"));
                        
            $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
            
            $qb = $this->getMockBuilder('DatabaseQueryBuilder')
                          ->setMethods(null)
                          ->getMock();
                          
            $qb->setConnection($connection);
                        
            $db->setQueryBuilder($qb);
            
            $db->setConnection($connection);
            
            
            $qresult = $this->getMockBuilder('DatabaseQueryResult')
                              ->setMethods(array('getResult', 'getNumRows'))
                              ->getMock();
                          
            $qresult->expects($this->any())
                     ->method('getResult')
                     ->will($this->returnValue(17));

             $qresult->expects($this->any())
                     ->method('getNumRows')
                     ->will($this->returnValue(1991));
            
            
            $qr = $this->getMockBuilder('DatabaseQueryRunner')
                      ->setMethods(array('execute'))
                      ->getMock();                          
            $qr->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue($qresult)); 
            $qr->setQueryResult($qresult);
            
            $db->setQueryRunner($qr);

            $db->setData($data);
            
            $cache = $this->getMockCacheInstanceReturnNull();
            $db->setCache($cache);
           
            $db->getQueryBuilder()->from('foo');
            $this->assertTrue($db->insert());  
		}
        
        public function testInsertQueryHasError() {
            $data = array('foo' => 'bar');
            
            $db = new Database();
            
            $pdo = $this->getMockBuilder('PDOMock')
                         ->getMock();
                        
             $pdo->expects($this->any())
                 ->method('lastInsertId')
                 ->will($this->returnValue(17));
                 
            $connection = $this->getMockBuilder('DatabaseConnection')
                                ->getMock();
           
            $connection->expects($this->any())
                        ->method('escape')
                        ->will($this->returnValue("'bar'"));
                        
            $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
            
            $qb = $this->getMockBuilder('DatabaseQueryBuilder')
                          ->setMethods(null)
                          ->getMock();
                          
            $qb->setConnection($connection);
                        
            $db->setQueryBuilder($qb);
            
            $db->setConnection($connection);
            
            
            $qresult = $this->getMockBuilder('DatabaseQueryResult')
                              ->setMethods(array('getResult', 'getNumRows'))
                              ->getMock();
                          
            $qresult->expects($this->any())
                     ->method('getResult')
                     ->will($this->returnValue(17));

             $qresult->expects($this->any())
                     ->method('getNumRows')
                     ->will($this->returnValue(1991));
            
            
            $qr = $this->getMockBuilder('DatabaseQueryRunner')
                      ->setMethods(array('execute'))
                      ->getMock();                          
            $qr->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue(null)); 
            $qr->setQueryResult($qresult);
            
            $db->setQueryRunner($qr);

            $db->setData($data);
            
             $cache = $this->getMockCacheInstanceReturnNull();
            $db->setCache($cache);
           
            $db->getQueryBuilder()->from('foo');
            $this->assertFalse($db->insert());  
		}
        
         public function testUpdate() {
            $data = array('foo' => 'bar');
            
            $db = new Database();
            
            $pdo = $this->getMockBuilder('PDOMock')
                         ->getMock();
                        
            
            $connection = $this->getMockBuilder('DatabaseConnection')
                                ->getMock();
           
            $connection->expects($this->any())
                        ->method('escape')
                        ->will($this->returnValue("'bar'"));
                        
            $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
            
            $qb = $this->getMockBuilder('DatabaseQueryBuilder')
                          ->setMethods(null)
                          ->getMock();
                          
            $qb->setConnection($connection);
                        
            $db->setQueryBuilder($qb);
            
            $db->setConnection($connection);
            
            
            $qresult = $this->getMockBuilder('DatabaseQueryResult')
                              ->setMethods(array('getResult', 'getNumRows'))
                              ->getMock();
                          
            $qresult->expects($this->any())
                     ->method('getResult')
                     ->will($this->returnValue(true));

             $qresult->expects($this->any())
                     ->method('getNumRows')
                     ->will($this->returnValue(1));
            
            
            $qr = $this->getMockBuilder('DatabaseQueryRunner')
                      ->setMethods(array('execute'))
                      ->getMock();                          
            $qr->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue($qresult)); 
            $qr->setQueryResult($qresult);
            
            $db->setQueryRunner($qr);

            $db->setData($data);
            
            $cache = $this->getMockCacheInstanceReturnNull();
            $db->setCache($cache);
           
            $db->getQueryBuilder()->from('foo');
            $this->assertTrue($db->update());  
		}
        
        public function testDelete() {
            $db = new Database();
            
            $pdo = $this->getMockBuilder('PDOMock')
                         ->getMock();
                        
            
            $connection = $this->getMockBuilder('DatabaseConnection')
                                ->getMock();
           
            $qb = $this->getMockBuilder('DatabaseQueryBuilder')
                          ->setMethods(null)
                          ->getMock();
                          
            $qb->setConnection($connection);
                        
            $db->setQueryBuilder($qb);
            
            $db->setConnection($connection);
            
            
            $qresult = $this->getMockBuilder('DatabaseQueryResult')
                              ->setMethods(array('getResult', 'getNumRows'))
                              ->getMock();
                          
            $qresult->expects($this->any())
                     ->method('getResult')
                     ->will($this->returnValue(true));

             $qresult->expects($this->any())
                     ->method('getNumRows')
                     ->will($this->returnValue(1));
            
            
            $qr = $this->getMockBuilder('DatabaseQueryRunner')
                      ->setMethods(array('execute'))
                      ->getMock();                          
            $qr->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue($qresult)); 
            $qr->setQueryResult($qresult);
            
            $db->setQueryRunner($qr);
            
            $cache = $this->getMockCacheInstanceReturnNull();
            $db->setCache($cache);

            $db->getQueryBuilder()->from('foo');
            $this->assertTrue($db->delete());  
		}
        
        
        /**
        * Mock of Database cache instance that will return null value
        */
        private function getMockCacheInstanceReturnNull() {
            $cache = $this->getMockBuilder('DatabaseCache')
                          ->getMock();
            
            $cache->expects($this->any())
                 ->method('setQuery')
                 ->will($this->returnValue($cache)); 
                 
            $cache->expects($this->any())
                 ->method('setReturnType')
                 ->will($this->returnValue($cache)); 
                 
            $cache->expects($this->any())
                 ->method('setReturnAsArray')
                 ->will($this->returnValue($cache)); 
            
            $cache->expects($this->any())
                 ->method('setCacheTtl')
                 ->will($this->returnValue($cache)); 
                 
             $cache->expects($this->any())
                 ->method('getCacheContent')
                 ->will($this->returnValue(null)); 
              return $cache;    
        }
        

	}
<?php 

	/**
     * DatabaseQueryRunner class tests
     *
     * @group core
     * @group database
     */
	class DatabaseQueryRunnerTest extends TnhTestCase {	
	
		
                        
		public function testConstructor() {
            //connection is null;
            $qr = new DatabaseQueryRunner();
            $this->assertNull($qr->getConnection());
             
            //Using connection
            $connection = $this->getMockBuilder('DatabaseConnection')
                        ->disableOriginalConstructor()
                        ->getMock();
                        
            $qr = new DatabaseQueryRunner($connection);
            $this->assertNotNull($qr->getConnection());
            $this->assertInstanceOf('DatabaseConnection', $qr->getConnection());
		}
        
        public function testGetSetConnection() {
            $qr = new DatabaseQueryRunner();
            $this->assertNull($qr->getConnection());
             
            $connection = $this->getMockBuilder('DatabaseConnection')
                        ->disableOriginalConstructor()
                        ->getMock();
                        
            $qr->setConnection($connection);
            $this->assertNotNull($qr->getConnection());
            $this->assertInstanceOf('DatabaseConnection', $qr->getConnection());
		}
        
        public function testGetSetBenchmark() {
            $qr = new DatabaseQueryRunner();
            //Note Benchmark instance is already set in constructor
            $qr->setBenchmark(null);
            $this->assertNull($qr->getBenchmark());
             
            $benchmark = $this->getMockBuilder('Benchmark')
                        ->getMock();
                        
            $qr->setBenchmark($benchmark);
            $this->assertNotNull($qr->getBenchmark());
            $this->assertInstanceOf('Benchmark', $qr->getBenchmark());
		}
        
        public function testGetSetQueryResult() {
            $qr = new DatabaseQueryRunner();
            //Note Query Result instance is already set in constructor
            $qr->setQueryResult(null);
            $this->assertNull($qr->getQueryResult());
             
            $queryResult = $this->getMockBuilder('DatabaseQueryResult')
                        ->disableOriginalConstructor()
                        ->getMock();
                        
            $qr->setQueryResult($queryResult);
            $this->assertNotNull($qr->getQueryResult());
            $this->assertInstanceOf('DatabaseQueryResult', $qr->getQueryResult());
		}
        
        public function testGetSetQuery() {
            $qr = new DatabaseQueryRunner();
            $this->assertNull($qr->getQuery());
             
            $query = 'SELECT foo FROM bar';  
            $qr->setQuery($query);
            $this->assertSame($query, $qr->getQuery());
		}
        
        public function testSetReturnType() {
            $returnType = new ReflectionProperty('DatabaseQueryRunner', 'returnAsList');
            $returnType->setAccessible(true);
            
            $qr = new DatabaseQueryRunner();
            $this->assertTrue($returnType->getValue($qr));
             
            $qr->setReturnType(false);
            $this->assertFalse($returnType->getValue($qr));
		}
        
        public function testSetReturnAsArray() {
            $returnAsArray = new ReflectionProperty('DatabaseQueryRunner', 'returnAsArray');
            $returnAsArray->setAccessible(true);
            
            $qr = new DatabaseQueryRunner();
            $this->assertTrue($returnAsArray->getValue($qr));
             
            $qr->setReturnAsArray(false);
            $this->assertFalse($returnAsArray->getValue($qr));
		}
        
         public function testExecuteGetSingleRecord() {
             $queryStr = 'SELECT foo FROM bar';
             $dataResult = array('foo' => 'bar');
             
             $pdoStatment = $this->getMockBuilder('PDOStatement')
                                 ->getMock();
             $pdoStatment->expects($this->any())
                         ->method('rowCount')
                         ->will($this->returnValue(1));
                         
             $pdoStatment->expects($this->any())
                         ->method('fetch')
                         ->will($this->returnValue($dataResult));            
             
             $pdo = $this->getMockBuilder('PDO')
                         ->disableOriginalConstructor()
                         ->getMock();
                        
             $pdo->expects($this->any())
                 ->method('query')
                 ->will($this->returnValue($pdoStatment));
              
             $connection = $this->getMockBuilder('DatabaseConnection')
                                ->disableOriginalConstructor()
                                ->setMethods(array())
                                ->getMock();
             $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
                        
             $connection->expects($this->any())
                        ->method('getDriver')
                        ->will($this->returnValue('mysql'));
                        
            $benchmark = $this->getMockBuilder('Benchmark')->getMock();
                        
            $qr = new DatabaseQueryRunner($connection);
            $qr->setBenchmark($benchmark);
            
            $queryResult = $this->getMockBuilder('DatabaseQueryResult')
                        ->disableOriginalConstructor()
                        ->setMethods(array())
                        ->getMock();
                        
            $qr->setQueryResult(new DatabaseQueryResult());
            
            $qr->setReturnAsArray(true);
            $qr->setReturnType(false);
            $qr->setQuery($queryStr);
            $result = $qr->execute();
            $this->assertNotNull($result);
            $this->assertInstanceOf('DatabaseQueryResult', $result);
            $this->assertArrayHasKey('foo', $result->getResult());
            $this->assertSame(1, $result->getNumRows());
		}
        
        public function testExecuteGetListOfRecord() {
             $queryStr = 'SELECT foo FROM bar';
             $dataResult = array(array('foo' => 'bar'), array('bar' => 'foo'));
             
             $pdoStatment = $this->getMockBuilder('PDOStatement')
                                 ->getMock();
             $pdoStatment->expects($this->any())
                         ->method('rowCount')
                         ->will($this->returnValue(2));
                         
             $pdoStatment->expects($this->any())
                         ->method('fetchAll')
                         ->will($this->returnValue($dataResult));            
             
             $pdo = $this->getMockBuilder('PDO')
                         ->disableOriginalConstructor()
                         ->getMock();
                        
             $pdo->expects($this->any())
                 ->method('query')
                 ->will($this->returnValue($pdoStatment));
              
             $connection = $this->getMockBuilder('DatabaseConnection')
                                ->disableOriginalConstructor()
                                ->setMethods(array())
                                ->getMock();
             $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
                        
             $connection->expects($this->any())
                        ->method('getDriver')
                        ->will($this->returnValue('mysql'));
                        
            $benchmark = $this->getMockBuilder('Benchmark')->getMock();
                        
            $qr = new DatabaseQueryRunner($connection);
            $qr->setBenchmark($benchmark);
            
            $queryResult = $this->getMockBuilder('DatabaseQueryResult')
                        ->disableOriginalConstructor()
                        ->setMethods(array())
                        ->getMock();
                        
            $qr->setQueryResult(new DatabaseQueryResult());
            
            $qr->setReturnAsArray(true);
            $qr->setReturnType(true);
            $qr->setQuery($queryStr);
            $result = $qr->execute();
            $this->assertNotNull($result);
            $this->assertInstanceOf('DatabaseQueryResult', $result);
            $this->assertSame(2, count($result->getResult()));
            $this->assertSame(2, $result->getNumRows());
		}
        
        public function testExecuteGetListOfRecordForSqliteOrPostgre() {
             $queryStr = 'SELECT foo FROM bar';
             $dataResult = array(array('foo' => 'bar'), array('bar' => 'foo'));
             
             $pdoStatment = $this->getMockBuilder('PDOStatement')
                                 ->getMock();
             $pdoStatment->expects($this->never())
                         ->method('rowCount');
                         
                         
             $pdoStatment->expects($this->any())
                         ->method('fetchAll')
                         ->will($this->returnValue($dataResult));            
             
             $pdo = $this->getMockBuilder('PDO')
                         ->disableOriginalConstructor()
                         ->getMock();
                        
             $pdo->expects($this->any())
                 ->method('query')
                 ->will($this->returnValue($pdoStatment));
              
             $connection = $this->getMockBuilder('DatabaseConnection')
                                ->disableOriginalConstructor()
                                ->setMethods(array())
                                ->getMock();
             $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
                        
             $connection->expects($this->any())
                        ->method('getDriver')
                        ->will($this->returnValue('pgsql'));
                        
            $benchmark = $this->getMockBuilder('Benchmark')->getMock();
                        
            $qr = new DatabaseQueryRunner($connection);
            $qr->setBenchmark($benchmark);
            
            $queryResult = $this->getMockBuilder('DatabaseQueryResult')
                        ->disableOriginalConstructor()
                        ->setMethods(array())
                        ->getMock();
                        
            $qr->setQueryResult(new DatabaseQueryResult());
            
            $qr->setReturnAsArray(true);
            $qr->setReturnType(true);
            $qr->setQuery($queryStr);
            $result = $qr->execute();
            $this->assertNotNull($result);
            $this->assertInstanceOf('DatabaseQueryResult', $result);
            $this->assertSame(2, count($result->getResult()));
            $this->assertSame(2, $result->getNumRows());
		}
        
        public function testExecuteNonSelectQuery() {
             $queryStr = 'DELETE FROM foo';
             $dataResult = true;
             
             $pdoStatment = $this->getMockBuilder('PDOStatement')
                                 ->getMock();
             $pdoStatment->expects($this->any())
                         ->method('rowCount')
                         ->will($this->returnValue(1));
                         
             $pdo = $this->getMockBuilder('PDO')
                         ->disableOriginalConstructor()
                         ->getMock();
                        
             $pdo->expects($this->any())
                 ->method('query')
                 ->will($this->returnValue($pdoStatment));
              
             $connection = $this->getMockBuilder('DatabaseConnection')
                                ->disableOriginalConstructor()
                                ->setMethods(array())
                                ->getMock();
             $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
                        
             $connection->expects($this->any())
                        ->method('getDriver')
                        ->will($this->returnValue('oracle'));
                        
            $benchmark = $this->getMockBuilder('Benchmark')->getMock();
                        
            $qr = new DatabaseQueryRunner($connection);
            $qr->setBenchmark($benchmark);
            
            $queryResult = $this->getMockBuilder('DatabaseQueryResult')
                        ->disableOriginalConstructor()
                        ->setMethods(array())
                        ->getMock();
                        
            $qr->setQueryResult(new DatabaseQueryResult());
            
            $qr->setReturnAsArray(true);
            $qr->setReturnType(true);
            $qr->setQuery($queryStr);
            $result = $qr->execute();
            $this->assertNotNull($result);
            $this->assertInstanceOf('DatabaseQueryResult', $result);
            $this->assertTrue($result->getResult());
            $this->assertSame(1, $result->getNumRows());
		}
        
        
        public function testExecuteNonSelectQueryForSqliteOrPostgre() {
             $queryStr = 'DELETE FROM foo';
             $dataResult = true;
             
             $pdoStatment = $this->getMockBuilder('PDOStatement')
                                 ->getMock();
             $pdoStatment->expects($this->never())
                         ->method('rowCount');
                         
             $pdo = $this->getMockBuilder('PDO')
                         ->disableOriginalConstructor()
                         ->getMock();
                        
             $pdo->expects($this->any())
                 ->method('query')
                 ->will($this->returnValue($pdoStatment));
              
             $connection = $this->getMockBuilder('DatabaseConnection')
                                ->disableOriginalConstructor()
                                ->setMethods(array())
                                ->getMock();
             $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
                        
             $connection->expects($this->any())
                        ->method('getDriver')
                        ->will($this->returnValue('sqlite'));
                        
            $benchmark = $this->getMockBuilder('Benchmark')->getMock();
                        
            $qr = new DatabaseQueryRunner($connection);
            $qr->setBenchmark($benchmark);
            
            $queryResult = $this->getMockBuilder('DatabaseQueryResult')
                        ->disableOriginalConstructor()
                        ->setMethods(array())
                        ->getMock();
                        
            $qr->setQueryResult(new DatabaseQueryResult());
            
            $qr->setReturnAsArray(true);
            $qr->setReturnType(true);
            $qr->setQuery($queryStr);
            $result = $qr->execute();
            $this->assertNotNull($result);
            $this->assertInstanceOf('DatabaseQueryResult', $result);
            $this->assertTrue($result->getResult());
            $this->assertSame(1, $result->getNumRows());
		}
        
         public function testExecuteHighResponseTime() {
             $queryStr = 'SELECT foo FROM bar';
             $dataResult = array(array('foo' => 'bar'), array('bar' => 'foo'));
             
             $pdoStatment = $this->getMockBuilder('PDOStatement')
                                 ->getMock();
             $pdoStatment->expects($this->any())
                         ->method('rowCount')
                         ->will($this->returnValue(2));
                         
             $pdoStatment->expects($this->any())
                         ->method('fetchAll')
                         ->will($this->returnValue($dataResult));            
             
             $pdo = $this->getMockBuilder('PDO')
                         ->disableOriginalConstructor()
                         ->getMock();
                        
             $pdo->expects($this->any())
                 ->method('query')
                 ->will($this->returnValue($pdoStatment));
              
             $connection = $this->getMockBuilder('DatabaseConnection')
                                ->disableOriginalConstructor()
                                ->setMethods(array())
                                ->getMock();
             $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
                        
             $connection->expects($this->any())
                        ->method('getDriver')
                        ->will($this->returnValue('mysql'));
                        
            $benchmark = $this->getMockBuilder('Benchmark')->getMock();
            $benchmark->expects($this->any())
                        ->method('elapsedTime')
                        ->will($this->returnValue(677.988));
                        
            $qr = new DatabaseQueryRunner($connection);
            $qr->setBenchmark($benchmark);
            
            $queryResult = $this->getMockBuilder('DatabaseQueryResult')
                        ->disableOriginalConstructor()
                        ->setMethods(array())
                        ->getMock();
                        
            $qr->setQueryResult(new DatabaseQueryResult());
            
            $qr->setReturnAsArray(true);
            $qr->setReturnType(true);
            $qr->setQuery($queryStr);
            $result = $qr->execute();
            $this->assertNotNull($result);
            $this->assertInstanceOf('DatabaseQueryResult', $result);
            $this->assertSame(2, count($result->getResult()));
            $this->assertSame(2, $result->getNumRows());
		}
        
        
        public function testExecuteQueryGotError() {
             $queryStr = 'SELECT foo FROM bar';
             $dataResult = array(array('foo' => 'bar'), array('bar' => 'foo'));
             
             $pdoStatment = $this->getMockBuilder('PDOStatement')
                                 ->getMock();
             $pdoStatment->expects($this->any())
                         ->method('rowCount')
                         ->will($this->returnValue(2));
                         
             $pdoStatment->expects($this->any())
                         ->method('fetchAll')
                         ->will($this->returnValue($dataResult));            
             
             $pdo = $this->getMockBuilder('PDO')
                         ->disableOriginalConstructor()
                         ->getMock();
                        
             $pdo->expects($this->any())
                 ->method('query')
                 ->will($this->returnValue(false));
                 
              $pdo->expects($this->any())
                 ->method('errorInfo')
                 ->will($this->returnValue(array('S0000', '1064', 'synthax error')));
              
             $connection = $this->getMockBuilder('DatabaseConnection')
                                ->disableOriginalConstructor()
                                ->setMethods(array())
                                ->getMock();
             $connection->expects($this->any())
                        ->method('getPdo')
                        ->will($this->returnValue($pdo));
                        
             $connection->expects($this->any())
                        ->method('getDriver')
                        ->will($this->returnValue('mysql'));
                        
            $benchmark = $this->getMockBuilder('Benchmark')->getMock();
            $benchmark->expects($this->any())
                        ->method('elapsedTime')
                        ->will($this->returnValue(677.988));
                        
            $qr = new DatabaseQueryRunner($connection);
            $qr->setBenchmark($benchmark);
            
            $queryResult = $this->getMockBuilder('DatabaseQueryResult')
                        ->disableOriginalConstructor()
                        ->setMethods(array())
                        ->getMock();
                        
            $qr->setQueryResult(new DatabaseQueryResult());
            
            $qr->setReturnAsArray(true);
            $qr->setReturnType(true);
            $qr->setQuery($queryStr);
            $result = $qr->execute();
            $this->assertNull($result);
            $this->assertNotEmpty($qr->getQueryError());
		}
        
        
       

	}
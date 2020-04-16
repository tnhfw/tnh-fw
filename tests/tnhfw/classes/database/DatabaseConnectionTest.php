<?php 

	/**
     * DatabaseConnection class tests
     *
     * @group core
     * @group database
     */
	class DatabaseConnectionTest extends TnhTestCase {	
		
		public function testConnectToDatabaseSuccessfully() {
            $cfg = $this->getDbConfig();
            //Don't connect automatically
            $cnx = new DatabaseConnection($cfg, false);
            $this->assertTrue($cnx->connect());
            
            //auto connect
            $cnx = new DatabaseConnection($cfg, true);
            $this->assertNotNull($cnx->getPdo());
            $this->assertInstanceOf('PDO', $cnx->getPdo());
		}
        
        public function testCannotConnectToDatabase() {
             $cnx = new DatabaseConnection(array(
                                  'driver' => '',
                                  'username' => '',
                                  'password' => '',
                                  'database' => '',
                                  'hostname' => '',
                                  'charset' => '',
                                  'collation' => '',
                                  'prefix' => '',
                                  'port' => ''
                                ), 
                                false);
             $this->assertFalse($cnx->connect());
		}
        
        public function testDisconnectToDatabase() {
            $cfg = $this->getDbConfig();
            $cnx = new DatabaseConnection($cfg, false);
            $this->assertTrue($cnx->connect());
            $this->assertNotNull($cnx->getPdo());
            $this->assertInstanceOf('PDO', $cnx->getPdo());
            $cnx->disconnect();
            $this->assertNull($cnx->getPdo());
		}
        
        public function testSetPdo() {
            $pdo = $this->getMockBuilder('PDOMock')
                        ->getMock();
                        
            $cnx = new DatabaseConnection(array(), false);
            $this->assertNull($cnx->getPdo());
            $cnx->setPdo($pdo);
            $this->assertNotNull($cnx->getPdo());
            $this->assertInstanceOf('PDO', $cnx->getPdo());
		}
        
         public function testSetCollation() {
            $cnx = new DatabaseConnection(array(), false);
            $this->assertNull($cnx->getCollation());
            $cnx->setCollation('utf8_general_ci');
            $this->assertSame('utf8_general_ci', $cnx->getCollation());
		}
        
        public function testSetPrefix() {
            $cnx = new DatabaseConnection(array(), false);
            $this->assertNull($cnx->getPrefix());
            $cnx->setPrefix('pf_');
            $this->assertSame('pf_', $cnx->getPrefix());
		}
        
        public function testSetGetConfig() {
            $cnx = new DatabaseConnection(array(), false);
            $this->assertEmpty($cnx->getConfig());
            $config = array('driver' => 'foo');
            $cnx->setConfig($config);
            $this->assertNotEmpty($cnx->getConfig());
            $this->assertSame(1, count($cnx->getConfig()));
            $this->assertArrayHasKey('driver', $cnx->getConfig());
		}
        
        
        
        public function testEscapeData() {
            $cfg = $this->getDbConfig();
            $cnx = new DatabaseConnection($cfg, false);
            $this->assertTrue($cnx->connect());
            
            $data = 'foo';
            //no need escape
            $expected = "foo";
            $this->assertSame($expected, $cnx->escape($data, false));
            
            //need escape
            $expected = "'foo'";
            $this->assertSame($expected, $cnx->escape($data, true));
            
            //need escape with quote in value
            $data = "fo'o";
            $expected = "'fo''o'";
            $this->assertSame($expected, $cnx->escape($data, true));
            
            //need escape with double quote in value
            $data = 'fo"o';
            $expected = "'fo\"o'";
            $this->assertSame($expected, $cnx->escape($data, true));
		}
        
        public function testGetDsn() {
            
            //Using standard port
            $config = array(
                              'driver' => 'sqlite',
                              'username' => 'foouser',
                              'password' => 'barpassword',
                              'database' => 'bazdb',
                              'hostname' => 'mydbhostname',
                              'charset' => 'utf8',
                              'collation' => '',
                              'prefix' => '',
                              'port' => ''
                            );
            $cnx = new DatabaseConnection($config, false);
            //Invalid DSN
            $expected = null;
            $cnx->setDriver('foodriver');
            $this->assertEmpty($cnx->getDsn());
            
            //Sqlite
            $expected = 'sqlite:bazdb';
            $cnx->setDriver('sqlite');
            $this->assertSame($expected, $cnx->getDsn());
            
            //MySQL
            $expected = 'mysql:host=mydbhostname;dbname=bazdb;charset=utf8';
            $cnx->setDriver('mysql');
            $this->assertSame($expected, $cnx->getDsn());
            
            //PostgreSQL
            $expected = 'pgsql:host=mydbhostname;dbname=bazdb;charset=utf8';
            $cnx->setDriver('pgsql');
            $this->assertSame($expected, $cnx->getDsn());
            
            //Oracle
            $expected = 'oci:dbname=mydbhostname/bazdb;charset=utf8';
            $cnx->setDriver('oracle');
            $this->assertSame($expected, $cnx->getDsn());
            
            
            //Using custom port
            $config = array(
                              'driver' => 'mysql',
                              'username' => 'foouser',
                              'password' => 'barpassword',
                              'database' => 'bazdb',
                              'hostname' => 'mydbhostname:46575',
                              'charset' => 'utf8',
                              'collation' => '',
                              'prefix' => '',
                              'port' => ''
                            );
            $cnx = new DatabaseConnection($config, false);
            
            //MySQL
            $expected = 'mysql:host=mydbhostname;port=46575;dbname=bazdb;charset=utf8';
            $cnx->setDriver('mysql');
            $this->assertSame($expected, $cnx->getDsn());
            
            //PostgreSQL
            $expected = 'pgsql:host=mydbhostname;port=46575;dbname=bazdb;charset=utf8';
            $cnx->setDriver('pgsql');
            $this->assertSame($expected, $cnx->getDsn());
            
            //Oracle
            $expected = 'oci:dbname=mydbhostname:46575/bazdb;charset=utf8';
            $cnx->setDriver('oracle');
            $this->assertSame($expected, $cnx->getDsn());
		}

	}
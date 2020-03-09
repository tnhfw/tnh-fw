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
            $db = new DatabaseConnection($cfg, false);
            $isConnected = $db->connect();
            $this->assertTrue($isConnected);
		}
        
        public function testCannotConnectToDatabase() {
             $db = new DatabaseConnection(array(
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
             $isConnected = $db->connect();
			$this->assertFalse($isConnected);
		}

	}
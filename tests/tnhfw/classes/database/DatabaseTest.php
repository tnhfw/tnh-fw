<?php 

	/**
     * Database class tests
     *
     * @group core
     * @group database
     */
	class DatabaseTest extends TnhTestCase {	
		
		public function testConnectToDatabaseSuccessfully() {
            $cfg = $this->getDbConfig();
            $db = new Database($cfg, false);
            $isConnected = $db->connect();
            $this->assertTrue($isConnected);
		}
        
        public function testCannotConnectToDatabase() {
             $db = new Database(array(
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
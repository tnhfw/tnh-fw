<?php 

	
	class DatabaseTest extends TnhTestCase
	{	
	
		public static function setUpBeforeClass()
		{
		
		}
		
		public static function tearDownAfterClass()
		{
			
		}
		
		protected function setUp()
		{
		}

		protected function tearDown()
		{
		}
		
		public function testConnectToDatabaseSuccessfully()
		{
            $cfg = $this->getDbConfig();
            $db = new Database($cfg, false);
            $isConnected = $db->connect();
            $this->assertTrue($isConnected);
		}
        
        public function testCannotConnectToDatabase()
		{
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
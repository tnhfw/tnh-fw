<?php 

    /**
     * DBSessionHandler class tests
     *
     * @group core
     * @group core_classes
     * @group session
     */
	class DBSessionHandlerTest extends TnhTestCase {	
	
		private $db = null;
		
		private $model = null;
		
		private $secret = 'bXlzZWNyZXQ';
		
		public function __construct(){
            parent::__construct();  
            $this->db = $this->getDbInstanceForTest();
		}
		
		public static function setUpBeforeClass() {
			require APPS_MODEL_PATH . 'DBSessionModel.php';
		}
		
		protected function setUp() {
            parent::setUp();
            $this->model = new DBSessionModel($this->db);
            
            //to prevent old data conflict
			$this->model->truncate();
		}

		
		public function testUsingSessionConfiguration(){
            //using value in the configuration
			$this->config->set('session_save_path', 'DBSessionModel');
			$this->config->set('session_secret', $this->secret);
			$dbsh = new DBSessionHandler();
			//assign Database instance manually
			$o = &get_instance();
			$o->database = $this->db;
            
            $this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertEmpty($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//do update of existing data
			$this->assertTrue($dbsh->write('foo', '445'));
			$this->assertEquals($dbsh->read('foo'), '445');	
			$this->assertTrue($dbsh->destroy('foo'));
			$this->assertNull($dbsh->read('foo'));
			$this->assertTrue($dbsh->gc(13));
			$encoded = $this->runPrivateProtectedMethod($dbsh, 'encode', array('foo'));
			$this->assertNotEmpty($encoded);
            $decoded = $this->runPrivateProtectedMethod($dbsh, 'decode', array($encoded));
			$this->assertEquals($decoded, 'foo');
		}
		
        
		public function testWhenDataIsExpired(){
			$dbsh = new DBSessionHandler($this->model);
			$dbsh->setSessionSecret($this->secret);
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//put it in expired
			$this->model->update('foo', array('s_time' => 1234567));
			$this->assertEmpty($dbsh->read('foo'));
		}
		
		public function testWhenDataAlreadyExistDoUpdate(){
			$dbsh = new DBSessionHandler($this->model);
			$dbsh->setSessionSecret($this->secret);
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//do update of existing data
			$this->assertTrue($dbsh->write('foo', '445'));
			$this->assertEquals($dbsh->read('foo'), '445');	
		}
		
		public function testUsingCustomModelInstance(){
			$dbsh = new DBSessionHandler($this->model);
			$dbsh->setSessionSecret($this->secret);
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertEmpty($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//put it in expired
			$this->model->update('foo', array('s_time' => 1234567));
			
			$this->assertNull($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			
			//do update of existing data
			$this->assertTrue($dbsh->write('foo', '445'));
			$this->assertEquals($dbsh->read('foo'), '445');	
			$this->assertTrue($dbsh->destroy('foo'));
			$this->assertNull($dbsh->read('foo'));
			$this->assertTrue($dbsh->gc(13));
		}
		
        
		public function testWhenModelInsanceIsNotSet(){
            $this->config->set('session_save_path', 'DBSessionModel');
            //assign Database instance manually
			$o = &get_instance();
			$o->database = $this->db;
            
			$dbsh = new DBSessionHandler();
			$dbsh->setSessionSecret($this->secret);
           
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertEmpty($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//put it in expired
			$this->model->update('foo', array('s_time' => 1234567));
			
			$this->assertNull($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			
			//do update of existing data
			$this->assertTrue($dbsh->write('tnh', '445'));
			$this->assertTrue($dbsh->write('foo', '445'));
			$this->assertEquals($dbsh->read('foo'), '445');	
			$this->assertTrue($dbsh->destroy('foo'));
			$this->assertNull($dbsh->read('foo'));
			$this->assertTrue($dbsh->gc(13));
		}
		
		public function testWhenModelTableColumnsIsNotSet(){
			//session table is empty
			$this->model->setSessionTableColumns(array());
			$this->assertEmpty($this->model->getSessionTableColumns());
			$dbsh = new DBSessionHandler($this->model);
			$this->assertTrue($dbsh->open(null, null));
		}
		
		
	}
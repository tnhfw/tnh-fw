<?php 

	use PHPUnit\Framework\TestCase;

	class DBSessionHandlerTest extends TestCase
	{	
	
		private $db = null;
		
		private $model = null;
		
		private $secret = 'bXlzZWNyZXQ';
		
		private static $config = null;
		
		public function __construct(){
            $cfg = get_db_config();
			$this->db = new Database($cfg);
            $qr = new DatabaseQueryRunner($this->db->getPdo());
            $qr->setBenchmark(new Benchmark());
            $qr->setDriver('sqlite');
            $this->db->setQueryRunner($qr);
		}
		
		public static function setUpBeforeClass()
		{
			require APPS_MODEL_PATH . 'DBSessionModel.php';
			self::$config = new Config();
			self::$config->init();
		}
		
		
		public static function tearDownAfterClass()
		{
			
		}
		
		protected function setUp()
		{
			$this->model = new DBSessionModel($this->db);
            //to prevent old data conflict
			$this->model->truncate();
		}

		protected function tearDown()
		{
		}

		
		
		public function testUsingSessionConfiguration(){
			//using value in the configuration
			self::$config->set('session_save_path', 'DBSessionModel');
			self::$config->set('session_secret', $this->secret);
			$dbsh = new DBSessionHandler();
			//assign Database instance manually
			$o = &get_instance();
			$o->database = $this->db;
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertNull($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//do update of existing data
			$this->assertTrue($dbsh->write('foo', '445'));
			$this->assertEquals($dbsh->read('foo'), '445');	
			$this->assertTrue($dbsh->destroy('foo'));
			$this->assertNull($dbsh->read('foo'));
			$this->assertTrue($dbsh->gc(13));
			$encoded = $dbsh->encode('foo');
			$this->assertNotEmpty($encoded);
			$this->assertEquals($dbsh->decode($encoded), 'foo');
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
			$this->assertNull($dbsh->read('foo'));
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
			$this->assertNull($dbsh->read('foo'));
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
			$encoded = $dbsh->encode('foo');
			$this->assertNotEmpty($encoded);
			$this->assertEquals($dbsh->decode($encoded), 'foo');
		}
			
			
		public function testUsingCustomLogInstance(){
			$dbsh = new DBSessionHandler($this->model);
			$dbsh->setSessionSecret($this->secret);
            $dbsh->setLogger(new Log());
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertNull($dbsh->read('foo'));
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
			$encoded = $dbsh->encode('foo');
			$this->assertNotEmpty($encoded);
			$this->assertEquals($dbsh->decode($encoded), 'foo');
		}
		
		public function testUsingCustomLoaderInstance(){
			$dbsh = new DBSessionHandler($this->model);
			$dbsh->setSessionSecret($this->secret);
            $dbsh->setLoader(new Loader());
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertNull($dbsh->read('foo'));
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
			$encoded = $dbsh->encode('foo');
			$this->assertNotEmpty($encoded);
			$this->assertEquals($dbsh->decode($encoded), 'foo');
		}
		
		
		public function testWhenModelInsanceIsNotSet(){
			$dbsh = new DBSessionHandler();
			$dbsh->setSessionSecret($this->secret);
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertNull($dbsh->read('foo'));
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
			$encoded = $dbsh->encode('foo');
			$this->assertNotEmpty($encoded);
			$this->assertEquals($dbsh->decode($encoded), 'foo');
		}
		
		public function testWhenModelTableColumnsIsNotSet(){
			//session table is empty
			$this->model->setSessionTableColumns(array());
			$dbsh = new DBSessionHandler($this->model);
			$this->assertTrue($dbsh->open(null, null));
		}
		
		
	}
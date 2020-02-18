<?php 

	use PHPUnit\Framework\TestCase;

	class DBSessionHandlerTest extends TestCase
	{	
	
		private $dbConfig = array(
								'driver'    =>  'mysql',
								'username'  =>  'root',
								'password'  =>  '',
								'database'  =>  'db_gesco',
								'hostname'  =>  'localhost:3307',
								'charset'   => 'utf8',
								'collation' => 'utf8_general_ci',
								'prefix'    =>  '',
								'port'      =>  3307
							);
		private $db = null;
		
		private static $config = null;
		
		public static function setUpBeforeClass()
		{
			require APPS_MODEL_PATH . 'DBSessionModel.php';
			static::$config = new Config();
			static::$config->init();
		}
		
		
		public static function tearDownAfterClass()
		{
			
		}
		
		protected function setUp()
		{
			$this->db = new Database($this->dbConfig);
			$this->db->setBenchmark(new Benchmark());
		}

		protected function tearDown()
		{
		}

		
		
		public function testUsingSessionConfiguration(){
			$secret = 'bXlzZWNyZXQ';
			//using value in the configuration
			static::$config->set('session_save_path', 'DBSessionModel');
			static::$config->set('session_secret', $secret);
			$dbsh = new DBSessionHandler();
			//assign Database instance manually
			$o = &get_instance();
			$o->database = $this->db;
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//do update of existing data
			$this->assertTrue($dbsh->write('foo', '445'));
			$this->assertEquals($dbsh->read('foo'), '445');	
			$this->assertTrue($dbsh->destroy('foo'));
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->gc(13));
			$encoded = $dbsh->encode('foo');
			$this->assertNotEmpty($encoded);
			$this->assertEquals($dbsh->decode($encoded), 'foo');
		}
		
		public function testWhenDataIsExpired(){
			$model = new DBSessionModel($this->db);
			//to prevent old data conflict
			$model->truncate();
			
			$secret = 'bXlzZWNyZXQ';
			$dbsh = new DBSessionHandler($model);
			$dbsh->setSessionSecret($secret);
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//put it in expired
			$model->update('foo', array('s_time' => 1234567));
			$this->assertFalse($dbsh->read('foo'));
		}
		
		public function testWhenDataAlreadyExistDoUpdate(){
			$model = new DBSessionModel($this->db);
			//to prevent old data conflict
			$model->truncate();
			
			$secret = 'bXlzZWNyZXQ';
			$dbsh = new DBSessionHandler($model);
			$dbsh->setSessionSecret($secret);
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//do update of existing data
			$this->assertTrue($dbsh->write('foo', '445'));
			$this->assertEquals($dbsh->read('foo'), '445');	
		}
		
		public function testUsingCustomModelInstance(){
			
			$model = new DBSessionModel($this->db);
			//to prevent old data conflict
			$model->truncate();
			
			$secret = 'bXlzZWNyZXQ';
			$dbsh = new DBSessionHandler($model);
			$dbsh->setSessionSecret($secret);
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//put it in expired
			$model->update('foo', array('s_time' => 1234567));
			
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			
			//do update of existing data
			$this->assertTrue($dbsh->write('foo', '445'));
			$this->assertEquals($dbsh->read('foo'), '445');	
			$this->assertTrue($dbsh->destroy('foo'));
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->gc(13));
			$encoded = $dbsh->encode('foo');
			$this->assertNotEmpty($encoded);
			$this->assertEquals($dbsh->decode($encoded), 'foo');
		}
			
			
		public function testUsingCustomLogInstance(){
			$model = new DBSessionModel($this->db);
			//to prevent old data conflict
			$model->truncate();
			
			$secret = 'bXlzZWNyZXQ';
			$dbsh = new DBSessionHandler($model, new Log());
			$dbsh->setSessionSecret($secret);
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//put it in expired
			$model->update('foo', array('s_time' => 1234567));
			
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			
			//do update of existing data
			$this->assertTrue($dbsh->write('foo', '445'));
			$this->assertEquals($dbsh->read('foo'), '445');	
			$this->assertTrue($dbsh->destroy('foo'));
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->gc(13));
			$encoded = $dbsh->encode('foo');
			$this->assertNotEmpty($encoded);
			$this->assertEquals($dbsh->decode($encoded), 'foo');
		}
		
		public function testUsingCustomLoaderInstance(){
			$model = new DBSessionModel($this->db);
			//to prevent old data conflict
			$model->truncate();
			
			$secret = 'bXlzZWNyZXQ';
			$dbsh = new DBSessionHandler($model, null, new Loader());
			$dbsh->setSessionSecret($secret);
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//put it in expired
			$model->update('foo', array('s_time' => 1234567));
			
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			
			//do update of existing data
			$this->assertTrue($dbsh->write('foo', '445'));
			$this->assertEquals($dbsh->read('foo'), '445');	
			$this->assertTrue($dbsh->destroy('foo'));
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->gc(13));
			$encoded = $dbsh->encode('foo');
			$this->assertNotEmpty($encoded);
			$this->assertEquals($dbsh->decode($encoded), 'foo');
		}
		
		public function testWhenModelInsanceIsNotSet(){
			$model = new DBSessionModel($this->db);
			//to prevent old data conflict
			$model->truncate();
			
			$secret = 'bXlzZWNyZXQ';
			$dbsh = new DBSessionHandler(null, null, new Loader());
			$dbsh->setSessionSecret($secret);
			
			$this->assertTrue($dbsh->open(null, null));
			$this->assertTrue($dbsh->close());
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			$this->assertNotEmpty($dbsh->read('foo'));
			$this->assertEquals($dbsh->read('foo'), '444');
			//put it in expired
			$model->update('foo', array('s_time' => 1234567));
			
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->write('foo', '444'));
			
			//do update of existing data
			$this->assertTrue($dbsh->write('foo', '445'));
			$this->assertEquals($dbsh->read('foo'), '445');	
			$this->assertTrue($dbsh->destroy('foo'));
			$this->assertFalse($dbsh->read('foo'));
			$this->assertTrue($dbsh->gc(13));
			$encoded = $dbsh->encode('foo');
			$this->assertNotEmpty($encoded);
			$this->assertEquals($dbsh->decode($encoded), 'foo');
		}
		
		public function testWhenModelTableColumnsIsNotSet(){
			$model = new DBSessionModel($this->db);
			//to prevent old data conflict
			$model->truncate();
			
			//session table is empty
			$model->setSessionTableColumns(array());
			$dbsh = new DBSessionHandler($model);
			$this->assertTrue($dbsh->open(null, null));
			
		}
	}
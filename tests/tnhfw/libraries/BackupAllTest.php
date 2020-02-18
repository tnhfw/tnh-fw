<?php 

	use PHPUnit\Framework\TestCase;

	class BackupAllTest extends TestCase
	{	
	
		public static function setUpBeforeClass()
		{
			require 'hmvc/models/CoursModel.php';
			require 'hmvc/models/ClasseModel.php';
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

		// tests
		public function testSomeFeature()
		{
			$config['driver']    =  'mysql';
			$config['username']  =  'root';
			$config['password']  =  '';
			$config['database']  =  'db_gesco';
			$config['hostname']  =  'localhost:3307';
			$config['charset']   = 'utf8';
			$config['collation'] = 'utf8_general_ci';
			$config['prefix']    =  '';
			$config['port']      =  3307;
			
			$l = new Log();
			$l->setLogger('TNHBIS');
			
			$db = new Database($config, $l);
			
			$b = new Benchmark();
			$db->setBenchmark($b);
			
			$c = new FileCache();
			$db->setCacheInstance($c);
			
			$db->cached(3000)->select('*')->from('etudiant')->getAll();
			
			//$db->setLogger($l);
			//file_put_contents('tnh.txt', stringfy_vars($db->getBenchmark()));
			$this->assertNotEmpty($db->getDatabaseConfiguration());
			$this->assertTrue($db->connect());
			
			
			
			$fv = new FormValidation();
			
			$this->assertFalse($fv->run());
			
			$m = new CoursModel($db);
			$m->setFormValidation($fv);
			$this->assertFalse($m->insert(array('dep_id' => 566, 'spe_lib' => '')));
			$this->assertFalse($m->getFormValidation()->run());
			
			$db = new Database();
			$db->setDatabaseConfiguration($config);
			$this->assertTrue($db->connect());
		}
		
		public function testCache()
		{
			$lf = new Log();
			$lf->setLogger('TNHCACHEFILE');
			$c = new FileCache();
			$c->setLogger($lf);
			//prevent using old data
			$c->clean();
			$this->assertFalse($c->get('foo'));
			$c->set('foo', 'bar');
			$this->assertEquals($c->get('foo'), 'bar');
			//class_loader('foo');
			//$this->expectOutputRegex('cannot');
		}
		
		
		
		
		public function testConfigChange(){
			//echo Config::get('base_url');
			$c = new Config();
			$c->set('base_url', 'foo');
			//echo get_config('base_url'); 
		}
		
		public function testCallPrivateProtectedMethod(){
			$c = new FileCache();
			$expected = CACHE_PATH . md5('foo') . '.cache';
			$result = runPrivateOrProtectedMethod($c, 'getFilePath', array('foo'));
			$this->assertEquals($expected, $result);
		}
		
		public function testMocks(){
			$mock = $this->getMockBuilder('Model')->getMock();
			$mock->expects($this->any())->method('get')->will($this->returnValue('foobar'));
			$this->assertEquals('foobar', $mock->get(1));
		}
	}
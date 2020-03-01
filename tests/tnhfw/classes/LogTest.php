<?php 

	use PHPUnit\Framework\TestCase;

	class LogTest extends TestCase
	{	
       private $logPath = '';
       private $config = null;
       private $vfsRoot = null;
       private $logFilename = '';
       
       public function __construct(){
           $root = vfsStream::setup();
           $this->vfsRoot = vfsStream::newDirectory('log_path')->at($root);
           $this->logPath = $this->vfsRoot->url() . DS;
           
           //log file name
           $this->logFilename = 'logs-' . date('Y-m-d') . '.log';
       }
	
		public static function setUpBeforeClass()
		{
		
		}
		
		public static function tearDownAfterClass()
		{
			
		}
		
		protected function setUp()
		{
            //some global configuration
           $this->config = new Config();
           $this->config->init();
           $this->config->deleteAll();
           $this->config->set('log_save_path', $this->logPath);
           $this->config->set('log_logger_name', array());
           
            if($this->vfsRoot->hasChild($this->logFilename)){
                $this->vfsRoot->removeChild($this->logFilename);
            }
		}

		protected function tearDown()
		{
		}
        
        public function testSetLoggerName()
		{
            $log = new Log();
            $this->assertSame('ROOT_LOGGER', $log->getLogger());
            $log->setLogger('MY_LOGGER_NAME');
            $this->assertSame('MY_LOGGER_NAME', $log->getLogger());
		}
		
		public function testLogLevelNone()
		{
            $this->config->set('log_level', 'NONE');
            $log = new Log();
            $this->assertSame('ROOT_LOGGER', $log->getLogger());
            $log->debug('Debug message');
            $this->assertFalse($this->vfsRoot->hasChild($this->logFilename));
		}
        
        public function testLogLevelDebug()
		{
            $this->config->set('log_level', 'DEBUG');
            $log = new Log();
            $log->debug('Debug message');
            $this->assertTrue($this->vfsRoot->hasChild($this->logFilename));
		}

	}
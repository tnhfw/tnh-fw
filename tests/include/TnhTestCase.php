<?php 
	use PHPUnit\Framework\TestCase;

	class TnhTestCase extends TestCase
	{	
        //vfsStream root
        protected $vfsRoot = null;
        
        //vfs logs path 
        protected $vfsLogPath = null;
        
        //vfs log filename
        protected $logFilename = '';
        
        //vfs file caches path 
        protected $vfsFileCachePath = null;
        
        //Application configuration 
        //see class "Config"
        protected $config = null;
        
       
	
		public function __construct() {
           //some global configuration
           $this->config = new Config();
           $this->config->init();
           //delete all configuration each test will set the custom config to use
           $this->config->deleteAll();
           
           $this->vfsRoot = vfsStream::setup();
           $this->vfsLogPath = vfsStream::newDirectory('logs')->at($this->vfsRoot);
           $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
           
           //log file name
           $this->logFilename = 'logs-' . date('Y-m-d') . '.log';
        }
        
        /**
        * Method to test private & protected method
        */
        protected function runPrivateProtectedMethod($object, $method, array $args = array()){
            $r = new ReflectionClass(get_class($object));
            $m = $r->getMethod($method);
            $m->setAccessible(true);
            return $m->invokeArgs($object, $args);
        }
        
        /**
        * Method to return the correct database configuration
        * using for test
        */
        protected function getDbConfig(){
            return array(
                        'driver'    =>  'sqlite',
                        'database'  =>  TESTS_PATH . 'assets' . DS . 'db_tests.db',
                        'charset'   => 'utf8',
                        'collation' => 'utf8_general_ci',
                    );
        }
        
        
        /**
        * this is used only to debug test case in certain situation
        */
        protected function debugTest($o){
            $fp = fopen('debug_test.txt', 'a+');
            if(is_resource($fp)){
                $separator = str_repeat('=', 90). "\n\n";
                $s = $separator;
                $s .= stringfy_vars($o) . "\n";
                $s .= $separator;
                fwrite($fp, $s);
                fclose($fp);
            }
        }

	}

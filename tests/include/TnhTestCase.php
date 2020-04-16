<?php 
    /**
     * TNH Framework
     *
     * A simple PHP framework using HMVC architecture
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2017 TNH Framework
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in all
     * copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
     * SOFTWARE.
     */
    
    use PHPUnit\Framework\TestCase;

    class TnhTestCase extends TestCase {    
        //The super controller instance
        private $instance = null;
        
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
            parent::__construct();
            $this->instance = & get_instance();
            $this->config = new Config();
           
            $this->vfsRoot = vfsStream::setup();
            $this->vfsLogPath = vfsStream::newDirectory('logs')->at($this->vfsRoot);
            $this->vfsFileCachePath = vfsStream::newDirectory('cache')->at($this->vfsRoot);
           
           //log file name
           $this->logFilename = 'logs-' . date('Y-m-d') . '.log';
        }
        
        /**
         * &get_instance() adapter for test
         */
        public static function getInstanceForTest(){
           if(! Controller::getInstance()){
                //Some required classes
                class_loader('GlobalVar', 'classes');
                class_loader('Module', 'classes');
                class_loader('Config', 'classes');
        
                $instance = new Controller();
                return $instance;
            }
            return Controller::getInstance();
        }
        
        protected function setUp() {
            //delete all configuration, so each test will set the custom config to use
           $this->config->deleteAll();
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
        * Method to set/get private & protected attribute
        */
        protected function getPrivateProtectedAttribute($className, $attr){
            $rProp = new ReflectionProperty($className, $attr);
            $rProp->setAccessible(true);
            return $rProp;
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
        * Return the database instance for test
        */
        protected function getDbInstanceForTest() {
            $connection = new DatabaseConnection($this->getDbConfig(), true);
            $db = new Database($connection);
                        
            $qr = new DatabaseQueryRunner($connection);
            $qr->setBenchmark(new Benchmark());
            
            $qresult = new DatabaseQueryResult();
            $qr->setQueryResult($qresult);
            $db->setQueryRunner($qr);
            
            $qb = new DatabaseQueryBuilder($connection);
            $db->setQueryBuilder($qb);
            
            $cache = $this->getMockBuilder('DatabaseCache')
                          ->setMethods(array('getCacheContent', 'setCacheContent'))
                          ->getMock();
            
             $cache->expects($this->any())
                 ->method('getCacheContent')
                 ->will($this->returnValue(null));    
             $db->setCache($cache);
             
            return $db;
        }
        
        
        /**
        * This is used only to debug test case in certain situation
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

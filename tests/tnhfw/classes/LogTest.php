<?php 

	/**
     * Log class tests
     *
     * @group core
     * @group core_classes
     */
	class LogTest extends TnhTestCase {	
        
        public function __construct(){
            parent::__construct();
            $this->config->set('log_logger_name', array());
        }
		
		protected function setUp() {
            parent::setUp();
            //need setup for each test
            $this->vfsRoot = vfsStream::setup();
            $this->vfsLogPath = vfsStream::newDirectory('logs')->at($this->vfsRoot);
            $this->config->set('log_save_path', $this->vfsLogPath->url() . '/');
            if($this->vfsLogPath->hasChild($this->logFilename)){
               $this->vfsLogPath->removeChild($this->logFilename);
            }
		}

        public function testSetLoggerName() {
            $log = new Log();
            $this->assertSame('ROOT_LOGGER', $log->getLogger());
            $log->setLogger('MY_LOGGER_NAME');
            $this->assertSame('MY_LOGGER_NAME', $log->getLogger());
		}
        
        public function testCheckAndSetLogFileDirectoryConfigLogPathIsEmpty() {
            $this->config->set('log_save_path', '');
            $log = new Log();
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'DEBUG');
            $log = new Log();
            $log->debug('Debug message');
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
		}
        
        public function testCheckAndSetLogFileDirectoryConfigLogPathIsSetButNotExist() {
            $this->config->set('log_save_path', 'path/foo/bar/');
            $log = new Log();
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'DEBUG');
            $log = new Log();
            $log->debug('Debug message');
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
		}
		
		public function testLogLevelEmpty() {
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', '');
            $log = new Log();
            $this->assertSame('ROOT_LOGGER', $log->getLogger());
            $log->debug('Debug message');
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
		}
        
        public function testLogLevelNone() {
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'NONE');
            $log = new Log();
            $this->assertSame('ROOT_LOGGER', $log->getLogger());
            $log->debug('Debug message');
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
		}
        
        
        public function testLogLevelDebug() {
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'DEBUG');
            $log = new Log();
            $log->debug('Debug message');
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('Debug message', $content);
		}
        
        public function testLogLevelInfo() {
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'INFO');
            $log = new Log();
            $log->info('Info message');
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('Info message', $content);
		}
        
        public function testLogLevelNotice() {  
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'NOTICE');
            $log = new Log();
            $log->notice('Notice message');
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('Notice message', $content);
		}
        
        public function testLogLevelWarning() {
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'WARNING');
            $log = new Log();
            $log->warning('Warning message');
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('Warning message', $content);
		}
        
        public function testLogLevelError() {
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'ERROR');
            $log = new Log();
            $log->error('Error message');
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('Error message', $content);
		}
        
        public function testLogLevelCritical() {
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'CRITICAL');
            $log = new Log();
            $log->critical('Critical message');
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('Critical message', $content);
		}
        
        
        public function testLogLevelAlert() {
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'ALERT');
            $log = new Log();
            $log->alert('Alert message');
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('Alert message', $content);
		}
        
        
        
        public function testLogLevelEmergency() {
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'EMERGENCY');
            $log = new Log();
            $log->emergency('Emergency message');
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('Emergency message', $content);
		}
        
        
        public function testLogLevelThreshold(){
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'CRITICAL');
            $log = new Log();
            $log->critical('Critical message');            
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            //log can not be saved 
            $log->info('Info message');
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('Critical message', $content);
            $this->assertNotContains('Info message', $content);
        }
        
        public function testWrongConfigLogLevel(){
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'FOO_LEVEL');
            $log = new Log();
            $log->critical('Critical message');            
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
        }
        
        public function testUsingDirectMethodLog(){
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'DEBUG');
            $log = new Log();
            $log->log('INFO', 'Info message');        
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('Info message', $content);
        }
        
        public function testCurrentLoggerNameCantSaveLogData(){
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'DEBUG');
            $this->config->set('log_logger_name', array('FOO_LOGGER'));
            $log = new Log();
            $this->assertSame('ROOT_LOGGER',$log->getLogger());
            $log->log('INFO', 'Info message');            
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
        }
        
        public function testCurrentLoggerNameCanSaveLogData(){
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'DEBUG');
            $this->config->set('log_logger_name', array('FOO_LOGGER'));
            $log = new Log();
            $log->setLogger('FOO_LOGGER');
            $this->assertSame('FOO_LOGGER',$log->getLogger());
            $log->info('Info message');            
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
            $content = $this->vfsLogPath->getChild($this->logFilename)->getContent();
            $this->assertContains('Info message', $content);
            $this->assertContains('FOO_LOGGER', $content);
        }
        
        public function testCustomLoggerNameLevel(){
            //check if filename not exists before
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_level', 'ERROR');
            $this->config->set('log_logger_name_level', array());
            
            $log = new Log();
            $log->debug('Debug message');                       
            $this->assertFalse($this->vfsLogPath->hasChild($this->logFilename));
            
            $this->config->set('log_logger_name_level', array('FOO_LOGGER' => 'DEBUG'));
            $log = new Log();
            $log->setLogger('FOO_LOGGER');
            $log->info('Info message');                       
            $this->assertTrue($this->vfsLogPath->hasChild($this->logFilename));
        }
        

	}
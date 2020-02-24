<?php 

	use PHPUnit\Framework\TestCase;

	class FunctionUserAgentTest extends TestCase
	{	
	
		public static function setUpBeforeClass()
		{
            require_once CORE_FUNCTIONS_PATH . 'function_user_agent.php';
		}
		
		public function testLoopBack()
		{
			$this->assertEquals('127.0.0.1', get_ip());
		}
        
        public function testServerRemoteAddr()
		{
            $ip = '182.23.24.56';
            $_SERVER['REMOTE_ADDR'] = $ip;
			$this->assertEquals($ip, get_ip());
		}
        
        public function testServerHttpClientIp()
		{
            $ip = '192.168.24.1';
            $_SERVER['HTTP_CLIENT_IP'] = $ip;
			$this->assertEquals($ip, get_ip());
		}
        
        public function testServerHttpXForwardedFor()
		{
            $ip = '172.18.2.1';
            $_SERVER['HTTP_X_FORWARDED_FOR'] = $ip;
			$this->assertEquals($ip, get_ip());
		}
        
        public function testServerHttpXForwarded()
		{
            
            $ip = '12.18.2.1';
            $_SERVER['HTTP_X_FORWARDED'] = $ip;
			$this->assertEquals($ip, get_ip());
		}
        
        public function testServerHttpForwardedFor()
		{
            $ip = '198.180.2.1';
            $_SERVER['HTTP_FORWARDED_FOR'] = $ip;
			$this->assertEquals($ip, get_ip());
		}
        
        public function testServerHttpForwarded()
		{
            $ip = '220.200.2.1';
            $_SERVER['HTTP_FORWARDED'] = $ip;
			$this->assertEquals($ip, get_ip());
		}
        
         public function testManyIp()
		{
            $ips = '20.200.2.1, 192.168.34.4';
            $ip = '20.200.2.1';
            $_SERVER['REMOTE_ADDR'] = $ips;
			$this->assertEquals($ip, get_ip());
		}

	}
<?php 

	
	class EventInfoTest extends TnhTestCase
	{	
	
		public static function setUpBeforeClass()
		{
		
		}
		
		public static function tearDownAfterClass()
		{
			
		}
		
		protected function setUp()
        {
            parent::setUp();
        }

		protected function tearDown()
		{
		}

		
		
		public function testDefaultValue(){
			$e = new EventInfo('foo');
			$this->assertSame($e->name, 'foo');
			$this->assertSame($e->payload, array());
			$this->assertFalse($e->returnBack);
			$this->assertFalse($e->stop);
		}
		
		public function testPayloadValueIsSet(){
			$e = new EventInfo('foo', array('bar'));
			$this->assertSame($e->name, 'foo');
			$this->assertSame($e->payload, array('bar'));
			$this->assertFalse($e->returnBack);
			$this->assertFalse($e->stop);
		}
		
		public function testReturnBackValueIsSetToTrue(){
			$e = new EventInfo('foo', array('bar'), true);
			$this->assertSame($e->name, 'foo');
			$this->assertSame($e->payload, array('bar'));
			$this->assertTrue($e->returnBack);
			$this->assertFalse($e->stop);
		}
		
		public function testStopValueIsSetToTue(){
			$e = new EventInfo('foo', array('bar'), true, true);
			$this->assertSame($e->name, 'foo');
			$this->assertSame($e->payload, array('bar'));
			$this->assertTrue($e->returnBack);
			$this->assertTrue($e->stop);
		}
	}
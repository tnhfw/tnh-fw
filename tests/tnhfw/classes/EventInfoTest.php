<?php 

	/**
     * EventInfo class tests
     *
     * @group core
     * @group core_classes
     * @group event
     */
	class EventInfoTest extends TnhTestCase {	
	
		protected function setUp()
        {
            parent::setUp();
        }

		
		public function testDefaultValue(){
			$e = new EventInfo('foo');
			$this->assertSame($e->getName(), 'foo');
			$this->assertSame($e->getPayload(), array());
			$this->assertFalse($e->isReturnBack());
			$this->assertFalse($e->isStop());
		}
		
		public function testPayloadValueIsSet(){
			$e = new EventInfo('foo', array('bar'));
			$this->assertSame($e->getName(), 'foo');
			$this->assertSame($e->getPayload(), array('bar'));
            //using setter
            $e->setPayload('foobar');
            $this->assertSame($e->getPayload(), 'foobar');
			$this->assertFalse($e->isReturnBack());
			$this->assertFalse($e->isStop());
		}
		
		public function testReturnBackValueIsSetToTrue(){
			$e = new EventInfo('foo', array('bar'), true);
			$this->assertSame($e->getName(), 'foo');
			$this->assertSame($e->getPayload(), array('bar'));
			$this->assertTrue($e->isReturnBack());
			$this->assertFalse($e->isStop());
		}
		
		public function testStopValueIsSetToTue(){
			$e = new EventInfo('foo', array('bar'), true, true);
			$this->assertSame($e->getName(), 'foo');
			$this->assertSame($e->getPayload(), array('bar'));
			$this->assertTrue($e->isReturnBack());
            $this->assertTrue($e->isStop());
             //using setter
			$e->setStop(false);
			$this->assertFalse($e->isStop());
		}
	}
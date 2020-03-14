<?php 

	/**
     * StringHash library class tests
     *
     * @group core
     * @group libraries
     * @group security
     */
	class StringHashTest extends TnhTestCase {	

		
		public function testHash() {
            $plain = 'fooobarr';
            $wplain = 'fooobarrr';
            $s = new StringHash();
            $hash = $s->hash($plain);
			$this->assertNotEmpty($hash);
            
            $this->assertTrue($s->check($hash, $plain));
            $this->assertFalse($s->check($hash, $wplain));
            $this->assertFalse($s->check('ddjdjgjgssgjgf', $plain));
		}

	}
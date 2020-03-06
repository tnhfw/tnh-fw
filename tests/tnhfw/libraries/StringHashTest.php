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
            $hash = StringHash::hash($plain);
			$this->assertNotEmpty($hash);
            
            $this->assertTrue(StringHash::check($hash, $plain));
            $this->assertFalse(StringHash::check($hash, $wplain));
            $this->assertFalse(StringHash::check('ddjdjgjgssgjgf', $plain));
		}

	}
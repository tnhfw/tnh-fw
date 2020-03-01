<?php
	class DBSessionModel extends DBSessionHandlerModel{
		
		protected $_table = 'ses';
		protected $primary_key = 's_id';
		
		protected $sessionTableColumns = array(
			'sid' => 's_id', //VARCHAR(255)
			'sdata' => 's_data', //TEXT
			'stime' => 's_time', //unix timestamp (INT|BIGINT)
			'shost' => 's_host', //VARCHAR(255)
			'sip' => 's_ip', //VARCHAR(255) 
			'sbrowser' => 's_browser', //VARCHAR(255) 
			'skey' => 'test_id' //VARCHAR(255) 
		);
		
		public function deleteByTime($time){
			$this->getQueryBuilder()->from($this->_table)
									->where('s_time', '<', $time);
			$this->_database->delete();
		}

		
		public function getKeyValue(){
			return 'foobarbaz';
		}
	}

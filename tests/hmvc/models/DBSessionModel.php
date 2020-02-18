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
			'skey' => 'usr_id' //VARCHAR(255) 
		);
		
		public function deleteByTime($time){
			$this->_database->from($this->_table)
						->where('s_time', '<', $time)
						->delete();
		}

		
		public function getKeyValue(){
			$user_id = 0;
			return $user_id;
		}
	}
<?php
	defined('ROOT_PATH') || exit('Access denied');

	/**
	 * DB session handler class
	 */
	abstract class DBSessionHandler_model extends Model {
		/**
		 * the session table columns to use
		 * @var array
		 * @example
		 * 	array(
				'sid' => '', //VARCHAR(255) Note: this a primary key
				'sdata' => '', //TEXT
				'stime' => '', //unix timestamp (INT|BIGINT)
				'shost' => '', //VARCHAR(255)
				'sip' => '', //VARCHAR(255) 
				'sbrowser' => '', //VARCHAR(255) 
				'skey' => '' //VARCHAR(255) 
			);
		 */
		protected $sessionTableColumns = array();

		public function __construct(){
			parent::__construct();
		}

		public function getSessionTableColumns(){
			return $this->sessionTableColumns;
		}

		/**
		 * delete the expire sesion
		 * @param  int|long $time the unix timestamp
		 * @return int       affected rows
		 */
		abstract public function deleteByTime($time);

		/**
		 * get online session
		 * @param  int $offset the limit offset
		 * @param  int $limit  the number of rows
		 * @return array         the list of online session
		 */
		abstract public function get_online_list($offset = null, $limit = null);

		/**
		 * how to get the value of the table column key. Generally is the session key
		 * @return mixed the key value like used to identify
		 */
		abstract public function getKeyValue();

	}
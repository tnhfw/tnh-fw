<?php
	defined('ROOT_PATH') || exit('Access denied');
	/**
	 * TNH Framework
	 *
	 * A simple PHP framework using HMVC architecture
	 *
	 * This content is released under the GNU GPL License (GPL)
	 *
	 * Copyright (C) 2017 Tony NGUEREZA
	 *
	 * This program is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU General Public License
	 * as published by the Free Software Foundation; either version 3
	 * of the License, or (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program; if not, write to the Free Software
	 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
	*/
	
	/**
	 * DB session handler class
	 */
 	abstract class DBSessionHandler_model extends Model {
		/**
		 * The session table columns to use
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
		 * Delete the expire session
		 * @param  int|long $time the unix timestamp
		 * @return int       affected rows
		 */
		abstract public function deleteByTime($time);

		/**
		 * Get online session
		 * @param  int $offset the limit offset
		 * @param  int $limit  the number of rows
		 * @return array         the list of online session
		 */
		abstract public function get_online_list($offset = null, $limit = null);

		/**
		 * How to get the value of the table column key. Generally is the session key
		 * @return mixed the key value like used to identify the data
		 */
		abstract public function getKeyValue();
	}
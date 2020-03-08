<?php
    defined('ROOT_PATH') || exit('Access denied');
    /**
     * TNH Framework
     *
     * A simple PHP framework using HMVC architecture
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2017 TNH Framework
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in all
     * copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
     * SOFTWARE.
     */
	
    /**
    * DB session handler model class
    */
    abstract class DBSessionHandlerModel extends Model {
		
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

        public function __construct(Database $db = null) {
            parent::__construct($db);
        }

        /**
         * Return the session database table columns
         * @return array 
         */
        public function getSessionTableColumns() {
            return $this->sessionTableColumns;
        }

        /**
         * Set the session database table columns
         * @param array $columns the columns definition
         */
        public function setSessionTableColumns(array $columns) {
            $this->sessionTableColumns = $columns;
            return $this;
        }

        /**
         * Delete the expire session
         * @param  int $time the unix timestamp
         * @return int       affected rows
         */
        abstract public function deleteByTime($time);

		
        /**
         * How to get the value of the table column key. Generally is the session key
         * @return mixed the key value like used to identify the data
         */
        abstract public function getKeyValue();
    }

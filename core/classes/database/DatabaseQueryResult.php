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
  
    class DatabaseQueryResult{
  	
        /**
         * The database query result
         * @var mixed
         */
        private $result  = null;
  	
    
        /**
         * The number of rows returned by the last query
         * @var int
         */
        private $numRows = 0;
  	
	
    /**
     * Construct new DatabaseQueryResult
     * @param mixed $result the query result
     * @param int $numRows the number of rows returned by the query
     */
    public function __construct($result = null, $numRows = 0) {
        $this->result = $result;
        $this->numRows = $numRows;
    }

    
        /**
         * Return the query result
         *
         * @return mixed
         */
    public function getResult(){
        return $this->result;
    }

    /**
     * Set the query result
     * @param mixed $result the query result
     *
     * @return object DatabaseQueryResult
     */
    public function setResult($result){
        $this->result = $result;
        return $this;
    }
    
    /**
     * Return the number of rows returned by the query
     *
     * @return int
     */
    public function getNumRows(){
        return $this->numRows;
    }

    /**
     * Set the number of rows returned by the query
     * @param int $numRows the number of rows returned
     *
     * @return object DatabaseQueryResult
     */
    public function setNumRows($numRows){
        $this->numRows = $numRows;
        return $this;
    }
   
}

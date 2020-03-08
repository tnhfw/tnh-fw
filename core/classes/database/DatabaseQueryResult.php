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
  
    class DatabaseQueryResult {
  	
        /**
         * The database query result
         * @var mixed
         */
        private $result = null;
  	
    
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
    public function getResult() {
        return $this->result;
    }

    /**
     * Set the query result
     * @param mixed $result the query result
     *
     * @return object DatabaseQueryResult
     */
    public function setResult($result) {
        $this->result = $result;
        return $this;
    }
    
    /**
     * Return the number of rows returned by the query
     *
     * @return int
     */
    public function getNumRows() {
        return $this->numRows;
    }

    /**
     * Set the number of rows returned by the query
     * @param int $numRows the number of rows returned
     *
     * @return object DatabaseQueryResult
     */
    public function setNumRows($numRows) {
        $this->numRows = $numRows;
        return $this;
    }
   
}

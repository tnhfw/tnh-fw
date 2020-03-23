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
    
    class DatabaseQueryRunner extends BaseClass {

         /**
         * The DatabaseConnection instance
         * @var object
         */
        private $connection = null;

        /**
         * The last query result
         * @var object
         */
        protected $queryResult = null;
  	
        /**
         * The benchmark instance
         * @var object
         */
        protected $benchmark = null;
        
        /**
         * The SQL query statment to execute
         * @var string
         */
        private $query = null;
    
        /**
         * Indicate if we need return result as list
         * @var boolean
         */
        private $returnAsList = true;
     
     
        /**
         * Indicate if we need return result as array or not
         * @var boolean
         */
        private $returnAsArray = true;
     
        /**
         * The last PDOStatment instance
         * @var object
         */
        private $pdoStatment = null;
     
        /**
         * The error returned for the last query
         * @var string
         */
        private $error = null;
	
        /**
         * Construct new DatabaseQueryRunner
         * @param object $connection the DatabaseConnection object
         */
        public function __construct(DatabaseConnection $connection = null) {
            parent::__construct();
            if ($connection !== null) {
                $this->connection = $connection;
            }
        }
        
        /**
         * Run the database SQL query and return the DatabaseQueryResult object
         * @see Database::query
         * 
         * @return object|void
         */
        public function execute() {
            //reset instance
            $this->reset();

            $this->logger->debug('Begin execution of SQL query [' . $this->query .']');
           
            //for database query execution time
            $benchmarkMarkerKey = $this->getBenchmarkKey();
            
            $this->benchmark->mark('DATABASE_QUERY_START(' . $benchmarkMarkerKey . ')');                
            //Now execute the query
            $this->pdoStatment = $this->connection->getPdo()->query($this->query);
            //get response time for this query
            $responseTime = $this->benchmark->elapsedTime(
                                                                    'DATABASE_QUERY_START(' . $benchmarkMarkerKey . ')', 
                                                                    'DATABASE_QUERY_END(' . $benchmarkMarkerKey . ')'
                                                                    );
                //TODO use the configuration value for the high response time currently is 1 second
            if ((double) $responseTime >= 1.000000) {
                $this->logger->warning(
                                        'High response time while processing database query [' . $this->query . '].' 
                                        . 'The response time is [' .$responseTime . '] sec.'
                                        );
            }
    		
            if ($this->pdoStatment !== false) {
                $this->logger->info('No error found for this query');
                $isSelectQuery = stristr($this->query, 'SELECT') !== false;
                if ($isSelectQuery) {
                    $this->logger->info('This an SELECT SQL query');
                    $this->setResultForSelect();              
                } else {
                    $this->logger->info('This is not an SELECT SQL query');
                    $this->setResultForNonSelect();
                }
                //close cursor
                $this->pdoStatment->closeCursor();
                return $this->queryResult;
            }
            $this->setQueryRunnerError();
        }
    	
        /**
         * Return the DatabaseConnection instance
         * @return object DatabaseConnection
         */
        public function getConnection() {
            return $this->connection;
        }

        /**
         * Set the DatabaseConnection instance
         * @param object DatabaseConnection $connection the DatabaseConnection object
         *
         * @return object the current instance
         */
        public function setConnection(DatabaseConnection $connection) {
            $this->connection = $connection;
            return $this;
        }


        /**
         * Return the benchmark instance
         * @return Benchmark
         */
        public function getBenchmark() {
            return $this->benchmark;
        }

        /**
         * Set the benchmark instance
         * @param Benchmark $benchmark the benchmark object
         * @return object DatabaseQueryRunner
         */
        public function setBenchmark($benchmark) {
            $this->benchmark = $benchmark;
            return $this;
        }
        
        /**
         * Return the database query result
         *
         * @return object DatabaseQueryResult
         */
        public function getQueryResult() {
            return $this->queryResult;
        }

        /**
         * Set the database query result instance
         * @param object $queryResult the query result
         *
         * @return object DatabaseQueryRunner
         */
        public function setQueryResult(DatabaseQueryResult $queryResult = null) {
            $this->queryResult = $queryResult;
            return $this;
        }
        
        /**
         * Return the current query SQL string
         * @return string
         */
        public function getQuery() {
            return $this->query;
        }
        
        /**
         * Set the query SQL string
         * @param string $query the SQL query to set
         * @return object DatabaseQueryRunner
         */
        public function setQuery($query) {
            $this->query = $query;
            return $this;
        }
        
        /**
         * Set the query return type as list or not
         * @param boolean $returnType
         * @return object DatabaseQueryRunner
         */
        public function setReturnType($returnType) {
            $this->returnAsList = $returnType;
            return $this;
        }
        
        /**
         * Set the return as array or not
         * @param boolean $status the status if true will return as array
         * @return object DatabaseQueryRunner
         */
        public function setReturnAsArray($status = true) {
            $this->returnAsArray = $status;
            return $this;
        }
        
        /**
         * Return the error for last query execution
         * @return string
         */
        public function getQueryError() {
            return $this->error;
        }
        
        /**
         * Return the benchmark key for the current query
         * 
         *  @return string
         */
        protected function getBenchmarkKey() {
            return md5($this->query . $this->returnAsList . $this->returnAsArray);
        }
        
        /**
         * Set error for database query execution
         */
        protected function setQueryRunnerError() {
            $error = $this->connection->getPdo()->errorInfo();
            $this->error = isset($error[2]) ? $error[2] : '';
            $this->logger->error('The database query execution got an error: ' . stringfy_vars($error));
            //show error message
            show_error('Query: "' . $this->query . '" Error: ' . $this->error, 'Database Error');
        }

        /**
         * Return the result for SELECT query
         * @see DatabaseQueryRunner::execute
         */
        protected function setResultForSelect() {
            //if need return all result like list of record
            $result = null;
            $numRows = 0;
            $fetchMode = PDO::FETCH_OBJ;
            if ($this->returnAsArray) {
                $fetchMode = PDO::FETCH_ASSOC;
            }
            if ($this->returnAsList) {
                $result = $this->pdoStatment->fetchAll($fetchMode);
            } else {
                $result = $this->pdoStatment->fetch($fetchMode);
            }
            //Sqlite and pgsql always return 0 when using rowCount()
            if (in_array($this->connection->getDriver(), array('sqlite', 'pgsql'))) {
                //by default count() return 1 if the parameter is not an array
                //or object implements Countable.
                if ($result) {
                    if ($this->returnAsList) {
                        $numRows = count($result);
                    } else {
                        //if object only one row will be returned
                        $numRows = 1;
                    }
                }
            } else {
                $numRows = $this->pdoStatment->rowCount(); 
            }
            $this->queryResult->setResult($result);
            $this->queryResult->setNumRows($numRows);
        }

        /**
         * Return the result for non SELECT query
         * @see DatabaseQueryRunner::execute
         */
        protected function setResultForNonSelect() {
            //Sqlite and pgsql always return 0 when using rowCount()
            $result = false;
            $numRows = 0;
            if (in_array($this->connection->getDriver(), array('sqlite', 'pgsql'))) {
                $result = true; //to test the result for the query like UPDATE, INSERT, DELETE
                $numRows = 1; //TODO use the correct method to get the exact affected row
            } else {
                //to test the result for the query like UPDATE, INSERT, DELETE
                $result  = $this->pdoStatment->rowCount() >= 0; 
                $numRows = $this->pdoStatment->rowCount(); 
            }
            $this->queryResult->setResult($result);
            $this->queryResult->setNumRows($numRows);
        }

        /**
         * Reset the instance before run each query
         */
        private function reset() {
            $this->error = null;
            $this->pdoStatment = null;
        }

    }

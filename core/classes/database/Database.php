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
    
    class Database extends BaseClass {
	
        /**
         * The DatabaseConnection instance
         * @var object
         */
        private $connection = null;
    
        /**
         * The number of rows returned by the last query
         * @var int
         */
        private $numRows = 0;
	
        /**
         * The last insert id for the primary key column that have auto increment or sequence
         * @var mixed
         */
        private $insertId = null;
	
        /**
         * The full SQL query statment after build for each command
         * @var string
         */
        private $query = null;
	
        /**
         * The result returned for the last query
         * @var mixed
         */
        private $result = array();
	
        /**
         * The number of executed query for the current request
         * @var int
         */
        private $queryCount = 0;
	
        /**
         * The default data to be used in the statments query INSERT, UPDATE
         * @var array
         */
        private $data = array();
	
        /**
         * The cache default time to live in second. 0 means no need to use the cache feature
         * @var int
         */
        private $cacheTtl = 0;

        /**
         * The cache current time to live. 0 means no need to use the cache feature
         * @var int
         */
        private $temporaryCacheTtl = 0;

        /**
         * The DatabaseQueryBuilder instance
         * @var object
         */
        protected $queryBuilder = null;
    
        /**
         * The DatabaseQueryRunner instance
         * @var object
         */
        protected $queryRunner = null;

        /**
         * The DatabaseCache instance
         * @var object
         */
        protected $cacheInstance = null;

        /**
         * Construct new instance
         * 
         * @param object $connection the DatabaseConnection instance
         */
        public function __construct(DatabaseConnection $connection = null) {
            parent::__construct();
    		
            //Set DatabaseQueryBuilder instance to use
            $this->setDependencyInstanceFromParamOrCreate('queryBuilder', null, 'DatabaseQueryBuilder', 'classes/database');
           
            //Set DatabaseQueryRunner instance to use
            $this->setDependencyInstanceFromParamOrCreate('queryRunner', null, 'DatabaseQueryRunner', 'classes/database');

            //Set DatabaseCache instance to use
            $this->setDependencyInstanceFromParamOrCreate('cacheInstance', null, 'DatabaseCache', 'classes/database');

            if ($connection !== null) {
                $this->connection = $connection;
            } else {
                $this->setConnectionUsingConfigFile();
            }
            //Update some properties
            $this->updateProperties();
        }

        /**
         * Return the number of rows returned by the current query
         * @return int
         */
        public function numRows() {
            return $this->numRows;
        }

        /**
         * Return the last insert id value
         * @return mixed
         */
        public function insertId() {
            return $this->insertId;
        }

        /**
         * Get the result of one record rows returned by the current query
         * @param  boolean|string $sqlOrResult if is boolean and true will return the SQL query string.
         * If is string will determine the result type "array" or "object"
         * @return mixed       the query SQL string or the record result
         */
        public function get($sqlOrResult = false) {
            $this->queryBuilder->limit(1);
            $query = $this->getAll($returnSql = true);
            if ($sqlOrResult === true) {
                return $query;
            } 
            return $this->query($query, false, $sqlOrResult == 'array');
        }

        /**
         * Get the result of record rows list returned by the current query
         * @param  boolean|string $sqlOrResult if is boolean and true will return the SQL query string.
         * If is string will determine the result type "array" or "object"
         * @return mixed       the query SQL string or the record result
         */
        public function getAll($sqlOrResult = false) {
            $query = $this->queryBuilder->getQuery();
            if ($sqlOrResult === true) {
                return $query;
            }
            return $this->query($query, true, $sqlOrResult == 'array');
        }

        /**
         * Insert new record in the database
         * @param  array   $data   the record data if is empty will use the $this->data array.
         * @param  boolean $escape  whether to escape or not the values
         * @return mixed          the insert id of the new record or null
         */
        public function insert($data = array(), $escape = true) {
            if (empty($data) && !empty($this->data)) {
                //as when using $this->setData() may be the data already escaped
                $escape = false;
                $data = $this->data;
            }
            $query = $this->queryBuilder->insert($data, $escape)->getQuery();
            $result = $this->query($query);
            if ($result) {
                $this->insertId = $this->connection->getPdo()->lastInsertId();
                //if the table doesn't have the auto increment field or sequence, the value of 0 will be returned 
                $id = $this->insertId;
                if (!$id) {
                    $id = true;
                }
                return $id;
            }
            return false;
        }

        /**
         * Update record in the database
         * @param  array   $data   the record data if is empty will use the $this->data array.
         * @param  boolean $escape  whether to escape or not the values
         * @return mixed          the update status
         */
        public function update($data = array(), $escape = true) {
            if (empty($data) && !empty($this->data)) {
                //as when using $this->setData() may be the data already escaped
                $escape = false;
                $data = $this->data;
            }
            $query = $this->queryBuilder->update($data, $escape)->getQuery();
            return $this->query($query);
        }

        /**
         * Delete the record in database
         * @return mixed the delete status
         */
        public function delete() {
            $query = $this->queryBuilder->delete()->getQuery();
            return $this->query($query);
        }

        /**
         * Set database cache time to live
         * @param integer $ttl the cache time to live in second
         * @return object        the current instance
         */
        public function setCache($ttl = 0) {
            $this->cacheTtl = $ttl;
            $this->temporaryCacheTtl = $ttl;
            return $this;
        }
	
        /**
         * Enabled cache temporary for the current query not globally   
         * @param  integer $ttl the cache time to live in second
         * @return object        the current instance
         */
        public function cached($ttl = 0) {
            $this->temporaryCacheTtl = $ttl;
            return $this;
        }

        /**
         * Return the number query executed count for the current request
         * @return int
         */
        public function queryCount() {
            return $this->queryCount;
        }

        /**
         * Return the current query SQL string
         * @return string
         */
        public function getQuery() {
            return $this->query;
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
        public function setConnection(DatabaseConnection $connection = null) {
            $this->connection = $connection;
            return $this;
        }

        /**
         * Return the DatabaseQueryBuilder instance
         * @return object DatabaseQueryBuilder
         */
        public function getQueryBuilder() {
            return $this->queryBuilder;
        }

        /**
         * Set the DatabaseQueryBuilder instance
         * @param object DatabaseQueryBuilder $queryBuilder the DatabaseQueryBuilder object
         */
        public function setQueryBuilder(DatabaseQueryBuilder $queryBuilder = null) {
            $this->queryBuilder = $queryBuilder;
            return $this;
        }

        /**
         * Return the DatabaseCache instance
         * @return object DatabaseCache
         */
        public function getCacheInstance() {
            return $this->cacheInstance;
        }

        /**
         * Set the DatabaseCache instance
         * @param object DatabaseCache $cacheInstance the DatabaseCache object
         */
        public function setCacheInstance(DatabaseCache $cacheInstance = null) {
            $this->cacheInstance = $cacheInstance;
            return $this;
        }
    
        /**
         * Return the DatabaseQueryRunner instance
         * @return object DatabaseQueryRunner
         */
        public function getQueryRunner() {
            return $this->queryRunner;
        }

        /**
         * Set the DatabaseQueryRunner instance
         * @param object DatabaseQueryRunner $queryRunner the DatabaseQueryRunner object
         */
        public function setQueryRunner(DatabaseQueryRunner $queryRunner = null) {
            $this->queryRunner = $queryRunner;
            return $this;
        }

        /**
         * Return the data to be used for insert, update, etc.
         * @return array
         */
        public function getData() {
            return $this->data;
        }

        /**
         * Set the data to be used for insert, update, etc.
         * @param string|array $key the data key identified
         * @param mixed $value the data value
         * @param boolean $escape whether to escape or not the $value
         * @return object        the current Database instance
         */
        public function setData($key, $value = null, $escape = true) {
            if (is_array($key)) {
                foreach ($key as $k => $v) {
                    $this->setData($k, $v, $escape);
                }	
            } else {
                $this->data[$key] = $this->connection->escape($value, $escape);
            }
            return $this;
        }

        /**
         * Execute an SQL query
         * @param  string  $query the query SQL string
         * @param  boolean $returnAsList  indicate whether to return all record or just one row 
         * @param  boolean $returnAsArray return the result as array or not
         * @return mixed         the query result
         */
        public function query($query, $returnAsList = true, $returnAsArray = false) {
            $this->reset();
            $this->query = preg_replace('/\s\s+|\t\t+/', ' ', trim($query));
            $this->logger->info('Execute SQL query [' . $this->query . ']');

            $cacheExpire = $this->temporaryCacheTtl;

            //return to the initial cache time
            $this->temporaryCacheTtl = $this->cacheTtl;

            //the database cache content
            $cacheContent = $this->cacheInstance->setQuery($query)
                                                ->setReturnType($returnAsList)
                                                ->setReturnAsArray($returnAsArray)
                                                ->setCacheTtl($cacheExpire)
                                                ->getCacheContent();
            if (!$cacheContent) {
                $this->logger->info('No cache data found for this query or is not a SELECT query, get result from real database');
                //count the number of query execution to server
                $this->queryCount++;
                
                $queryResult = $this->queryRunner->setQuery($query)
                                                 ->setReturnType($returnAsList)
                                                 ->setReturnAsArray($returnAsArray)
                                                 ->execute();

                if (is_object($queryResult)) {
                    $this->result  = $queryResult->getResult();
                    $this->numRows = $queryResult->getNumRows();
                    //save the result into cache
                    $this->cacheInstance->saveCacheContent($this->result);
                }
            } else {
                $this->logger->info('The result for query [' . $this->query . '] already cached use it');
                $this->result = $cacheContent;
                $this->numRows = count($this->result);
            }
            return $this->result;
        }

         /**
         * Set the connection instance using database configuration file
         *
         * @return object|void
         */
        protected function setConnectionUsingConfigFile(){
            $dbConfigFromFile = $this->getDatabaseConfigFromFile();
            $connection = &class_loader('DatabaseConnection', 'classes/database');
            $connection->setConfig($dbConfigFromFile);
            $connection->connect();
            $this->connection = $connection;
            return $this;
        }


        /**
         * Get the database configuration using the configuration file
         
         * @return array the database configuration from file
         */
        protected function getDatabaseConfigFromFile() {
            $db = array();
            if (file_exists(CONFIG_PATH . 'database.php')) {
                //here don't use require_once because somewhere user can create database instance directly
                require CONFIG_PATH . 'database.php';
            }
            return $db;
        }

        /**
         * Update the dependency for some properties
         * @return object the current instance
         */
        protected function updateProperties() {
            //update queryBuilder with some properties needed
            if (is_object($this->queryBuilder)) {
                $this->queryBuilder->setConnection($this->connection);
            }

            //update queryRunner with some properties needed
            if (is_object($this->queryRunner)) {
                $this->queryRunner->setConnection($this->connection);
            }
            return $this;
        }
	
        /**
         * Reset the database class attributs to the initail values before each query.
         */
        private function reset() {
            //query builder reset
            $this->queryBuilder->reset();
            $this->numRows  = 0;
            $this->insertId = null;
            $this->query    = null;
            $this->result   = array();
            $this->data     = array();
        }
    }

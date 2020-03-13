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
    
    class DatabaseCache extends BaseClass {
	
        /**
         * The cache time to live in second. 0 means no need to use the cache feature
         * @var int
         */
        private $cacheTtl = 0;
	
        /**
         * The CacheInterface instance
         * @var object
         */
        private $cache = null;

        /**
         * The SQL query statment to get or save the result into cache
         * @var string
         */
        private $query = null;

        /**
         * If the current query is the SELECT query
         * @var boolean
         */
        private $isSelectQuery = false;

        /**
         * The status of the database cache
         * @var boolean
         */
        private $dbCacheStatus = false;

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
         * Construct new instance
         */
        public function __construct() {
            parent::__construct();
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
         * Set database cache time to live
         * @param integer $ttl the cache time to live in second
         * @return object        the current Database instance
         */
        public function setCacheTtl($ttl = 0) {
            $this->cacheTtl = $ttl;
            return $this;
        }

        /**
         * Return the cache instance
         * @return object CacheInterface
         */
        public function getCache() {
            return $this->cache;
        }

        /**
         * Set the cache instance
         * @param object CacheInterface $cache the cache object
         * @return object Database
         */
        public function setCache($cache) {
            $this->cache = $cache;
            return $this;
        }


        /**
         * Set the query return type as list or not
         * @param boolean $returnType
         * @return object the current instance
         */
        public function setReturnType($returnType) {
            $this->returnAsList = $returnType;
            return $this;
        }
        
        /**
         * Set the return as array or not
         * @param boolean $status the status if true will return as array
         * @return object the current instance
         */
        public function setReturnAsArray($status = true) {
            $this->returnAsArray = $status;
            return $this;
        }

    	
        /**
         * Get the cache content for this query
         * @see Database::query
         *      
         * @return mixed
         */
        public function getCacheContent() {
            //set some attributes values
            $this->setPropertiesValues();
            if(! $this->isSelectQuery || ! $this->dbCacheStatus){
                $this->logger->info('The cache is not enabled for this query or is not a SELECT query'); 
                return null;
            }
            $this->setCacheFromSuperInstanceIfNull();
            $this->logger->info('The cache is enabled for this query, try to get result from cache'); 
            $cacheKey = $this->getCacheKey();
            return $this->cache->get($cacheKey);
        }

        /**
         * Save the result of query into cache
         * @param string $key    the cache key
         * @param mixed $result the query result to save
         * @param int $expire the cache TTL
         *
         * @return boolean|null the status of the operation
         */
        public function saveCacheContent($result) {
            //set some attributes values
            $this->setPropertiesValues();
            if(! $this->isSelectQuery || ! $this->dbCacheStatus){
                return null;
            }
            $this->setCacheFromSuperInstanceIfNull();
            $cacheKey = $this->getCacheKey();
            $this->logger->info('Save the result for query [' . $this->query . '] into cache for future use');
            return $this->cache->set($cacheKey, $result, $this->cacheTtl);
        }

        /**
         * Set the cache instance using the super global instance if the current instance is null 
         * and the cache feature is enabled.
         */
        protected function setCacheFromSuperInstanceIfNull() {
            if (!is_object($this->cache)) {
                //can not call method with reference in argument
                //like $this->setCache(& get_instance()->cache);
                //use temporary variable
                $instance = & get_instance()->cache;
                $this->cache = $instance;
            }
        }

        /**
         * Set some properties values to use later
         */
        protected function setPropertiesValues() {
            //If is the SELECT query
            $this->isSelectQuery = stristr($this->query, 'SELECT') !== false;

             //if can use cache feature for this query
            $this->dbCacheStatus = get_config('cache_enable', false) && $this->cacheTtl > 0;
        }
        
        /**
         * Return the cache key for the current query
         * @see Database::query
         * 
         *  @return string
         */
        protected function getCacheKey() {
            return md5($this->query . $this->returnAsList . $this->returnAsArray);
        }

}

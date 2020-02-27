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
    class DatabaseCache extends BaseClass {
	
        /**
         * The cache time to live in second. 0 means no need to use the cache feature
         * @var int
         */
        private $cacheTtl = 0;
	
        /**
         * The cache instance
         * @var object
         */
        private $cacheInstance = null;

        /**
         * The SQL query statment to get or save the result into cache
         * @var string
         */
        private $query = null;

        /**
         * If the current query is the SELECT query
         * @var boolean
         */
        private $isSqlSELECTQuery = false;

        /**
         * The status of the database cache
         * @var boolean
         */
        private $dbCacheStatus = false;

        /**
         * Indicate if we need return result as list (boolean) 
         * or the data used to replace the placeholder (array)
         * @var array|boolean
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
        public function getCacheInstance() {
            return $this->cacheInstance;
        }

        /**
         * Set the cache instance
         * @param object CacheInterface $cache the cache object
         * @return object Database
         */
        public function setCacheInstance($cache) {
            $this->cacheInstance = $cache;
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
            if(! $this->isSqlSELECTQuery || ! $this->dbCacheStatus){
                $this->logger->info('The cache is not enabled for this query or is not a SELECT query'); 
                return null;
            }
            $this->logger->info('The cache is enabled for this query, try to get result from cache'); 
            $cacheKey = $this->getCacheKey();
            if (!is_object($this->cacheInstance)) {
                    //can not call method with reference in argument
                    //like $this->setCacheInstance(& get_instance()->cache);
                    //use temporary variable
                    $instance = & get_instance()->cache;
                    $this->cacheInstance = $instance;
            }
            return $this->cacheInstance->get($cacheKey);
        }

        /**
         * Save the result of query into cache
         * @param string $key    the cache key
         * @param mixed $result the query result to save
         * @param int $expire the cache TTL
         *
         * @return boolean the status of the operation
         */
        public function saveCacheContent($result) {
            //set some attributes values
            $this->setPropertiesValues();
            if(! $this->isSqlSELECTQuery || ! $this->dbCacheStatus){
                //just return true
                return true;
            }
            $cacheKey = $this->getCacheKey();
            $this->logger->info('Save the result for query [' . $this->query . '] into cache for future use');
            if (!is_object($this->cacheInstance)) {
                //can not call method with reference in argument
                //like $this->setCacheInstance(& get_instance()->cache);
                //use temporary variable
                $instance = & get_instance()->cache;
                $this->cacheInstance = $instance;
            }
            return $this->cacheInstance->set($cacheKey, $result, $this->cacheTtl);
        }

        /**
         * Set some properties values to use later
         */
        protected function setPropertiesValues() {
            //If is the SELECT query
            $this->isSqlSELECTQuery = stristr($this->query, 'SELECT') !== false;

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

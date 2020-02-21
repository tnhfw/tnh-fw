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
  class Database{
	
	/**
	 * The PDO instance
	 * @var object
	*/
    private $pdo                 = null;
    
	/**
	 * The database name used for the application
	 * @var string
	*/
	private $databaseName        = null;
	
	/**
	 * The number of rows returned by the last query
	 * @var int
	*/
    private $numRows             = 0;
	
	/**
	 * The last insert id for the primary key column that have auto increment or sequence
	 * @var mixed
	*/
    private $insertId            = null;
	
	/**
	 * The full SQL query statment after build for each command
	 * @var string
	*/
    private $query               = null;
	
	/**
	 * The error returned for the last query
	 * @var string
	*/
    private $error               = null;
	
	/**
	 * The result returned for the last query
	 * @var mixed
	*/
    private $result              = array();
	
	/**
	 * The cache default time to live in second. 0 means no need to use the cache feature
	 * @var int
	*/
	private $cacheTtl              = 0;
	
	/**
	 * The cache current time to live. 0 means no need to use the cache feature
	 * @var int
	*/
    private $temporaryCacheTtl   = 0;
	
	/**
	 * The number of executed query for the current request
	 * @var int
	*/
    private $queryCount          = 0;
	
	/**
	 * The default data to be used in the statments query INSERT, UPDATE
	 * @var array
	*/
    private $data                = array();
	
	/**
	 * The database configuration
	 * @var array
	*/
    private $config              = array();
	
	/**
	 * The logger instance
	 * @var object
	 */
    private $logger              = null;

    /**
    * The cache instance
    * @var object
    */
    private $cacheInstance       = null;

    /**
    * The benchmark instance
    * @var object
    */
    private $benchmarkInstance   = null;
	
	/**
    * The DatabaseQueryBuilder instance
    * @var object
    */
    private $queryBuilder        = null;


    /**
     * Construct new database
     * @param array $overwriteConfig the config to overwrite with the config set in database.php
     */
    public function __construct($overwriteConfig = array()){
        //Set Log instance to use
        $this->setLoggerFromParamOrCreateNewInstance(null);
		
		    //Set DatabaseQueryBuilder instance to use
		    $this->setQueryBuilderFromParamOrCreateNewInstance(null);

        //Set database configuration
        $this->setDatabaseConfiguration($overwriteConfig);
	
		    //cache time to live
    	  $this->temporaryCacheTtl = $this->cacheTtl;
    }

    /**
     * This is used to connect to database
     * @return bool 
     */
    public function connect(){
      $config = $this->getDatabaseConfiguration();
      if (! empty($config)){
        try{
            $this->pdo = new PDO($this->getDsnFromDriver(), $config['username'], $config['password']);
            $this->pdo->exec("SET NAMES '" . $config['charset'] . "' COLLATE '" . $config['collation'] . "'");
            $this->pdo->exec("SET CHARACTER SET '" . $config['charset'] . "'");
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            return true;
          }
          catch (PDOException $e){
            $this->logger->fatal($e->getMessage());
            show_error('Cannot connect to Database.');
            return false;
          }
      }
      else{
        show_error('Database configuration is not set.');
        return false;
      }
    }


    /**
     * Return the number of rows returned by the current query
     * @return int
     */
    public function numRows(){
      return $this->numRows;
    }

    /**
     * Return the last insert id value
     * @return mixed
     */
    public function insertId(){
      return $this->insertId;
    }

    /**
     * Show an error got from the current query (SQL command synthax error, database driver returned error, etc.)
     */
    public function error(){
  		if ($this->error){
  			show_error('Query: "' . $this->query . '" Error: ' . $this->error, 'Database Error');
  		}
    }

    /**
     * Get the result of one record rows returned by the current query
     * @param  boolean $returnSQLQueryOrResultType if is boolean and true will return the SQL query string.
     * If is string will determine the result type "array" or "object"
     * @return mixed       the query SQL string or the record result
     */
    public function get($returnSQLQueryOrResultType = false){
      $this->getQueryBuilder()->limit(1);
      $query = $this->getAll(true);
      if ($returnSQLQueryOrResultType === true){
        return $query;
      }
      else{
        return $this->query($query, false, $returnSQLQueryOrResultType == 'array');
      }
    }

    /**
     * Get the result of record rows list returned by the current query
     * @param  boolean|string $returnSQLQueryOrResultType if is boolean and true will return the SQL query string.
     * If is string will determine the result type "array" or "object"
     * @return mixed       the query SQL string or the record result
     */
    public function getAll($returnSQLQueryOrResultType = false){
	   $query = $this->getQueryBuilder()->getQuery();
	   if ($returnSQLQueryOrResultType === true){
      	return $query;
      }
      return $this->query($query, true, $returnSQLQueryOrResultType == 'array');
    }

    /**
     * Insert new record in the database
     * @param  array   $data   the record data if is empty will use the $this->data array.
     * @param  boolean $escape  whether to escape or not the values
     * @return mixed          the insert id of the new record or null
     */
    public function insert($data = array(), $escape = true){
      if (empty($data) && $this->getData()){
        //as when using $this->setData() may be the data already escaped
        $escape = false;
        $data = $this->getData();
      }
      $query = $this->getQueryBuilder()->insert($data, $escape)->getQuery();
      $result = $this->query($query);
      if ($result){
        $this->insertId = $this->pdo->lastInsertId();
		//if the table doesn't have the auto increment field or sequence, the value of 0 will be returned 
        return ! $this->insertId() ? true : $this->insertId();
      }
      return false;
    }

    /**
     * Update record in the database
     * @param  array   $data   the record data if is empty will use the $this->data array.
     * @param  boolean $escape  whether to escape or not the values
     * @return mixed          the update status
     */
    public function update($data = array(), $escape = true){
      if (empty($data) && $this->getData()){
        //as when using $this->setData() may be the data already escaped
        $escape = false;
        $data = $this->getData();
      }
      $query = $this->getQueryBuilder()->update($data, $escape)->getQuery();
      return $this->query($query);
    }

    /**
     * Delete the record in database
     * @return mixed the delete status
     */
    public function delete(){
		$query = $this->getQueryBuilder()->delete()->getQuery();
    	return $this->query($query);
    }

    /**
     * Set database cache time to live
     * @param integer $ttl the cache time to live in second
     * @return object        the current Database instance
     */
    public function setCache($ttl = 0){
      if ($ttl > 0){
        $this->cacheTtl = $ttl;
        $this->temporaryCacheTtl = $ttl;
      }
      return $this;
    }
	
	/**
	 * Enabled cache temporary for the current query not globally	
	 * @param  integer $ttl the cache time to live in second
	 * @return object        the current Database instance
	 */
  	public function cached($ttl = 0){
        if ($ttl > 0){
          $this->temporaryCacheTtl = $ttl;
        }
        return $this;
    }

    /**
     * Escape the data before execute query useful for security.
     * @param  mixed $data the data to be escaped
     * @param boolean $escaped whether we can do escape of not 
     * @return mixed       the data after escaped or the same data if not
     */
    public function escape($data, $escaped = true){
      return $escaped ? 
                      $this->getPdo()->quote(trim($data)) 
                      : $data; 
    }

    /**
     * Return the number query executed count for the current request
     * @return int
     */
    public function queryCount(){
      return $this->queryCount;
    }

    /**
     * Return the current query SQL string
     * @return string
     */
    public function getQuery(){
      return $this->query;
    }

    /**
     * Return the application database name
     * @return string
     */
    public function getDatabaseName(){
      return $this->databaseName;
    }

    /**
     * Return the PDO instance
     * @return object
     */
    public function getPdo(){
      return $this->pdo;
    }

    /**
     * Set the PDO instance
     * @param object $pdo the pdo object
	 * @return object Database
     */
    public function setPdo(PDO $pdo){
      $this->pdo = $pdo;
      return $this;
    }


    /**
     * Return the Log instance
     * @return Log
     */
    public function getLogger(){
      return $this->logger;
    }

    /**
     * Set the log instance
     * @param Log $logger the log object
	 * @return object Database
     */
    public function setLogger($logger){
      $this->logger = $logger;
      return $this;
    }

     /**
     * Return the cache instance
     * @return CacheInterface
     */
    public function getCacheInstance(){
      return $this->cacheInstance;
    }

    /**
     * Set the cache instance
     * @param CacheInterface $cache the cache object
	 * @return object Database
     */
    public function setCacheInstance($cache){
      $this->cacheInstance = $cache;
      return $this;
    }

    /**
     * Return the benchmark instance
     * @return Benchmark
     */
    public function getBenchmark(){
      return $this->benchmarkInstance;
    }

    /**
     * Set the benchmark instance
     * @param Benchmark $benchmark the benchmark object
	 * @return object Database
     */
    public function setBenchmark($benchmark){
      $this->benchmarkInstance = $benchmark;
      return $this;
    }
	
	
	/**
     * Return the DatabaseQueryBuilder instance
     * @return object DatabaseQueryBuilder
     */
    public function getQueryBuilder(){
      return $this->queryBuilder;
    }

    /**
     * Set the DatabaseQueryBuilder instance
     * @param object DatabaseQueryBuilder $queryBuilder the DatabaseQueryBuilder object
     */
    public function setQueryBuilder(DatabaseQueryBuilder $queryBuilder){
      $this->queryBuilder = $queryBuilder;
      return $this;
    }

    /**
     * Return the data to be used for insert, update, etc.
     * @return array
     */
    public function getData(){
      return $this->data;
    }

    /**
     * Set the data to be used for insert, update, etc.
     * @param string|array $key the data key identified
     * @param mixed $value the data value
     * @param boolean $escape whether to escape or not the $value
     * @return object        the current Database instance
     */
    public function setData($key, $value = null, $escape = true){
  	  if(is_array($key)){
    		foreach($key as $k => $v){
    			$this->setData($k, $v, $escape);
    		}	
  	  } else {
        $this->data[$key] = $this->escape($value, $escape);
  	  }
      return $this;
    }

     /**
     * Execute an SQL query
     * @param  string  $query the query SQL string
     * @param  boolean|array $all  if boolean this indicate whether to return all record or not, if array 
     * will 
     * @param  boolean $array return the result as array
     * @return mixed         the query result
     */
    public function query($query, $all = true, $array = false){
      $this->reset();
      $query = $this->getPreparedQuery($query, $all);
      $this->query = preg_replace('/\s\s+|\t\t+/', ' ', trim($query));
      
      $isSqlSELECTQuery = stristr($this->query, 'SELECT') !== false;

      $this->logger->info(
                          'Execute SQL query ['.$this->query.'], return type: ' 
                          . ($array?'ARRAY':'OBJECT') .', return as list: ' 
                          . (is_bool($all) && $all ? 'YES':'NO')
                        );
      //cache expire time
      $cacheExpire = $this->temporaryCacheTtl;
      
      //return to the initial cache time
      $this->temporaryCacheTtl = $this->cacheTtl;
      
      //config for cache
      $cacheEnable = get_config('cache_enable');
      
      //the database cache content
      $cacheContent = null;

      //if can use cache feature for this query
      $dbCacheStatus = $cacheEnable && $cacheExpire > 0;
    
      if ($dbCacheStatus && $isSqlSELECTQuery){
          $this->logger->info('The cache is enabled for this query, try to get result from cache'); 
          $cacheContent = $this->getCacheContentForQuery($query, $all, $array);  
      }
      
      if ( !$cacheContent){
        $sqlQuery = $this->runSqlQuery($query, $all, $array);
        if (is_object($sqlQuery)){
          if ($isSqlSELECTQuery){
            $this->setQueryResultForSelect($sqlQuery, $all, $array);
            $this->setCacheContentForQuery(
                                            $this->query, 
                                            $this->getCacheBenchmarkKeyForQuery($this->query, $all, $array), 
                                            $this->result, 
                                            $dbCacheStatus && $isSqlSELECTQuery, 
                                            $cacheExpire
                                          );
            if (! $this->result){
              $this->logger->info('No result where found for the query [' . $query . ']');
            }
          } else {
              $this->setQueryResultForNonSelect($sqlQuery);
              if (! $this->result){
                $this->setQueryError();
              }
          }
        }
      } else if ($isSqlSELECTQuery){
          $this->logger->info('The result for query [' .$this->query. '] already cached use it');
          $this->result = $cacheContent;
          $this->numRows = count($this->result);
      }
      return $this->result;
    }
	
	/**
     * Run the database SQL query and return the PDOStatment object
     * @see Database::query
     * 
     * @return object|void
     */
    public function runSqlQuery($query, $all, $array){
       //for database query execution time
        $benchmarkMarkerKey = $this->getCacheBenchmarkKeyForQuery($query, $all, $array);
        $benchmarkInstance = $this->getBenchmark();
        if (! is_object($benchmarkInstance)){
          $obj = & get_instance();
          $benchmarkInstance = $obj->benchmark; 
          $this->setBenchmark($benchmarkInstance);
        }
        
        $benchmarkInstance->mark('DATABASE_QUERY_START(' . $benchmarkMarkerKey . ')');
        //Now execute the query
        $sqlQuery = $this->pdo->query($query);
        
        //get response time for this query
        $responseTime = $benchmarkInstance->elapsedTime('DATABASE_QUERY_START(' . $benchmarkMarkerKey . ')', 'DATABASE_QUERY_END(' . $benchmarkMarkerKey . ')');
		    //TODO use the configuration value for the high response time currently is 1 second
        if ($responseTime >= 1 ){
            $this->logger->warning('High response time while processing database query [' .$query. ']. The response time is [' .$responseTime. '] sec.');
        }
		    //count the number of query execution to server
        $this->queryCount++;
		
        if ($sqlQuery !== false){
          return $sqlQuery;
        }
        $this->setQueryError();
    }
	
	
	 /**
	 * Return the database configuration
	 * @return array
	 */
	public  function getDatabaseConfiguration(){
	  return $this->config;
	}

   /**
    * Setting the database configuration using the configuration file and additional configuration from param
    * @param array $overwriteConfig the additional configuration to overwrite with the existing one
    * @param boolean $useConfigFile whether to use database configuration file
	  * @return object Database
    */
    public function setDatabaseConfiguration(array $overwriteConfig = array(), $useConfigFile = true){
        $db = array();
        if ($useConfigFile && file_exists(CONFIG_PATH . 'database.php')){
            //here don't use require_once because somewhere user can create database instance directly
            require CONFIG_PATH . 'database.php';
        }
          
        if (! empty($overwriteConfig)){
          $db = array_merge($db, $overwriteConfig);
        }
        $config = array(
          'driver' => 'mysql',
          'username' => 'root',
          'password' => '',
          'database' => '',
          'hostname' => 'localhost',
          'charset' => 'utf8',
          'collation' => 'utf8_general_ci',
          'prefix' => '',
          'port' => ''
        );
		
		$config = array_merge($config, $db);
		if (strstr($config['hostname'], ':')){
			$p = explode(':', $config['hostname']);
			if (count($p) >= 2){
			  $config['hostname'] = $p[0];
			  $config['port'] = $p[1];
			}
		 }
		 $this->databaseName = $config['database'];
		 $this->config = $config;
		 $this->logger->info(
								'The database configuration are listed below: ' 
								. stringfy_vars(array_merge(
															$this->config, 
															array('password' => string_hidden($this->config['password']))
												))
							);
	  
		 //Now connect to the database
		 $this->connect();
		 
		 //update queryBuilder with some properties needed
		 if(is_object($this->getQueryBuilder())){
			  $this->getQueryBuilder()->setDriver($config['driver']);
			  $this->getQueryBuilder()->setPrefix($config['prefix']);
			  $this->getQueryBuilder()->setPdo($this->getPdo());
		 }
		 return $this;
  }
	
   /**
   * Set the result for SELECT query using PDOStatment
   * @see Database::query
   */
    protected function setQueryResultForSelect($pdoStatment, $all, $array){
      //if need return all result like list of record
      if (is_bool($all) && $all){
          $this->result = ($array === false) ? $pdoStatment->fetchAll(PDO::FETCH_OBJ) : $pdoStatment->fetchAll(PDO::FETCH_ASSOC);
      }
      else{
          $this->result = ($array === false) ? $pdoStatment->fetch(PDO::FETCH_OBJ) : $pdoStatment->fetch(PDO::FETCH_ASSOC);
      }
      //Sqlite and pgsql always return 0 when using rowCount()
      if (in_array($this->config['driver'], array('sqlite', 'pgsql'))){
        $this->numRows = count($this->result);  
      }
      else{
        $this->numRows = $pdoStatment->rowCount(); 
      }
    }

    /**
     * Set the result for other command than SELECT query using PDOStatment
     * @see Database::query
     */
    protected function setQueryResultForNonSelect($pdoStatment){
      //Sqlite and pgsql always return 0 when using rowCount()
      if (in_array($this->config['driver'], array('sqlite', 'pgsql'))){
        $this->result = true; //to test the result for the query like UPDATE, INSERT, DELETE
        $this->numRows = 1; //TODO use the correct method to get the exact affected row
      }
      else{
          $this->result = $pdoStatment->rowCount() >= 0; //to test the result for the query like UPDATE, INSERT, DELETE
          $this->numRows = $pdoStatment->rowCount(); 
      }
    }

	

    /**
     * This method is used to get the PDO DSN string using the configured driver
     * @return string the DSN string
     */
    protected function getDsnFromDriver(){
      $config = $this->getDatabaseConfiguration();
      if (! empty($config)){
        $driver = $config['driver'];
        $driverDsnMap = array(
                                'mysql' => 'mysql:host=' . $config['hostname'] . ';' 
                                            . (($config['port']) != '' ? 'port=' . $config['port'] . ';' : '') 
                                            . 'dbname=' . $config['database'],
                                'pgsql' => 'pgsql:host=' . $config['hostname'] . ';' 
                                            . (($config['port']) != '' ? 'port=' . $config['port'] . ';' : '')
                                            . 'dbname=' . $config['database'],
                                'sqlite' => 'sqlite:' . $config['database'],
                                'oracle' => 'oci:dbname=' . $config['hostname'] 
                                            . (($config['port']) != '' ? ':' . $config['port'] : '')
                                            . '/' . $config['database']
                              );
        return isset($driverDsnMap[$driver]) ? $driverDsnMap[$driver] : '';
      }                   
      return null;
    }

     /**
     * Transform the prepared query like (?, ?, ?) into string format
     * @see Database::query
     *
     * @return string
     */
    protected function getPreparedQuery($query, $data){
      if (is_array($data)){
  			$x = explode('?', $query);
  			$q = '';
  			foreach($x as $k => $v){
  			  if (! empty($v)){
  				  $q .= $v . (isset($data[$k]) ? $this->escape($data[$k]) : '');
  			  }
  			}
  			return $q;
      }
      return $query;
    }

    /**
     * Get the cache content for this query
     * @see Database::query
     *      
     * @return mixed
     */
    protected function getCacheContentForQuery($query, $all, $array){
        $cacheKey = $this->getCacheBenchmarkKeyForQuery($query, $all, $array);
        if (! is_object($this->getCacheInstance())){
    			//can not call method with reference in argument
    			//like $this->setCacheInstance(& get_instance()->cache);
    			//use temporary variable
    			$instance = & get_instance()->cache;
    			$this->setCacheInstance($instance);
        }
        return $this->getCacheInstance()->get($cacheKey);
    }

    /**
     * Save the result of query into cache
     * @param string $query  the SQL query
     * @param string $key    the cache key
     * @param mixed $result the query result to save
     * @param boolean $status whether can save the query result into cache
     * @param int $expire the cache TTL
     */
     protected function setCacheContentForQuery($query, $key, $result, $status, $expire){
        if ($status){
            $this->logger->info('Save the result for query [' .$query. '] into cache for future use');
            if (! is_object($this->getCacheInstance())){
      				//can not call method with reference in argument
      				//like $this->setCacheInstance(& get_instance()->cache);
      				//use temporary variable
      				$instance = & get_instance()->cache;
      				$this->setCacheInstance($instance);
      			}
            $this->getCacheInstance()->set($key, $result, $expire);
        }
     }

    
    /**
     * Set error for database query execution
     */
    protected function setQueryError(){
      $error = $this->pdo->errorInfo();
      $this->error = isset($error[2]) ? $error[2] : '';
      $this->logger->error('The database query execution got error: ' . stringfy_vars($error));
	    //show error message
      $this->error();
    }

	  /**
     * Return the cache key for the given query
     * @see Database::query
     * 
     *  @return string
     */
    protected function getCacheBenchmarkKeyForQuery($query, $all, $array){
      if (is_array($all)){
        $all = 'array';
      }
      return md5($query . $all . $array);
    }
    
	   /**
     * Set the Log instance using argument or create new instance
     * @param object $logger the Log instance if not null
     */
    protected function setLoggerFromParamOrCreateNewInstance(Log $logger = null){
      if ($logger !== null){
        $this->setLogger($logger);
      }
      else{
          $this->logger =& class_loader('Log', 'classes');
          $this->logger->setLogger('Library::Database');
      }
    }
	
   /**
   * Set the DatabaseQueryBuilder instance using argument or create new instance
   * @param object $queryBuilder the DatabaseQueryBuilder instance if not null
   */
	protected function setQueryBuilderFromParamOrCreateNewInstance(DatabaseQueryBuilder $queryBuilder = null){
	  if ($queryBuilder !== null){
      $this->setQueryBuilder($queryBuilder);
	  }
	  else{
		  $this->queryBuilder =& class_loader('DatabaseQueryBuilder', 'classes');
	  }
	}

    /**
     * Reset the database class attributs to the initail values before each query.
     */
    private function reset(){
	   //query builder reset
      $this->getQueryBuilder()->reset();
      $this->numRows  = 0;
      $this->insertId = null;
      $this->query    = null;
      $this->error    = null;
      $this->result   = array();
      $this->data     = array();
    }

    /**
     * The class destructor
     */
    public function __destruct(){
      $this->pdo = null;
    }

}

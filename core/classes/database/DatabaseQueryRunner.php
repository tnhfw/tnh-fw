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
  class DatabaseQueryRunner{
      
    /**
	* The logger instance
	* @var object
	*/
    private $logger            = null;
    
  	/**
  	 * The last query result
  	 * @var object
  	*/
  	private $queryResult       = null;
  	
  	/**
    * The benchmark instance
    * @var object
    */
    private $benchmarkInstance = null;
    
    /**
	 * The SQL query statment to execute
	 * @var string
	*/
    private $query             = null;
    
    /**
	 * Indicate if we need return result as list (boolean) 
     * or the data used to replace the placeholder (array)
	 * @var array|boolean
	 */
     private $returnAsList     = true;
     
     
     /**
	   * Indicate if we need return result as array or not
     * @var boolean
	   */
     private $returnAsArray     = true;
     
     /**
     * The last PDOStatment instance
     * @var object
     */
     private $pdoStatment       = null;
     
     /**
  	 * The error returned for the last query
  	 * @var string
  	 */
     private $error             = null;
	
    /**
     * The PDO instance
     * @var object
    */
    private $pdo                = null;
  
    /**
     * The database driver name used
     * @var string
    */
    private $driver             = null;


	
    /**
     * Construct new DatabaseQueryRunner
     * @param object $pdo the PDO object
     * @param string $query the SQL query to be executed
     * @param boolean $returnAsList if need return as list or just one row
     * @param boolean $returnAsArray whether to return the result as array or not
     */
    public function __construct(PDO $pdo = null, $query = null, $returnAsList = true, $returnAsArray = false){
        if (is_object($pdo)){
          $this->pdo = $pdo;
        }
        $this->query         = $query;
        $this->returnAsList  = $returnAsList;
        $this->returnAsArray = $returnAsArray;
        $this->setLoggerFromParamOrCreateNewInstance(null);
    }
    
    /**
     * Run the database SQL query and return the DatabaseQueryResult object
     * @see Database::query
     * 
     * @return object|void
     */
    public function execute(){
        //reset instance
        $this->reset();
       
       //for database query execution time
        $benchmarkMarkerKey = $this->getBenchmarkKey();
        if (! is_object($this->benchmarkInstance)){
          $this->benchmarkInstance = & class_loader('Benchmark');
        }
        
        $this->logger->info('Execute SQL query [' . $this->query . ']');

        $this->benchmarkInstance->mark('DATABASE_QUERY_START(' . $benchmarkMarkerKey . ')');                
        //Now execute the query
        $this->pdoStatment = $this->pdo->query($this->query);
        
        //get response time for this query
        $responseTime = $this->benchmarkInstance->elapsedTime(
                                                                'DATABASE_QUERY_START(' . $benchmarkMarkerKey . ')', 
                                                                'DATABASE_QUERY_END(' . $benchmarkMarkerKey . ')'
                                                              );
		    //TODO use the configuration value for the high response time currently is 1 second
        if ($responseTime >= 1 ){
            $this->logger->warning(
                                    'High response time while processing database query [' . $this->query . ']. 
                                     The response time is [' .$responseTime. '] sec.'
                                  );
        }
		
        if ($this->pdoStatment !== false){
          $isSqlSELECTQuery = stristr($this->query, 'SELECT') !== false;
          if($isSqlSELECTQuery){
              $this->setResultForSelect();              
          }
          else{
              $this->setResultForNonSelect();
          }
          return $this->queryResult;
        }
        $this->setQueryRunnerError();
    }
	
   /**
   * Return the result for SELECT query
   * @see DatabaseQueryRunner::execute
   */
    protected function setResultForSelect(){
      //if need return all result like list of record
      $result = null;
      $numRows = 0;
      $fetchMode = PDO::FETCH_OBJ;
      if($this->returnAsArray){
        $fetchMode = PDO::FETCH_ASSOC;
      }
      if ($this->returnAsList){
          $result = $this->pdoStatment->fetchAll($fetchMode);
      }
      else{
          $result = $this->pdoStatment->fetch($fetchMode);
      }
      //Sqlite and pgsql always return 0 when using rowCount()
      if (in_array($this->driver, array('sqlite', 'pgsql'))){
        $numRows = count($result);  
      }
      else{
        $numRows = $this->pdoStatment->rowCount(); 
      }
      if(! is_object($this->queryResult)){
          $this->queryResult = & class_loader('DatabaseQueryResult', 'classes/database');
      }
      $this->queryResult->setResult($result);
      $this->queryResult->setNumRows($numRows);
    }

    /**
   * Return the result for non SELECT query
   * @see DatabaseQueryRunner::execute
   */
    protected function setResultForNonSelect(){
      //Sqlite and pgsql always return 0 when using rowCount()
      $result = false;
      $numRows = 0;
      if (in_array($this->driver, array('sqlite', 'pgsql'))){
        $result = true; //to test the result for the query like UPDATE, INSERT, DELETE
        $numRows = 1; //TODO use the correct method to get the exact affected row
      }
      else{
          //to test the result for the query like UPDATE, INSERT, DELETE
          $result  = $this->pdoStatment->rowCount() >= 0; 
          $numRows = $this->pdoStatment->rowCount(); 
      }
      if(! is_object($this->queryResult)){
          $this->queryResult = & class_loader('DatabaseQueryResult', 'classes/database');
      }
      $this->queryResult->setResult($result);
      $this->queryResult->setNumRows($numRows);
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
	 * @return object DatabaseQueryRunner
     */
    public function setBenchmark($benchmark){
      $this->benchmarkInstance = $benchmark;
      return $this;
    }
    
    /**
     * Return the database query result
     *
     * @return object DatabaseQueryResult
     */
    public function getQueryResult(){
      return $this->queryResult;
    }

    /**
     * Set the database query result instance
     * @param object $queryResult the query result
     *
	 * @return object DatabaseQueryRunner
     */
    public function setQueryResult(DatabaseQueryResult $queryResult){
      $this->queryResult = $queryResult;
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
	 * @return object DatabaseQueryRunner
     */
    public function setLogger($logger){
      $this->logger = $logger;
      return $this;
    }
    
    /**
     * Return the current query SQL string
     * @return string
     */
    public function getQuery(){
      return $this->query;
    }
    
    /**
     * Set the query SQL string
     * @param string $query the SQL query to set
     * @return object DatabaseQueryRunner
     */
    public function setQuery($query){
       $this->query = $query;
       return $this;
    }
    
    /**
     * Set the query return type as list or not
     * @param boolean $returnType
     * @return object DatabaseQueryRunner
     */
    public function setReturnType($returnType){
       $this->returnAsList = $returnType;
       return $this;
    }
    
    /**
     * Set the return as array or not
     * @param boolean $status the status if true will return as array
     * @return object DatabaseQueryRunner
     */
    public function setReturnAsArray($status = true){
       $this->returnAsArray = $status;
       return $this;
    }
    
    /**
     * Return the error for last query execution
     * @return string
     */
    public function getQueryError(){
      return $this->error;
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
     * @param PDO $pdo the pdo object
     * @return object DatabaseQueryRunner
     */
    public function setPdo(PDO $pdo = null){
      $this->pdo = $pdo;
      return $this;
    }
  
     /**
     * Return the database driver
     * @return string
     */
    public function getDriver(){
      return $this->driver;
    }

    /**
     * Set the database driver
     * @param string $driver the new driver
     * @return object DatabaseQueryRunner
     */
    public function setDriver($driver){
      $this->driver = $driver;
      return $this;
    }
    
    /**
     * Return the benchmark key for the current query
     * 
     *  @return string
     */
    protected function getBenchmarkKey(){
      return md5($this->query . $this->returnAsList . $this->returnAsArray);
    }
    
    /**
     * Set error for database query execution
     */
    protected function setQueryRunnerError(){
      $error = $this->pdo->errorInfo();
      $this->error = isset($error[2]) ? $error[2] : '';
      $this->logger->error('The database query execution got an error: ' . stringfy_vars($error));
	  //show error message
      show_error('Query: "' . $this->query . '" Error: ' . $this->error, 'Database Error');
    }
    
    /**
     * Set the Log instance using argument or create new instance
     * @param object $logger the Log instance if not null
     */
    protected function setLoggerFromParamOrCreateNewInstance(Log $logger = null){
      if ($logger !== null){
        $this->logger = $logger;
      }
      else{
          $this->logger =& class_loader('Log', 'classes');
          $this->logger->setLogger('Library::DatabaseQueryRunner');
      }
    }
    
    
    /**
    * Reset the instance before run each query
    */
    private function reset(){
        $this->error = null;
        $this->pdoStatment = null;
    }

}

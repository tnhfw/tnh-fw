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
	 * The SQL SELECT statment
	 * @var string
	*/
	private $select              = '*';
	
	/**
	 * The SQL FROM statment
	 * @var string
	*/
    private $from                = null;
	
	/**
	 * The SQL WHERE statment
	 * @var string
	*/
    private $where               = null;
	
	/**
	 * The SQL LIMIT statment
	 * @var string
	*/
    private $limit               = null;
	
	/**
	 * The SQL JOIN statment
	 * @var string
	*/
    private $join                = null;
	
	/**
	 * The SQL ORDER BY statment
	 * @var string
	*/
    private $orderBy             = null;
	
	/**
	 * The SQL GROUP BY statment
	 * @var string
	*/
    private $groupBy             = null;
	
	/**
	 * The SQL HAVING statment
	 * @var string
	*/
    private $having              = null;
	
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
	 * The prefix used in each database table
	 * @var string
	*/
    private $prefix              = null;
	
	/**
	 * The list of SQL valid operators
	 * @var array
	*/
    private $operatorList        = array('=','!=','<','>','<=','>=','<>');
    
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
	 * @var Log
	 */
    private $logger              = null;


    /**
    * The cache instance
    * @var CacheInterface
    */
    private $cacheInstance       = null;

     /**
    * The benchmark instance
    * @var Benchmark
    */
    private $benchmarkInstance   = null;


    /**
     * Construct new database
     * @param array $overwriteConfig the config to overwrite with the config set in database.php
     */
    public function __construct($overwriteConfig = array()){
        //Set Log instance to use
        $this->setLoggerFromParamOrCreateNewInstance(null);

        //Set global configuration using the config file
        $this->setDatabaseConfigurationFromConfigFile($overwriteConfig);
        
    		$this->temporaryCacheTtl = $this->cacheTtl;
    }

    /**
     * This is used to connect to database
     * @return bool 
     */
    public function connect(){
      $config = $this->getDatabaseConfiguration();
      if(! empty($config)){
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
     * Set the SQL FROM statment
     * @param  string|array $table the table name or array of table list
     * @return object        the current Database instance
     */
    public function from($table){
      if(is_array($table)){
        $froms = '';
        foreach($table as $key){
          $froms .= $this->prefix . $key . ', ';
        }
        $this->from = rtrim($froms, ', ');
      }
      else{
        $this->from = $this->prefix . $table;
      }
      return $this;
    }

    /**
     * Set the SQL SELECT statment
     * @param  string|array $fields the field name or array of field list
     * @return object        the current Database instance
     */
    public function select($fields){
      $select = (is_array($fields) ? implode(', ', $fields) : $fields);
      $this->select = ($this->select == '*' ? $select : $this->select . ', ' . $select);
      return $this;
    }

    /**
     * Set the SQL SELECT DISTINCT statment
     * @param  string $field the field name to distinct
     * @return object        the current Database instance
     */
    public function distinct($field){
      $distinct = ' DISTINCT ' . $field;
      $this->select = ($this->select == '*' ? $distinct : $this->select . ', ' . $distinct);

      return $this;
    }

    /**
     * Set the SQL function MAX in SELECT statment
     * @param  string $field the field name
     * @param  string $name  if is not null represent the alias used for this field in the result
     * @return object        the current Database instance
     */
    public function max($field, $name = null){
      $func = 'MAX(' . $field . ')' . (!is_null($name) ? ' AS ' . $name : '');
      $this->select = ($this->select == '*' ? $func : $this->select . ', ' . $func);
      return $this;
    }

    /**
     * Set the SQL function MIN in SELECT statment
     * @param  string $field the field name
     * @param  string $name  if is not null represent the alias used for this field in the result
     * @return object        the current Database instance
     */
    public function min($field, $name = null){
      $func = 'MIN(' . $field . ')' . (!is_null($name) ? ' AS ' . $name : '');
      $this->select = ($this->select == '*' ? $func : $this->select . ', ' . $func);
      return $this;
    }

    /**
     * Set the SQL function SUM in SELECT statment
     * @param  string $field the field name
     * @param  string $name  if is not null represent the alias used for this field in the result
     * @return object        the current Database instance
     */
    public function sum($field, $name = null){
      $func = 'SUM(' . $field . ')' . (!is_null($name) ? ' AS ' . $name : '');
      $this->select = ($this->select == '*' ? $func : $this->select . ', ' . $func);
      return $this;
    }

    /**
     * Set the SQL function COUNT in SELECT statment
     * @param  string $field the field name
     * @param  string $name  if is not null represent the alias used for this field in the result
     * @return object        the current Database instance
     */
    public function count($field = '*', $name = null){
      $func = 'COUNT(' . $field . ')' . (!is_null($name) ? ' AS ' . $name : '');
      $this->select = ($this->select == '*' ? $func : $this->select . ', ' . $func);
      return $this;
    }

    /**
     * Set the SQL function AVG in SELECT statment
     * @param  string $field the field name
     * @param  string $name  if is not null represent the alias used for this field in the result
     * @return object        the current Database instance
     */
    public function avg($field, $name = null){
      $func = 'AVG(' . $field . ')' . (!is_null($name) ? ' AS ' . $name : '');
      $this->select = ($this->select == '*' ? $func : $this->select . ', ' . $func);
      return $this;
    }

    /**
     * Set the SQL JOIN statment
     * @param  string $table  the join table name
     * @param  string $field1 the first field for join conditions	
     * @param  string $op     the join condition operator. If is null the default will be "="
     * @param  string $field2 the second field for join conditions
     * @param  string $type   the type of join (INNER, LEFT, RIGHT)
     * @return object        the current Database instance
     */
    public function join($table, $field1 = null, $op = null, $field2 = null, $type = ''){
      $on = $field1;
      $table = $this->prefix . $table;
      if(! is_null($op)){
        $on = (! in_array($op, $this->operatorList) ? $this->prefix . $field1 . ' = ' . $this->prefix . $op : $this->prefix . $field1 . ' ' . $op . ' ' . $this->prefix . $field2);
      }
      if (empty($this->join)){
        $this->join = ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
      }
      else{
        $this->join = $this->join . ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
      }
      return $this;
    }

    /**
     * Set the SQL INNER JOIN statment
     * @see  Database::join()
     * @return object        the current Database instance
     */
    public function innerJoin($table, $field1, $op = null, $field2 = ''){
      return $this->join($table, $field1, $op, $field2, 'INNER ');
    }

    /**
     * Set the SQL LEFT JOIN statment
     * @see  Database::join()
     * @return object        the current Database instance
     */
    public function leftJoin($table, $field1, $op = null, $field2 = ''){
      return $this->join($table, $field1, $op, $field2, 'LEFT ');
	}

	/**
     * Set the SQL RIGHT JOIN statment
     * @see  Database::join()
     * @return object        the current Database instance
     */
    public function rightJoin($table, $field1, $op = null, $field2 = ''){
      return $this->join($table, $field1, $op, $field2, 'RIGHT ');
    }

    /**
     * Set the SQL FULL OUTER JOIN statment
     * @see  Database::join()
     * @return object        the current Database instance
     */
    public function fullOuterJoin($table, $field1, $op = null, $field2 = ''){
    	return $this->join($table, $field1, $op, $field2, 'FULL OUTER ');
    }

    /**
     * Set the SQL LEFT OUTER JOIN statment
     * @see  Database::join()
     * @return object        the current Database instance
     */
    public function leftOuterJoin($table, $field1, $op = null, $field2 = ''){
      return $this->join($table, $field1, $op, $field2, 'LEFT OUTER ');
    }

    /**
     * Set the SQL RIGHT OUTER JOIN statment
     * @see  Database::join()
     * @return object        the current Database instance
     */
    public function rightOuterJoin($table, $field1, $op = null, $field2 = ''){
      return $this->join($table, $field1, $op, $field2, 'RIGHT OUTER ');
    }

    /**
     * Set the SQL WHERE CLAUSE for IS NULL
     * @param  string|array $field  the field name or array of field list
     * @param  string $andOr the separator type used 'AND', 'OR', etc.
     * @return object        the current Database instance
     */
    public function whereIsNull($field, $andOr = 'AND'){
      if(is_array($field)){
        foreach($field as $f){
        	$this->whereIsNull($f, $andOr);
        }
      }
      else{
           $this->setWhereStr($field.' IS NULL ', $andOr);
      }
      return $this;
    }

    /**
     * Set the SQL WHERE CLAUSE for IS NOT NULL
     * @param  string|array $field  the field name or array of field list
     * @param  string $andOr the separator type used 'AND', 'OR', etc.
     * @return object        the current Database instance
     */
    public function whereIsNotNull($field, $andOr = 'AND'){
      if(is_array($field)){
        foreach($field as $f){
          $this->whereIsNotNull($f, $andOr);
        }
      }
      else{
          $this->setWhereStr($field.' IS NOT NULL ', $andOr);
      }
      return $this;
    }
    
    /**
     * Set the SQL WHERE CLAUSE statment
     * @param  string|array  $where the where field or array of field list
     * @param  array|string  $op     the condition operator. If is null the default will be "="
     * @param  mixed  $val    the where value
     * @param  string  $type   the type used for this where clause (NOT, etc.)
     * @param  string  $andOr the separator type used 'AND', 'OR', etc.
     * @param  boolean $escape whether to escape or not the $val
     * @return object        the current Database instance
     */
    public function where($where, $op = null, $val = null, $type = '', $andOr = 'AND', $escape = true){
      $whereStr = '';
      if (is_array($where)){
        $whereStr = $this->getWhereStrIfIsArray($where, $type, $andOr, $escape);
      }
      else{
        if(is_array($op)){
          $whereStr = $this->getWhereStrIfOperatorIsArray($where, $op, $type, $escape);
        } else {
          $whereStr = $this->getWhereStrForOperator($where, $op, $val, $type, $escape = true);
        }
      }
      $this->setWhereStr($whereStr, $andOr);
      return $this;
    }

    /**
     * Set the SQL WHERE CLAUSE statment using OR
     * @see  Database::where()
     * @return object        the current Database instance
     */
    public function orWhere($where, $op = null, $val = null, $escape = true){
      return $this->where($where, $op, $val, '', 'OR', $escape);
    }


    /**
     * Set the SQL WHERE CLAUSE statment using AND and NOT
     * @see  Database::where()
     * @return object        the current Database instance
     */
    public function notWhere($where, $op = null, $val = null, $escape = true){
      return $this->where($where, $op, $val, 'NOT ', 'AND', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment using OR and NOT
     * @see  Database::where()
     * @return object        the current Database instance
     */
    public function orNotWhere($where, $op = null, $val = null, $escape = true){
    	return $this->where($where, $op, $val, 'NOT ', 'OR', $escape);
    }

    /**
     * Set the opened parenthesis for the complex SQL query
     * @param  string $type   the type of this grouped (NOT, etc.)
     * @param  string $andOr the multiple conditions separator (AND, OR, etc.)
     * @return object        the current Database instance
     */
    public function groupStart($type = '', $andOr = ' AND'){
      if (empty($this->where)){
        $this->where = $type . ' (';
      }
      else{
          if(substr($this->where, -1) == '('){
            $this->where .= $type . ' (';
          }
          else{
          	$this->where .= $andOr . ' ' . $type . ' (';
          }
      }
      return $this;
    }

    /**
     * Set the opened parenthesis for the complex SQL query using NOT type
     * @see  Database::groupStart()
     * @return object        the current Database instance
     */
    public function notGroupStart(){
      return $this->groupStart('NOT');
    }

    /**
     * Set the opened parenthesis for the complex SQL query using OR for separator
     * @see  Database::groupStart()
     * @return object        the current Database instance
     */
    public function orGroupStart(){
      return $this->groupStart('', ' OR');
    }

     /**
     * Set the opened parenthesis for the complex SQL query using OR for separator and NOT for type
     * @see  Database::groupStart()
     * @return object        the current Database instance
     */
    public function orNotGroupStart(){
      return $this->groupStart('NOT', ' OR');
    }

    /**
     * Close the parenthesis for the grouped SQL
     * @return object        the current Database instance
     */
    public function groupEnd(){
      $this->where .= ')';
      return $this;
    }

    /**
     * Set the SQL WHERE CLAUSE statment for IN
     * @param  string  $field  the field name for IN statment
     * @param  array   $keys   the list of values used
     * @param  string  $type   the condition separator type (NOT)
     * @param  string  $andOr the multiple conditions separator (OR, AND)
     * @param  boolean $escape whether to escape or not the values
     * @return object        the current Database instance
     */
    public function in($field, array $keys, $type = '', $andOr = 'AND', $escape = true){
      $_keys = array();
      foreach ($keys as $k => $v){
        if(is_null($v)){
          $v = '';
        }
        $_keys[] = (is_numeric($v) ? $v : $this->escape($v, $escape));
      }
      $keys = implode(', ', $_keys);
      $whereStr = $field . ' ' . $type . ' IN (' . $keys . ')';
      $this->setWhereStr($whereStr, $andOr);
      return $this;
    }

    /**
     * Set the SQL WHERE CLAUSE statment for NOT IN with AND separator
     * @see  Database::in()
     * @return object        the current Database instance
     */
    public function notIn($field, array $keys, $escape = true){
      return $this->in($field, $keys, 'NOT ', 'AND', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for IN with OR separator
     * @see  Database::in()
     * @return object        the current Database instance
     */
    public function orIn($field, array $keys, $escape = true){
      return $this->in($field, $keys, '', 'OR', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for NOT IN with OR separator
     * @see  Database::in()
     * @return object        the current Database instance
     */
    public function orNotIn($field, array $keys, $escape = true){
      return $this->in($field, $keys, 'NOT ', 'OR', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for BETWEEN
     * @param  string  $field  the field used for the BETWEEN statment
     * @param  mixed  $value1 the BETWEEN begin value
     * @param  mixed  $value2 the BETWEEN end value
     * @param  string  $type   the condition separator type (NOT)
     * @param  string  $andOr the multiple conditions separator (OR, AND)
     * @param  boolean $escape whether to escape or not the values
     * @return object        the current Database instance
     */
    public function between($field, $value1, $value2, $type = '', $andOr = 'AND', $escape = true){
      if(is_null($value1)){
        $value1 = '';
      }
      if(is_null($value2)){
        $value2 = '';
      }
      $whereStr = $field . ' ' . $type . ' BETWEEN ' . $this->escape($value1, $escape) . ' AND ' . $this->escape($value2, $escape);
      $this->setWhereStr($whereStr, $andOr);
      return $this;
    }

    /**
     * Set the SQL WHERE CLAUSE statment for BETWEEN with NOT type and AND separator
     * @see  Database::between()
     * @return object        the current Database instance
     */
    public function notBetween($field, $value1, $value2, $escape = true){
      return $this->between($field, $value1, $value2, 'NOT ', 'AND', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for BETWEEN with OR separator
     * @see  Database::between()
     * @return object        the current Database instance
     */
    public function orBetween($field, $value1, $value2, $escape = true){
      return $this->between($field, $value1, $value2, '', 'OR', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for BETWEEN with NOT type and OR separator
     * @see  Database::between()
     * @return object        the current Database instance
     */
    public function orNotBetween($field, $value1, $value2, $escape = true){
      return $this->between($field, $value1, $value2, 'NOT ', 'OR', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for LIKE
     * @param  string  $field  the field name used in LIKE statment
     * @param  string  $data   the LIKE value for this field including the '%', and '_' part
     * @param  string  $type   the condition separator type (NOT)
     * @param  string  $andOr the multiple conditions separator (OR, AND)
     * @param  boolean $escape whether to escape or not the values
     * @return object        the current Database instance
     */
    public function like($field, $data, $type = '', $andOr = 'AND', $escape = true){
      if(empty($data)){
        $data = '';
      }
      $this->setWhereStr($field . ' ' . $type . ' LIKE ' . ($this->escape($data, $escape)), $andOr);
      return $this;
    }

    /**
     * Set the SQL WHERE CLAUSE statment for LIKE with OR separator
     * @see  Database::like()
     * @return object        the current Database instance
     */
    public function orLike($field, $data, $escape = true){
      return $this->like($field, $data, '', 'OR', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for LIKE with NOT type and AND separator
     * @see  Database::like()
     * @return object        the current Database instance
     */
    public function notLike($field, $data, $escape = true){
      return $this->like($field, $data, 'NOT ', 'AND', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for LIKE with NOT type and OR separator
     * @see  Database::like()
     * @return object        the current Database instance
     */
    public function orNotLike($field, $data, $escape = true){
      return $this->like($field, $data, 'NOT ', 'OR', $escape);
    }

    /**
     * Set the SQL LIMIT statment
     * @param  int $limit    the limit offset. If $limitEnd is null this will be the limit count
     * like LIMIT n;
     * @param  int $limitEnd the limit count
     * @return object        the current Database instance
     */
    public function limit($limit, $limitEnd = null){
      if(empty($limit)){
        return;
      }
      if (! is_null($limitEnd)){
        $this->limit = $limit . ', ' . $limitEnd;
      }
      else{
        $this->limit = $limit;
      }
      return $this;
    }

    /**
     * Set the SQL ORDER BY CLAUSE statment
     * @param  string $orderBy   the field name used for order
     * @param  string $orderDir the order direction (ASC or DESC)
     * @return object        the current Database instance
     */
    public function orderBy($orderBy, $orderDir = ' ASC'){
        if(stristr($orderBy, ' ') || $orderBy == 'rand()'){
          $this->orderBy = empty($this->orderBy) ? $orderBy : $this->orderBy . ', ' . $orderBy;
        }
        else{
          $this->orderBy = empty($this->orderBy) ? ($orderBy . ' ' 
                            . strtoupper($orderDir)) : $this->orderBy 
                            . ', ' . $orderBy . ' ' . strtoupper($orderDir);
        }
      return $this;
    }

    /**
     * Set the SQL GROUP BY CLAUSE statment
     * @param  string|array $field the field name used or array of field list
     * @return object        the current Database instance
     */
    public function groupBy($field){
      if(is_array($field)){
        $this->groupBy = implode(', ', $field);
      }
      else{
        $this->groupBy = $field;
      }
      return $this;
    }

    /**
     * Set the SQL HAVING CLAUSE statment
     * @param  string  $field  the field name used for HAVING statment
     * @param  string|array  $op     the operator used or array
     * @param  mixed  $val    the value for HAVING comparaison
     * @param  boolean $escape whether to escape or not the values
     * @return object        the current Database instance
     */
    public function having($field, $op = null, $val = null, $escape = true){
      if(is_array($op)){
        $x = explode('?', $field);
        $w = '';
        foreach($x as $k => $v){
  	      if(!empty($v)){
            if(isset($op[$k]) && is_null($op[$k])){
              $op[$k] = '';
            }
  	      	$w .= $v . (isset($op[$k]) ? $this->escape($op[$k], $escape) : '');
  	      }
      	}
        $this->having = $w;
      }
      else if (! in_array($op, $this->operatorList)){
        if(is_null($op)){
          $op = '';
        }
        $this->having = $field . ' > ' . ($this->escape($op, $escape));
      }
      else{
        if(is_null($val)){
          $val = '';
        }
        $this->having = $field . ' ' . $op . ' ' . ($this->escape($val, $escape));
      }
      return $this;
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
  		if($this->error){
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
      $this->limit = 1;
      $query = $this->getAll(true);
      if($returnSQLQueryOrResultType === true){
        return $query;
      }
      else{
        return $this->query( $query, false, (($returnSQLQueryOrResultType == 'array') ? true : false) );
      }
    }

    /**
     * Get the result of record rows list returned by the current query
     * @param  boolean $returnSQLQueryOrResultType if is boolean and true will return the SQL query string.
     * If is string will determine the result type "array" or "object"
     * @return mixed       the query SQL string or the record result
     */
    public function getAll($returnSQLQueryOrResultType = false){
      $query = 'SELECT ' . $this->select . ' FROM ' . $this->from;
      if (! empty($this->join)){
        $query .= $this->join;
      }
	  
      if (! empty($this->where)){
        $query .= ' WHERE ' . $this->where;
      }

      if (! empty($this->groupBy)){
        $query .= ' GROUP BY ' . $this->groupBy;
      }

      if (! empty($this->having)){
        $query .= ' HAVING ' . $this->having;
      }

      if (! empty($this->orderBy)){
          $query .= ' ORDER BY ' . $this->orderBy;
      }

      if(! empty($this->limit)){
      	$query .= ' LIMIT ' . $this->limit;
      }
	  
	   if($returnSQLQueryOrResultType === true){
      	return $query;
      }
      else{
    	   return $this->query($query, true, (($returnSQLQueryOrResultType == 'array') ? true : false) );
      }
    }

    /**
     * Insert new record in the database
     * @param  array   $data   the record data if is empty will use the $this->data array.
     * @param  boolean $escape  whether to escape or not the values
     * @return mixed          the insert id of the new record or null
     */
    public function insert($data = array(), $escape = true){
      if(empty($data) && $this->getData()){
        //as when using $this->setData() the data already escaped
        $escape = false;
        $data = $this->getData();
      }

      $columns = array_keys($data);
      $column = implode(',', $columns);
      $val = implode(', ', ($escape ? array_map(array($this, 'escape'), $data) : $data));

      $query = 'INSERT INTO ' . $this->from . ' (' . $column . ') VALUES (' . $val . ')';
      $query = $this->query($query);

      if ($query){
        if(! $this->pdo){
          $this->connect();
        }
        $this->insertId = $this->pdo->lastInsertId();
        return $this->insertId();
      }
      else{
		  return false;
      }
    }

    /**
     * Update record in the database
     * @param  array   $data   the record data if is empty will use the $this->data array.
     * @param  boolean $escape  whether to escape or not the values
     * @return mixed          the update status
     */
    public function update($data = array(), $escape = true){
      $query = 'UPDATE ' . $this->from . ' SET ';
      $values = array();
      if(empty($data) && $this->getData()){
        //as when using $this->setData() the data already escaped
        $escape = false;
        $data = $this->getData();
      }
      foreach ($data as $column => $val){
        $values[] = $column . ' = ' . ($this->escape($val, $escape));
      }
      $query .= implode(', ', $values);
      if (! empty($this->where)){
        $query .= ' WHERE ' . $this->where;
      }

      if (! empty($this->orderBy)){
        $query .= ' ORDER BY ' . $this->orderBy;
      }

      if (! empty($this->limit)){
        $query .= ' LIMIT ' . $this->limit;
      }
      return $this->query($query);
    }

    /**
     * Delete the record in database
     * @return mixed the delete status
     */
    public function delete(){
    	$query = 'DELETE FROM ' . $this->from;

    	if (! empty($this->where)){
    		$query .= ' WHERE ' . $this->where;
      	}

    	if (! empty($this->orderBy)){
    	  $query .= ' ORDER BY ' . $this->orderBy;
      	}

    	if (! empty($this->limit)){
    		$query .= ' LIMIT ' . $this->limit;
      	}

    	if($query == 'DELETE FROM ' . $this->from && $this->config['driver'] != 'sqlite'){  
    		$query = 'TRUNCATE TABLE ' . $this->from;
      }
    	return $this->query($query);
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
      $query = $this->transformPreparedQuery($query, $all);
      $this->query = preg_replace('/\s\s+|\t\t+/', ' ', trim($query));
      
      $isSqlSELECTQuery = stristr($this->query, 'SELECT');

      $this->logger->info('Execute SQL query ['.$this->query.'], return type: ' . ($array?'ARRAY':'OBJECT') .', return as list: ' . ($all ? 'YES':'NO'));
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
          $cacheContent = $this->getCacheContentForQuery($query, $all, $array);  
      }
      else{
		      $this->logger->info('The cache is not enabled for this query or is not the SELECT query, get the result directly from real database');
      }
     
      if (! $cacheContent && $isSqlSELECTQuery){
        $sqlQuery = $this->runSqlQuery($query, $all, $array);
        if (is_object($sqlQuery)){
            $this->setQueryResultForSelect($sqlQuery, $all, $array);
            $this->setCacheContentForQuery(
                                            $this->query, 
                                            $this->getCacheBenchmarkKeyForQuery($this->query, $all, $array), 
                                            $this->result, 
                                            $dbCacheStatus && $isSqlSELECTQuery, 
                                            $this->temporaryCacheTtl
                                          );
        }
      }
      else if ((! $cacheContent && !$isSqlSELECTQuery) || ($cacheContent && !$isSqlSELECTQuery)){
    		$sqlQuery = $this->runSqlQuery($query, $all, $array);
    		if(is_object($sqlQuery)){
          $this->setQueryResultForNonSelect($sqlQuery);
    		}
        if (! $this->result){
          $this->setQueryError();
        }
      }
      else{
        $this->logger->info('The result for query [' .$this->query. '] already cached use it');
        $this->result = $cacheContent;
	     	$this->numRows = count($this->result);
      }
      $this->queryCount++;
      if(! $this->result){
        $this->logger->info('No result where found for the query [' . $query . ']');
      }
      return $this->result;
    }

    /**
     * Set database cache time to live
     * @param integer $ttl the cache time to live in second
     * @return object        the current Database instance
     */
    public function setCache($ttl = 0){
      if($ttl > 0){
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
      if($ttl > 0){
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
      if($escaped){
        if(! $this->pdo){
          $this->connect();
        }
        return $this->pdo->quote(trim($data)); 
      }
      return $data;
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
     * Return the database configuration
     * @return array
     */
    public  function getDatabaseConfiguration(){
      return $this->config;
    }

    /**
     * set the database configuration
     * @param array $config the configuration
     */
    public function setDatabaseConfiguration(array $config){
      $this->config = array_merge($this->config, $config);
      $this->prefix = $this->config['prefix'];
      $this->databaseName = $this->config['database'];
      $this->logger->info('The database configuration are listed below: ' . stringfy_vars(array_merge($this->config, array('password' => string_hidden($this->config['password'])))));
      return $this;
    }

    /**
     * Return the PDO instance
     * @return PDO
     */
    public function getPdo(){
      return $this->pdo;
    }

    /**
     * Set the PDO instance
     * @param PDO $pdo the pdo object
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
     * @param Benchmark $cache the cache object
     */
    public function setBenchmark($benchmark){
      $this->benchmarkInstance = $benchmark;
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
     * @param string $key the data key identified
     * @param mixed $value the data value
     * @param boolean $escape whether to escape or not the $value
     * @return object        the current Database instance
     */
    public function setData($key, $value, $escape = true){
      $this->data[$key] = $this->escape($value, $escape);
      return $this;
    }

    /**
     * Set the Log instance using argument or create new instance
     * @param object $logger the Log instance if not null
     */
    protected function setLoggerFromParamOrCreateNewInstance(Log $logger = null){
      if($logger !== null){
        $this->logger = $logger;
      }
      else{
          $this->logger =& class_loader('Log', 'classes');
          $this->logger->setLogger('Library::Database');
      }
    }

   /**
    * Setting the database configuration using the configuration file
    * @param array $overwriteConfig the additional configuration to overwrite with the existing one
    */
    protected function setDatabaseConfigurationFromConfigFile(array $overwriteConfig = array()){
        $db = array();
        if(file_exists(CONFIG_PATH . 'database.php')){
            //here don't use require_once because somewhere user can create database instance directly
            require CONFIG_PATH . 'database.php';
        }
          
        if(! empty($overwriteConfig)){
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
        $this->setDatabaseConfiguration(array_merge($config, $db));
        $this->determinePortConfigurationFromHostname();  
    }

    /**
     * This method is used to get the PDO DSN string using th configured driver
     * @return string the DSN string
     */
    protected function getDsnFromDriver(){
      $config = $this->getDatabaseConfiguration();
      if(! empty($config)){
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
            return isset($driverDsnMap[$config['driver']]) ? $driverDsnMap[$config['driver']] : '';
      }                   
      return null;
    }

    /**
     * Set the database server port configuration using the current hostname like localhost:3309 
     * @return void
     */
    protected function determinePortConfigurationFromHostname(){
      if(strstr($this->config['hostname'], ':')){
        $p = explode(':', $this->config['hostname']);
        if(count($p) > 2){
          $this->setDatabaseConfiguration(array(
            'hostname' => $p[0],
            'port' => $p[1]
          ));
        }
      }
    }

   /**
     * Get the SQL WHERE clause using array column => value
     * @see Database::where
     *
     * @return string
     */
    protected function getWhereStrIfIsArray(array $where, $type = '', $andOr = 'AND', $escape = true){
        $_where = array();
        foreach ($where as $column => $data){
          if(is_null($data)){
            $data = '';
          }
          $_where[] = $type . $column . ' = ' . ($this->escape($data, $escape));
        }
        $where = implode(' '.$andOr.' ', $_where);
        return $where;
    }

     /**
     * Get the SQL WHERE clause when operator argument is an array
     * @see Database::where
     *
     * @return string
     */
    protected function getWhereStrIfOperatorIsArray($where, array $op, $type = '', $escape = true){
       $x = explode('?', $where);
       $w = '';
        foreach($x as $k => $v){
          if(! empty($v)){
              if(isset($op[$k]) && is_null($op[$k])){
                $op[$k] = '';
              }
              $w .= $type . $v . (isset($op[$k]) ? ($this->escape($op[$k], $escape)) : '');
          }
        }
        return $w;
    }

    /**
     * Get the default SQL WHERE clause using operator = or the operator argument
     * @see Database::where
     *
     * @return string
     */
    protected function getWhereStrForOperator($where, $op = null, $val = null, $type = '', $escape = true){
       $w = '';
       if (! in_array((string)$op, $this->operatorList)){
          if(is_null($op)){
            $op = '';
          }
          $w = $type . $where . ' = ' . ($this->escape($op, $escape));
        }
        else{
          if(is_null($val)){
            $val = '';
          }
          $w = $type . $where . $op . ($this->escape($val, $escape));
        }
        return $w;
      }

      /**
       * Set the $this->where property 
       * @param string $whereStr the WHERE clause string
       * @param  string  $andOr the separator type used 'AND', 'OR', etc.
       */
      protected function setWhereStr($whereStr, $andOr = 'AND'){
        if (empty($this->where)){
          $this->where = $whereStr;
        }
        else{
          if(substr($this->where, -1) == '('){
            $this->where = $this->where . ' ' . $whereStr;
          }
          else{
            $this->where = $this->where . ' '.$andOr.' ' . $whereStr;
          }
        }
      }

        /**
     * Transform the prepared query like (?, ?, ?) into string format
     * @see Database::query
     *
     * @return string
     */
    protected function transformPreparedQuery($query, $data){
      if(is_array($data)){
        $x = explode('?', $query);
        $q = '';
        foreach($x as $k => $v){
          if(! empty($v)){
            $q .= $v . (isset($data[$k]) ? $this->escape($data[$k]) : '');
          }
        }
        return $q;
      }
      return $query;
    }

    /**
     * Return the cache key for the query
     * @see Database::query
     * 
     *  @return string
     */
    protected function getCacheBenchmarkKeyForQuery($query, $all, $array){
      return md5($query . $all . $array);
    }

    /**
     * Get the cache content for this query
     * @see Database::query
     *      
     * @return mixed
     */
    protected function getCacheContentForQuery($query, $all, $array){
       $this->logger->info('The cache is enabled for this query, try to get result from cache'); 
        $cacheKey = $this->getCacheBenchmarkKeyForQuery($query, $all, $array);
        if(is_object($this->cacheInstance)){
          return $this->cacheInstance->get($cacheKey);
        }
        $instance = & get_instance()->cache;
        $this->setCacheInstance($instance);
        return $instance->get($cacheKey);
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
            $this->getCacheInstance()->set($key, $result, $expire);
        }
     }

    /**
     * Set the result for SELECT query using PDOStatment
     * @see Database::query
     */
    protected function setQueryResultForSelect($pdoStatment, $all = true, $array = false){
      //if need return all result like list of record
      if ($all){
          $this->result = ($array === false) ? $pdoStatment->fetchAll(PDO::FETCH_OBJ) : $pdoStatment->fetchAll(PDO::FETCH_ASSOC);
      }
      else{
          $this->result = ($array === false) ? $pdoStatment->fetch(PDO::FETCH_OBJ) : $pdoStatment->fetch(PDO::FETCH_ASSOC);
      }
      //Sqlite and pgsql always return 0 when using rowCount()
      if(in_array($this->config['driver'], array('sqlite', 'pgsql'))){
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
      if(in_array($this->config['driver'], array('sqlite', 'pgsql'))){
        $this->result = 1; //to test the result for the query like UPDATE, INSERT, DELETE
        $this->numRows = 1;  
      }
      else{
          $this->result = $pdoStatment->rowCount() >= 0; //to test the result for the query like UPDATE, INSERT, DELETE
          $this->numRows = $pdoStatment->rowCount(); 
      }
    }

    /**
     * Set error for database query execution
     */
    protected function setQueryError(){
      $error = $this->pdo->errorInfo();
      $this->error = isset($error[2]) ? $error[2] : '';
      $this->logger->error('The database query execution got error: ' . stringfy_vars($error));
      $this->error();
    }

    /**
     * Run the database SQL query and return the PDOStatment object
     * @see Database::query
     * 
     * @return object|void
     */
    protected function runSqlQuery($query, $all, $array){
       //for database query execution time
        $benchmarkMarkerKey = $this->getCacheBenchmarkKeyForQuery($query, $all, $array);
        $benchmarkInstance = $this->getBenchmark();
        if(! is_object($benchmarkInstance)){
          $obj = & get_instance();
          $benchmarkInstance = $obj->benchmark; 
          $this->setBenchmark($benchmarkInstance);
        }
        if(! $this->pdo){
            $this->connect();
        }
        
        $benchmarkInstance->mark('DATABASE_QUERY_START(' . $benchmarkMarkerKey . ')');
        //Now execute the query
        $sqlQuery = $this->pdo->query($query);
        
        //get response time for this query
        $responseTime = $benchmarkInstance->elapsedTime('DATABASE_QUERY_START(' . $benchmarkMarkerKey . ')', 'DATABASE_QUERY_END(' . $benchmarkMarkerKey . ')');
        //TODO use the configuration value for the high response time currently is 1 second
        if($responseTime >= 1 ){
            $this->logger->warning('High response time while processing database query [' .$query. ']. The response time is [' .$responseTime. '] sec.');
        }
        if($sqlQuery){
          return $sqlQuery;
        }
        $this->setQueryError();
    }

  /**
   * Reset the database class attributs to the initail values before each query.
   */
  private function reset(){
    $this->select   = '*';
    $this->from     = null;
    $this->where    = null;
    $this->limit    = null;
    $this->orderBy  = null;
    $this->groupBy  = null;
    $this->having   = null;
    $this->join     = null;
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

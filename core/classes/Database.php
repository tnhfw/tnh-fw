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
  class Database
  {

    private $pdo = null;
    private $databaseName  = null;
    private $select        = '*';
    private $from          = null;
    private $where         = null;
    private $limit         = null;
    private $join          = null;
    private $orderBy       = null;
    private $groupBy       = null;
    private $having        = null;
    private $numRows       = 0;
    private $insertId      = null;
    private $query         = null;
    private $error         = null;
    private $result        = array();
    private $prefix        = null;
    private $op            = array('=','!=','<','>','<=','>=','<>');
    private $cacheTtl   = 0;
    private $temporaryCacheTtl   = 0;
    private $queryCount    = 0;
    private $data          = array();
    private static $config = array();
    private $logger;

    /**
     * Construct new database
     * @param array $overwriteConfig the config to overwrite the config set in database.php
     */
    public function __construct($overwriteConfig = array()){
        /**
         * instance of the Log class
         */
        $this->logger =& class_loader('Log', 'classes');
        $this->logger->setLogger('Library::Database');

      	if(file_exists(CONFIG_PATH . 'database.php')){
          //here don't use require_once because somewhere user can create database instance directly
      	  require CONFIG_PATH . 'database.php';
          if(empty($db) || !is_array($db)){
      			show_error('No database configuration found in database.php');
		  }
		  else{
  				if(! empty($overwriteConfig)){
  				  $db = array_merge($db, $overwriteConfig);
  				}
  				$config['driver']    = isset($db['driver']) ? $db['driver'] : 'mysql';
  				$config['username']  = isset($db['username']) ? $db['username'] : 'root';
  				$config['password']  = isset($db['password']) ? $db['password'] : '';
  				$config['database']  = isset($db['database']) ? $db['database'] : '';
  				$config['hostname']  = isset($db['hostname']) ? $db['hostname'] : 'localhost';
  				$config['charset']   = isset($db['charset']) ? $db['charset'] : 'utf8';
  				$config['collation'] = isset($db['collation']) ? $db['collation'] : 'utf8_general_ci';
  				$config['prefix']    = isset($db['prefix']) ? $db['prefix'] : '';
  				$config['port']      = (strstr($config['hostname'], ':') ? explode(':', $config['hostname'])[1] : '');
  				$this->prefix        = $config['prefix'];
  				$this->databaseName = $config['database'];
  				$dsn = '';
  				if($config['driver'] == 'mysql' || $config['driver'] == '' || $config['driver'] == 'pgsql'){
  					  $dsn = $config['driver'] . ':host=' . $config['hostname'] . ';'
  						. (($config['port']) != '' ? 'port=' . $config['port'] . ';' : '')
  						. 'dbname=' . $config['database'];
  				}
  				else if ($config['driver'] == 'sqlite'){
  				  $dsn = 'sqlite:' . $config['database'];
  				}
  				else if($config['driver'] == 'oracle'){
  				  $dsn = 'oci:dbname=' . $config['host'] . '/' . $config['database'];
  				}
  				try{
  				  $this->pdo = new PDO($dsn, $config['username'], $config['password']);
  				  $this->pdo->exec("SET NAMES '".$config['charset']."' COLLATE '".$config['collation']."'");
  				  $this->pdo->exec("SET CHARACTER SET '".$config['charset']."'");
  				  $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
  				}
  				catch (PDOException $e){
  				  $this->logger->fatal($e->getMessage());
  				  show_error('Cannot connect to Database.');
  				}
  				static::$config = $config;
				$this->temporaryCacheTtl = $this->cacheTtl;
  				$this->logger->info('The database configuration are listed below: ' . stringfy_vars(array_merge($config, array('password' => string_hidden($config['password'])))));
  			}
    	}
    	else{
    		show_error('Unable to find database configuration');
    	}
    }

    public function from($table){
      if(is_array($table))
      {
        $f = '';
        foreach($table as $key){
          $f .= $this->prefix . $key . ', ';
        }

        $this->from = rtrim($f, ', ');
      }
      else{
        $this->from = $this->prefix . $table;
      }

      return $this;
    }

    public function select($fields)
    {
      $select = (is_array($fields) ? implode(", ", $fields) : $fields);
      $this->select = ($this->select == '*' ? $select : $this->select . ", " . $select);

      return $this;
    }

    public function max($field, $name = null)
    {
      $func = "MAX(" . $field . ")" . (!is_null($name) ? " AS " . $name : "");
      $this->select = ($this->select == '*' ? $func : $this->select . ", " . $func);

      return $this;
    }

    public function distinct($field)
    {
      $distinct = " DISTINCT " . $field;
      $this->select = ($this->select == '*' ? $distinct : $this->select . ", " . $distinct);

      return $this;
    }

    public function min($field, $name = null)
    {
      $func = "MIN(" . $field . ")" . (!is_null($name) ? " AS " . $name : "");
      $this->select = ($this->select == '*' ? $func : $this->select . ", " . $func);

      return $this;
    }

    public function sum($field, $name = null)
    {
      $func = "SUM(" . $field . ")" . (!is_null($name) ? " AS " . $name : "");
      $this->select = ($this->select == '*' ? $func : $this->select . ", " . $func);

      return $this;
    }

    public function count($field = '*', $name = null)
    {
      $func = "COUNT(" . $field . ")" . (!is_null($name) ? " AS " . $name : "");
      $this->select = ($this->select == '*' ? $func : $this->select . ", " . $func);

      return $this;
    }

    public function avg($field, $name = null)
    {
      $func = "AVG(" . $field . ")" . (!is_null($name) ? " AS " . $name : "");
      $this->select = ($this->select == '*' ? $func : $this->select . ", " . $func);

      return $this;
    }

    public function join($table, $field1 = null, $op = null, $field2 = null, $type = '')
    {
      $on = $field1;
      $table = $this->prefix . $table;

      if(!is_null($op)){
        $on = (!in_array($op, $this->op) ? $this->prefix . $field1 . ' = ' . $this->prefix . $op : $this->prefix . $field1 . ' ' . $op . ' ' . $this->prefix . $field2);
      }

      if (is_null($this->join)){
        $this->join = ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
      }
      else{
        $this->join = $this->join . ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
      }

      return $this;
    }

    public function innerJoin($table, $field1, $op = null, $field2 = '')
    {
      $this->join($table, $field1, $op, $field2, 'INNER ');

      return $this;
    }

    public function leftJoin($table, $field1, $op = null, $field2 = '')
    {
      $this->join($table, $field1, $op, $field2, 'LEFT ');

      return $this;
    }

    public function rightJoin($table, $field1, $op = null, $field2 = '')
    {
      $this->join($table, $field1, $op, $field2, 'RIGHT ');

      return $this;
    }

    public function fullOuterJoin($table, $field1, $op = null, $field2 = '')
    {
      $this->join($table, $field1, $op, $field2, 'FULL OUTER ');

      return $this;
    }

    public function leftOuterJoin($table, $field1, $op = null, $field2 = '')
    {
      $this->join($table, $field1, $op, $field2, 'LEFT OUTER ');

      return $this;
    }

    public function rightOuterJoin($table, $field1, $op = null, $field2 = '')
    {
      $this->join($table, $field1, $op, $field2, 'RIGHT OUTER ');
      return $this;
    }

    public function whereIsNull($field, $and_or = 'AND'){
      if(is_array($field)){
        foreach($field as $v){
          $this->whereIsNull($v, $and_or);
        }
      }
      else{
        if (!$this->where){
          $this->where = $field.' IS NULL ';
        }
        else{
            $this->where = $this->where . ' '.$and_or.' ' . $field.' IS NULL ';
          }
      }
       return $this;
    }

    public function whereIsNotNull($field, $and_or = 'AND'){
      if(is_array($field)){
        foreach($field as $v){
          $this->whereIsNull($v, $and_or);
        }
      }
      else{
        if (!$this->where){
          $this->where = $field.' IS NOT NULL ';
        }
        else{
            $this->where = $this->where . ' '.$and_or.' ' . $field.' IS NOT NULL ';
          }
      }
       return $this;
    }
    

    public function where($where, $op = null, $val = null, $type = '', $and_or = 'AND', $escape = true)
    {
      if (is_array($where))
      {
        $_where = array();

        foreach ($where as $column => $data){
          $_where[] = $type . $column . '=' . ($escape ? $this->escape($data) : $data);
        }
        $where = implode(' '.$and_or.' ', $_where);
      }
      else
      {
        if(is_array($op))
        {
          $x = explode('?', $where);
          $w = '';

          foreach($x as $k => $v){
            if(!empty($v)){
                $w .= $type . $v . (isset($op[$k]) ? ($escape ? $this->escape($op[$k]) : $op[$k]) : '');
            }
          }
          $where = $w;
        }
        elseif (!in_array((string)$op, $this->op)){
          $where = $type . $where . ' = ' . ($escape ? $this->escape($op) : $op);
        }
        else{
          $where = $type . $where . $op . ($escape ? $this->escape($val) : $val);
        }
      }

      if (is_null($this->where)){
        $this->where = $where;
      }
      else{
        if(substr($this->where, -1) == '('){
          $this->where = $this->where . ' ' . $where;
        }
        else{
          $this->where = $this->where . ' '.$and_or.' ' . $where;
        }
      }

      return $this;
    }

    

    public function orWhere($where, $op = null, $val = null, $escape = true)
    {
      $this->where($where, $op, $val, '', 'OR', $escape);
      return $this;
    }

    public function notWhere($where, $op = null, $val = null, $escape = true)
    {
      $this->where($where, $op, $val, 'NOT ', 'AND', $escape);
      return $this;
    }

    public function orNotWhere($where, $op = null, $val = null, $escape = true)
    {
      $this->where($where, $op, $val, 'NOT ', 'OR', $escape);
      return $this;
    }

    public function groupStart($type = '', $and_or = ' AND')
    {
      if (is_null($this->where)){
        $this->where = $type . ' (';
      }
      else{
          if(substr($this->where, -1) == '('){
            $this->where .= $type . ' (';
          }
          else{
            $this->where .= $and_or . ' ' . $type . ' (';
          }
      }
      return $this;
    }

    public function notGroupStart()
    {
      return $this->groupStart('NOT');
    }

    public function orGroupStart()
    {
      return $this->groupStart('', ' OR');
    }

    public function orNotGroupStart()
    {
      return $this->groupStart('NOT', ' OR');
    }

    public function groupEnd()
    {
      $this->where .= ')';
      return $this;
    }

    


    public function in($field, array $keys, $type = '', $and_or = 'AND', $escape = true)
    {
      if (is_array($keys))
      {
        $_keys = array();

        foreach ($keys as $k => $v){
          $_keys[] = (is_numeric($v) ? $v : ($escape ? $this->escape($v) : $v));
        }

        $keys = implode(', ', $_keys);

        if (is_null($this->where)){
          $this->where = $field . ' ' . $type . 'IN (' . $keys . ')';
        }
        else{
          if(substr($this->where, -1) == '('){
            $this->where = $this->where . ' ' . $field . ' '.$type.'IN (' . $keys . ')';
          }
          else{
            $this->where = $this->where . ' ' . $and_or . ' ' . $field . ' '.$type.'IN (' . $keys . ')';
          }
        }
      }

      return $this;
    }

    public function notIn($field, array $keys, $escape = true)
    {
      $this->in($field, $keys, 'NOT ', 'AND', $escape);
      return $this;
    }

    public function orIn($field, array $keys, $escape = true)
    {
      $this->in($field, $keys, '', 'OR', $escape);
      return $this;
    }

    public function orNotIn($field, array $keys, $escape = true)
    {
      $this->in($field, $keys, 'NOT ', 'OR', $escape);
      return $this;
    }

    public function between($field, $value1, $value2, $type = '', $and_or = 'AND', $escape = true)
    {
      if (is_null($this->where)){
        $this->where = $field . ' ' . $type . 'BETWEEN ' . ($escape ? $this->escape($value1) : $value1) . ' AND ' . ($escape ? $this->escape($value2) : $value2);
      }
      else{
        if(substr($this->where, -1) == '('){
          $this->where = $this->where . ' ' . $field . ' ' . $type . 'BETWEEN ' . ($escape ? $this->escape($value1) : $value1) . ' AND ' . ($escape ? $this->escape($value2) : $value2);
        }
        else{
          $this->where = $this->where . ' ' . $and_or . ' ' . $field . ' ' . $type . 'BETWEEN ' . ($escape ? $this->escape($value1) : $value1) . ' AND ' . ($escape ? $this->escape($value2) : $value2);
        }
      }
      return $this;
    }

    public function notBetween($field, $value1, $value2, $escape = true)
    {
      $this->between($field, $value1, $value2, 'NOT ', 'AND', $escape);
      return $this;
    }

    public function orBetween($field, $value1, $value2, $escape = true)
    {
      $this->between($field, $value1, $value2, '', 'OR', $escape);
      return $this;
    }

    public function orNotBetween($field, $value1, $value2, $escape = true)
    {
      $this->between($field, $value1, $value2, 'NOT ', 'OR', $escape);
      return $this;
    }

    public function like($field, $data, $type = '', $and_or = 'AND', $escape = true)
    {
      $like = $escape ? $this->escape($data) : $data;
      if (is_null($this->where)){
        $this->where = $field . ' ' . $type . 'LIKE ' . $like;
      }
      else{
        if(substr($this->where, -1) == '('){
          $this->where = $this->where . ' ' . $field . ' ' . $type . 'LIKE ' . $like;
        }
        else{
          $this->where = $this->where . ' '.$and_or.' ' . $field . ' ' . $type . 'LIKE ' . $like;
        }
      }
      return $this;
    }

    public function orLike($field, $data, $escape = true)
    {
      $this->like($field, $data, '', 'OR', $escape);
      return $this;
    }

    public function notLike($field, $data, $escape = true)
    {
      $this->like($field, $data, 'NOT ', 'AND', $escape);
      return $this;
    }

    public function orNotLike($field, $data, $escape = true)
    {
      $this->like($field, $data, 'NOT ', 'OR', $escape);
      return $this;
    }

    public function limit($limit, $limitEnd = null)
    {
      if (!is_null($limitEnd)){
        $this->limit = $limit . ', ' . $limitEnd;
      }
      else{
        $this->limit = $limit;
      }
      return $this;
    }

    public function orderBy($orderBy, $order_dir = ' ASC')
    {
      if (!is_null($order_dir)){
        $this->orderBy = ! $this->orderBy ? ($orderBy . ' ' . strtoupper($order_dir)) : $this->orderBy . ', ' . $orderBy . ' ' . strtoupper($order_dir);
      }
      else{
        if(stristr($orderBy, ' ') || $orderBy == 'rand()'){
          $this->orderBy = ! $this->orderBy ? $orderBy : $this->orderBy . ', ' . $orderBy;
        }
        else{
          $this->orderBy = ! $this->orderBy ? ($orderBy . ' ASC') : $this->orderBy . ', ' . ($orderBy . ' ASC');
        }
      }
      return $this;
    }

    public function groupBy($groupBy)
    {
      if(is_array($groupBy)){
        $this->groupBy = implode(', ', $groupBy);
      }
      else{
        $this->groupBy = $groupBy;
      }
      return $this;
    }

    public function having($field, $op = null, $val = null, $escape = true)
    {
      if(is_array($op)){
        $x = explode('?', $field);
        $w = '';
        foreach($x as $k => $v)
          if(!empty($v)){
            $w .= $v . (isset($op[$k]) ? ($escape ? $this->escape($op[$k]) : $op[$k]) : '');
          }
        $this->having = $w;
      }
      elseif (!in_array($op, $this->op)){
        $this->having = $field . ' > ' . ($escape ? $this->escape($op) : $op);
      }
      else{
        $this->having = $field . ' ' . $op . ' ' . ($escape ? $this->escape($val) : $val);
      }
      return $this;
    }

    public function numRows(){
      return $this->numRows;
    }

    public function insertId()
    {
      return $this->insertId;
    }

    public function error(){
		if($this->error){
			show_error('Query: "'.$this->query.'" Error: '.$this->error, 'Database Error');
		}
    }

    public function get($type = false)
    {
      $this->limit = 1;
      $query = $this->getAll(true);

      if($type === true){
        return $query;
      }
      else{
        return $this->query( $query, false, (($type == 'array') ? true : false) );
      }
    }

    public function getAll($type = false)
    {
      $query = 'SELECT ' . $this->select . ' FROM ' . $this->from;
      if (!is_null($this->join)){
        $query .= $this->join;
      }
	  
      if (!is_null($this->where)){
        $query .= ' WHERE ' . $this->where;
      }

      if (!is_null($this->groupBy)){
        $query .= ' GROUP BY ' . $this->groupBy;
      }

      if (!is_null($this->having)){
        $query .= ' HAVING ' . $this->having;
      }

      if (!is_null($this->orderBy)){
          $query .= ' ORDER BY ' . $this->orderBy;
      }

      if(!is_null($this->limit)){
      	$query .= ' LIMIT ' . $this->limit;
      }
	  
	  if($type === true){
    	      return $query;
      }
      else{
    		return $this->query( $query, true, (($type == 'array') ? true : false) );
      }
    }

    public function insert($data = array(), $escape = true)
    {
      $column = array();
      $val = array();
       if(! $data && $this->getData()){
        $columns = array_keys($this->getData());
        $column = implode(',', $columns);
        $val = implode(', ', $this->getData());
      }
      else{
        $columns = array_keys($data);
        $column = implode(',', $columns);
        $val = implode(', ', ($escape ? array_map(array($this, 'escape'), $data) : $data));
      }

      $query = 'INSERT INTO ' . $this->from . ' (' . $column . ') VALUES (' . $val . ')';
      $query = $this->query($query);

      if ($query)
      {
        $this->insertId = $this->pdo->lastInsertId();
        return $this->insertId();
      }
      else{
		  return false;
      }
    }

    public function update($data = array(), $escape = true)
    {
      $query = 'UPDATE ' . $this->from . ' SET ';
      $values = array();
      if(! $data && $this->getData()){
        foreach ($this->getData() as $column => $val){
          $values[] = $column . ' = ' . $val;
        }
      }
      else{
        foreach ($data as $column => $val){
          $values[] = $column . '=' . ($escape ? $this->escape($val) : $val);
        }
      }

      $query .= (is_array($data) ? implode(',', $values) : $data);
      if (!is_null($this->where)){
        $query .= ' WHERE ' . $this->where;
      }

      if (!is_null($this->orderBy)){
        $query .= ' ORDER BY ' . $this->orderBy;
      }

      if (!is_null($this->limit)){
        $query .= ' LIMIT ' . $this->limit;
      }
      return $this->query($query);
    }

    public function delete()
    {
    	$query = 'DELETE FROM ' . $this->from;

    	if (!is_null($this->where)){
    	  $query .= ' WHERE ' . $this->where;
      }

    	if (!is_null($this->orderBy)){
    	  $query .= ' ORDER BY ' . $this->orderBy;
      }

    	if (!is_null($this->limit)){
    	  $query .= ' LIMIT ' . $this->limit;
      }

    	if($query == 'DELETE FROM ' . $this->from){
    	  $query = 'TRUNCATE TABLE ' . $this->from;
      }
      return $this->query($query);
    }

    public function query($query, $all = true, $array = false)
    {
      $this->reset();
      if(is_array($all))
      {
        $x = explode('?', $query);
        $q = '';
        foreach($x as $k => $v){
          if(!empty($v)){
            $q .= $v . (isset($all[$k]) ? $this->escape($all[$k]) : '');
          }
        }
        $query = $q;
      }

      $this->query = preg_replace('/\s\s+|\t\t+/', ' ', trim($query));
      $sqlSELECTQuery = stristr($this->query, 'SELECT');

      $this->logger->info('Execute SQL query ['.$this->query.'], return type: ' . ($array?'ARRAY':'OBJECT') .', return as list: ' . ($all ? 'YES':'NO'));
      //cache expire time
	  $cacheExpire = $this->temporaryCacheTtl;
	  
	  //return to the initial cache time
	  $this->temporaryCacheTtl = $this->cacheTtl;
	  
	  //config for cache
      $cacheEnable = get_config('cache_enable');
	  
	  //the database cache content
      $cacheContent = null;
	  
	  //this database query cache key
      $cacheKey = null;
	  
	  //the cache manager instance
      $cacheInstance = null;
	  
	  //the instance of the super controller
      $obj = & get_instance();
	  
	  //if can use cache feature for this query
	  $dbCacheStatus = $cacheEnable && $cacheExpire > 0;
	  
      if ($dbCacheStatus && $sqlSELECTQuery){
        $this->logger->info('The cache is enabled for this query, try to get result from cache'); 
        $cacheKey = md5($query . $all . $array);
        $cacheInstance = $obj->{strtolower(get_config('cache_handler'))};
        $cacheContent = $cacheInstance->get($cacheKey);        
      }
      else{
		  $this->logger->info('The cache is not enabled for this query or is not the SELECT query, get the result directly from real database');
      }
      
      if (! $cacheContent && $sqlSELECTQuery)
      {
		//for database query execution time
        $benchmarkMarkerKey = md5($query . $all . $array);
        $obj->benchmark->mark('DATABASE_QUERY_START(' . $benchmarkMarkerKey . ')');
        //Now execute the query
		$sqlQuery = $this->pdo->query($this->query);
        
		//get response time for this query
        $responseTime = $obj->benchmark->elapsedTime('DATABASE_QUERY_START(' . $benchmarkMarkerKey . ')', 'DATABASE_QUERY_END(' . $benchmarkMarkerKey . ')');
		//TODO use the configuration value for the high response time currently is 1 second
        if($responseTime >= 1 ){
            $this->logger->warning('High response time while processing database query [' .$query. ']. The response time is [' .$responseTime. '] sec.');
        }
        if ($sqlQuery)
        {
          $this->numRows = $sqlQuery->rowCount();
          if (($this->numRows > 0))
          {
			//if need return all result like list of record
            if ($all)
            {
				$this->result = ($array == false) ? $sqlQuery->fetchAll(PDO::FETCH_OBJ) : $sqlQuery->fetchAll(PDO::FETCH_ASSOC);
		    }
            else
            {
				$this->result = ($array == false) ? $sqlQuery->fetch(PDO::FETCH_OBJ) : $sqlQuery->fetch(PDO::FETCH_ASSOC);
            }
          }
          if ($dbCacheStatus && $sqlSELECTQuery){
            $this->logger->info('Save the result for query [' .$this->query. '] into cache for future use');
            $cacheInstance->set($cacheKey, $this->result, $cacheExpire);
          }
        }
        else
        {
          $error = $this->pdo->errorInfo();
          $this->error = $error[2];
          $this->logger->fatal('The database query execution got error: ' . stringfy_vars($error));
          $this->error();
        }
      }
      else if ((! $cacheContent && !$sqlSELECTQuery) || ($cacheContent && !$sqlSELECTQuery))
      {
		$queryStr = $this->pdo->query($this->query);
		if($queryStr){
			$this->result = $queryStr->rowCount() >= 0; //to test the result for the query like UPDATE, INSERT, DELETE
			$this->numRows = $queryStr->rowCount();
		}
        if (! $this->result)
        {
          $error = $this->pdo->errorInfo();
          $this->error = $error[2];
          $this->logger->fatal('The database query execution got error: ' . stringfy_vars($error));
          $this->error();
        }
      }
      else
      {
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

    public function setCache($ttl = 0){
      if($ttl > 0){
        $this->cacheTtl = $ttl;
		$this->temporaryCacheTtl = $ttl;
      }
    }
	
	/**
	* Enabled cache temporary
	*/
	public function cached($ttl = 0){
      if($ttl > 0){
        $this->temporaryCacheTtl = $ttl;
      }
	  return $this;
    }

    public function escape($data)
    {
      if(is_null($data)){
        return null;
      }
      return $this->pdo->quote(trim($data));
    }

    public function queryCount()
    {
      return $this->queryCount;
    }

    public function getQuery()
    {
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
    public static function getDatabaseConfiguration(){
      return static::$config;
    }

    /**
     * Return the PDO instance
     * @return PDO
     */
    public function getPdo(){
      return $this->pdo;
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
     */
    public function setData($key, $value, $escape = true){
      $this->data[$key] = $escape ? $this->escape($value) : $value;
      return $this;
    }

  private function reset()
  {
      $this->select  = '*';
      $this->from    = null;
      $this->where  = null;
      $this->limit  = null;
      $this->orderBy  = null;
      $this->groupBy  = null;
      $this->having  = null;
      $this->join    = null;
      $this->numRows  = 0;
      $this->insertId  = null;
      $this->query  = null;
      $this->error  = null;
      $this->result  = array();
      $this->data  = array();
      return;
  }

  function __destruct()
  {
    $this->pdo = null;
  }
}

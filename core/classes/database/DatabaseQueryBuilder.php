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
    class DatabaseQueryBuilder {
        /**
         * The SQL SELECT statment
         * @var string
         */
        private $select = '*';
  	
        /**
         * The SQL FROM statment
         * @var string
         */
        private $from = null;
  	
        /**
         * The SQL WHERE statment
         * @var string
         */
        private $where = null;
  	
        /**
         * The SQL LIMIT statment
         * @var string
         */
        private $limit = null;
  	
        /**
         * The SQL JOIN statment
         * @var string
         */
        private $join = null;
  	
        /**
         * The SQL ORDER BY statment
         * @var string
         */
        private $orderBy = null;
  	
        /**
         * The SQL GROUP BY statment
         * @var string
         */
        private $groupBy = null;
  	
        /**
         * The SQL HAVING statment
         * @var string
         */
        private $having = null;
  	
        /**
         * The full SQL query statment after build for each command
         * @var string
         */
        private $query = null;
  	
        /**
         * The list of SQL valid operators
         * @var array
         */
    private $operatorList = array('=', '!=', '<', '>', '<=', '>=', '<>');
  	
	
    /**
     * The prefix used in each database table
     * @var string
     */
    private $prefix = null;
    

    /**
     * The PDO instance
     * @var object
     */
    private $pdo = null;
	
        /**
         * The database driver name used
         * @var string
         */
        private $driver = null;
  	
	
    /**
     * Construct new DatabaseQueryBuilder
     * @param object $pdo the PDO object
     */
    public function __construct(PDO $pdo = null) {
        if (is_object($pdo)) {
            $this->setPdo($pdo);
        }
    }

    /**
     * Set the SQL FROM statment
     * @param  string|array $table the table name or array of table list
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function from($table) {
        if (is_array($table)) {
        $froms = '';
        foreach ($table as $key) {
            $froms .= $this->getPrefix() . $key . ', ';
        }
        $this->from = rtrim($froms, ', ');
        } else {
        $this->from = $this->getPrefix() . $table;
        }
        return $this;
    }

    /**
     * Set the SQL SELECT statment
     * @param  string|array $fields the field name or array of field list
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function select($fields) {
        $select = (is_array($fields) ? implode(', ', $fields) : $fields);
        $this->select = (($this->select == '*' || empty($this->select)) ? $select : $this->select . ', ' . $select);
        return $this;
    }

    /**
     * Set the SQL SELECT DISTINCT statment
     * @param  string $field the field name to distinct
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function distinct($field) {
        $distinct = ' DISTINCT ' . $field;
        $this->select = (($this->select == '*' || empty($this->select)) ? $distinct : $this->select . ', ' . $distinct);
        return $this;
    }

        /**
         * Set the SQL function COUNT in SELECT statment
         * @param  string $field the field name
         * @param  string $name  if is not null represent the alias used for this field in the result
         * @return object        the current DatabaseQueryBuilder instance
         */
    public function count($field = '*', $name = null) {
        return $this->select_min_max_sum_count_avg('COUNT', $field, $name);
    }
    
    /**
     * Set the SQL function MIN in SELECT statment
     * @param  string $field the field name
     * @param  string $name  if is not null represent the alias used for this field in the result
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function min($field, $name = null) {
        return $this->select_min_max_sum_count_avg('MIN', $field, $name);
    }

    /**
     * Set the SQL function MAX in SELECT statment
     * @param  string $field the field name
     * @param  string $name  if is not null represent the alias used for this field in the result
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function max($field, $name = null) {
        return $this->select_min_max_sum_count_avg('MAX', $field, $name);
    }

    /**
     * Set the SQL function SUM in SELECT statment
     * @param  string $field the field name
     * @param  string $name  if is not null represent the alias used for this field in the result
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function sum($field, $name = null) {
        return $this->select_min_max_sum_count_avg('SUM', $field, $name);
    }

    /**
     * Set the SQL function AVG in SELECT statment
     * @param  string $field the field name
     * @param  string $name  if is not null represent the alias used for this field in the result
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function avg($field, $name = null) {
        return $this->select_min_max_sum_count_avg('AVG', $field, $name);
    }


    /**
     * Set the SQL JOIN statment
     * @param  string $table  the join table name
     * @param  string $field1 the first field for join conditions	
     * @param  string $op     the join condition operator. If is null the default will be "="
     * @param  string $field2 the second field for join conditions
     * @param  string $type   the type of join (INNER, LEFT, RIGHT)
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function join($table, $field1 = null, $op = null, $field2 = null, $type = ''){
        $on = $field1;
        $table = $this->getPrefix() . $table;
        if (! is_null($op)){
        $on = (! in_array($op, $this->operatorList) 
                                                    ? ($this->getPrefix() . $field1 . ' = ' . $this->getPrefix() . $op) 
                                                    : ($this->getPrefix() . $field1 . ' ' . $op . ' ' . $this->getPrefix() . $field2));
        }
        if (empty($this->join)){
        $this->join = ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
        } else{
        $this->join = $this->join . ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
        }
        return $this;
    }

    /**
     * Set the SQL INNER JOIN statment
     * @see  DatabaseQueryBuilder::join()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function innerJoin($table, $field1, $op = null, $field2 = '') {
        return $this->join($table, $field1, $op, $field2, 'INNER ');
    }

    /**
     * Set the SQL LEFT JOIN statment
     * @see  DatabaseQueryBuilder::join()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function leftJoin($table, $field1, $op = null, $field2 = '') {
        return $this->join($table, $field1, $op, $field2, 'LEFT ');
    }

    /**
     * Set the SQL RIGHT JOIN statment
     * @see  DatabaseQueryBuilder::join()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function rightJoin($table, $field1, $op = null, $field2 = '') {
        return $this->join($table, $field1, $op, $field2, 'RIGHT ');
    }

    /**
     * Set the SQL FULL OUTER JOIN statment
     * @see  DatabaseQueryBuilder::join()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function fullOuterJoin($table, $field1, $op = null, $field2 = '') {
        return $this->join($table, $field1, $op, $field2, 'FULL OUTER ');
    }

    /**
     * Set the SQL LEFT OUTER JOIN statment
     * @see  DatabaseQueryBuilder::join()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function leftOuterJoin($table, $field1, $op = null, $field2 = '') {
        return $this->join($table, $field1, $op, $field2, 'LEFT OUTER ');
    }

    /**
     * Set the SQL RIGHT OUTER JOIN statment
     * @see  DatabaseQueryBuilder::join()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function rightOuterJoin($table, $field1, $op = null, $field2 = '') {
        return $this->join($table, $field1, $op, $field2, 'RIGHT OUTER ');
    }

    /**
     * Set the SQL WHERE CLAUSE for IS NULL
     * @param  string|array $field  the field name or array of field list
     * @param  string $andOr the separator type used 'AND', 'OR', etc.
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function whereIsNull($field, $andOr = 'AND') {
        if (is_array($field)) {
        foreach ($field as $f) {
            $this->whereIsNull($f, $andOr);
        }
        } else {
            $this->setWhereStr($field . ' IS NULL ', $andOr);
        }
        return $this;
    }

    /**
     * Set the SQL WHERE CLAUSE for IS NOT NULL
     * @param  string|array $field  the field name or array of field list
     * @param  string $andOr the separator type used 'AND', 'OR', etc.
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function whereIsNotNull($field, $andOr = 'AND') {
        if (is_array($field)) {
        foreach ($field as $f) {
            $this->whereIsNotNull($f, $andOr);
        }
        } else {
            $this->setWhereStr($field . ' IS NOT NULL ', $andOr);
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
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function where($where, $op = null, $val = null, $type = '', $andOr = 'AND', $escape = true){
        $whereStr = '';
        if (is_array($where)){
        $whereStr = $this->getWhereStrIfIsArray($where, $type, $andOr, $escape);
        } else{
        if (is_array($op)){
            $whereStr = $this->getWhereStrIfOperatorIsArray($where, $op, $type, $escape);
        } else {
            $whereStr = $this->getWhereStrForOperator($where, $op, $val, $type, $escape);
        }
        }
        $this->setWhereStr($whereStr, $andOr);
        return $this;
    }

    /**
     * Set the SQL WHERE CLAUSE statment using OR
     * @see  DatabaseQueryBuilder::where()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function orWhere($where, $op = null, $val = null, $escape = true) {
        return $this->where($where, $op, $val, '', 'OR', $escape);
    }


    /**
     * Set the SQL WHERE CLAUSE statment using AND and NOT
     * @see  DatabaseQueryBuilder::where()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function notWhere($where, $op = null, $val = null, $escape = true) {
        return $this->where($where, $op, $val, 'NOT ', 'AND', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment using OR and NOT
     * @see  DatabaseQueryBuilder::where()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function orNotWhere($where, $op = null, $val = null, $escape = true) {
        return $this->where($where, $op, $val, 'NOT ', 'OR', $escape);
    }

    /**
     * Set the opened parenthesis for the complex SQL query
     * @param  string $type   the type of this grouped (NOT, etc.)
     * @param  string $andOr the multiple conditions separator (AND, OR, etc.)
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function groupStart($type = '', $andOr = ' AND') {
        if (empty($this->where)) {
        $this->where = $type . ' (';
        } else {
            if (substr(trim($this->where), -1) == '(') {
            $this->where .= $type . ' (';
            } else {
                $this->where .= $andOr . ' ' . $type . ' (';
            }
        }
        return $this;
    }

    /**
     * Set the opened parenthesis for the complex SQL query using NOT type
     * @see  DatabaseQueryBuilder::groupStart()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function notGroupStart() {
        return $this->groupStart('NOT');
    }

    /**
     * Set the opened parenthesis for the complex SQL query using OR for separator
     * @see  DatabaseQueryBuilder::groupStart()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function orGroupStart() {
        return $this->groupStart('', ' OR');
    }

        /**
         * Set the opened parenthesis for the complex SQL query using OR for separator and NOT for type
         * @see  DatabaseQueryBuilder::groupStart()
         * @return object        the current DatabaseQueryBuilder instance
         */
    public function orNotGroupStart() {
        return $this->groupStart('NOT', ' OR');
    }

    /**
     * Close the parenthesis for the grouped SQL
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function groupEnd() {
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
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function in($field, array $keys, $type = '', $andOr = 'AND', $escape = true) {
        $_keys = array();
        foreach ($keys as $k => $v) {
        if (is_null($v)) {
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
     * @see  DatabaseQueryBuilder::in()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function notIn($field, array $keys, $escape = true) {
        return $this->in($field, $keys, 'NOT ', 'AND', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for IN with OR separator
     * @see  DatabaseQueryBuilder::in()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function orIn($field, array $keys, $escape = true) {
        return $this->in($field, $keys, '', 'OR', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for NOT IN with OR separator
     * @see  DatabaseQueryBuilder::in()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function orNotIn($field, array $keys, $escape = true) {
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
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function between($field, $value1, $value2, $type = '', $andOr = 'AND', $escape = true) {
        if (is_null($value1)) {
        $value1 = '';
        }
        if (is_null($value2)) {
        $value2 = '';
        }
        $whereStr = $field . ' ' . $type . ' BETWEEN ' . $this->escape($value1, $escape) . ' AND ' . $this->escape($value2, $escape);
        $this->setWhereStr($whereStr, $andOr);
        return $this;
    }

    /**
     * Set the SQL WHERE CLAUSE statment for BETWEEN with NOT type and AND separator
     * @see  DatabaseQueryBuilder::between()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function notBetween($field, $value1, $value2, $escape = true) {
        return $this->between($field, $value1, $value2, 'NOT ', 'AND', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for BETWEEN with OR separator
     * @see  DatabaseQueryBuilder::between()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function orBetween($field, $value1, $value2, $escape = true) {
        return $this->between($field, $value1, $value2, '', 'OR', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for BETWEEN with NOT type and OR separator
     * @see  DatabaseQueryBuilder::between()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function orNotBetween($field, $value1, $value2, $escape = true) {
        return $this->between($field, $value1, $value2, 'NOT ', 'OR', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for LIKE
     * @param  string  $field  the field name used in LIKE statment
     * @param  string  $data   the LIKE value for this field including the '%', and '_' part
     * @param  string  $type   the condition separator type (NOT)
     * @param  string  $andOr the multiple conditions separator (OR, AND)
     * @param  boolean $escape whether to escape or not the values
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function like($field, $data, $type = '', $andOr = 'AND', $escape = true) {
        if (empty($data)) {
        $data = '';
        }
        $this->setWhereStr($field . ' ' . $type . ' LIKE ' . ($this->escape($data, $escape)), $andOr);
        return $this;
    }

    /**
     * Set the SQL WHERE CLAUSE statment for LIKE with OR separator
     * @see  DatabaseQueryBuilder::like()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function orLike($field, $data, $escape = true) {
        return $this->like($field, $data, '', 'OR', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for LIKE with NOT type and AND separator
     * @see  DatabaseQueryBuilder::like()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function notLike($field, $data, $escape = true) {
        return $this->like($field, $data, 'NOT ', 'AND', $escape);
    }

    /**
     * Set the SQL WHERE CLAUSE statment for LIKE with NOT type and OR separator
     * @see  DatabaseQueryBuilder::like()
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function orNotLike($field, $data, $escape = true) {
        return $this->like($field, $data, 'NOT ', 'OR', $escape);
    }

    /**
     * Set the SQL LIMIT statment
     * @param  int $limit    the limit offset. If $limitEnd is null this will be the limit count
     * like LIMIT n;
     * @param  int $limitEnd the limit count
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function limit($limit, $limitEnd = null){
        if (empty($limit)){
        $limit = 0;
        }
        if (! is_null($limitEnd)){
        $this->limit = $limit . ', ' . $limitEnd;
        } else{
        $this->limit = $limit;
        }
        return $this;
    }

    /**
     * Set the SQL ORDER BY CLAUSE statment
     * @param  string $orderBy   the field name used for order
     * @param  string $orderDir the order direction (ASC or DESC)
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function orderBy($orderBy, $orderDir = ' ASC'){
        if (stristr($orderBy, ' ') || $orderBy == 'rand()'){
        $this->orderBy = empty($this->orderBy) ? $orderBy : $this->orderBy . ', ' . $orderBy;
        } else{
        $this->orderBy = empty($this->orderBy) 
                        ? ($orderBy . ' ' . strtoupper($orderDir)) 
                        : $this->orderBy . ', ' . $orderBy . ' ' . strtoupper($orderDir);
        }
        return $this;
    }

    /**
     * Set the SQL GROUP BY CLAUSE statment
     * @param  string|array $field the field name used or array of field list
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function groupBy($field){
        if (is_array($field)){
        $this->groupBy = implode(', ', $field);
        } else{
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
     * @return object        the current DatabaseQueryBuilder instance
     */
    public function having($field, $op = null, $val = null, $escape = true){
        if (is_array($op)){
        $this->having = $this->getHavingStrIfOperatorIsArray($field, $op, $escape);
        } else if (! in_array($op, $this->operatorList)){
        if (is_null($op)){
            $op = '';
        }
        $this->having = $field . ' > ' . ($this->escape($op, $escape));
        } else{
        if (is_null($val)){
            $val = '';
        }
        $this->having = $field . ' ' . $op . ' ' . ($this->escape($val, $escape));
        }
        return $this;
    }

    /**
     * Insert new record in the database
     * @param  array   $data   the record data
     * @param  boolean $escape  whether to escape or not the values
     * @return object  the current DatabaseQueryBuilder instance        
     */
    public function insert($data = array(), $escape = true) {
        $columns = array_keys($data);
        $column = implode(', ', $columns);
        $val = implode(', ', ($escape ? array_map(array($this, 'escape'), $data) : $data));

        $this->query = 'INSERT INTO ' . $this->from . ' (' . $column . ') VALUES (' . $val . ')';
        return $this;
    }

    /**
     * Update record in the database
     * @param  array   $data   the record data if is empty will use the $this->data array.
     * @param  boolean $escape  whether to escape or not the values
     * @return object  the current DatabaseQueryBuilder instance 
     */
    public function update($data = array(), $escape = true) {
        $query = 'UPDATE ' . $this->from . ' SET ';
        $values = array();
        foreach ($data as $column => $val) {
        $values[] = $column . ' = ' . ($this->escape($val, $escape));
        }
        $query .= implode(', ', $values);
        if (!empty($this->where)) {
        $query .= ' WHERE ' . $this->where;
        }

        if (!empty($this->orderBy)) {
        $query .= ' ORDER BY ' . $this->orderBy;
        }

        if (!empty($this->limit)) {
        $query .= ' LIMIT ' . $this->limit;
        }
        $this->query = $query;
        return $this;
    }

    /**
     * Delete the record in database
     * @return object  the current DatabaseQueryBuilder instance 
     */
    public function delete() {
        $query = 'DELETE FROM ' . $this->from;
        $isTruncate = $query;
        if (!empty($this->where)) {
            $query .= ' WHERE ' . $this->where;
        }

        if (!empty($this->orderBy)) {
            $query .= ' ORDER BY ' . $this->orderBy;
        }

        if (!empty($this->limit)) {
            $query .= ' LIMIT ' . $this->limit;
        }

            if ($isTruncate == $query && $this->driver != 'sqlite') {  
            $query = 'TRUNCATE TABLE ' . $this->from;
            }
        $this->query = $query;
        return $this;
    }

    /**
     * Escape the data before execute query useful for security.
     * @param  mixed $data the data to be escaped
     * @param boolean $escaped whether we can do escape of not 
     * @return mixed       the data after escaped or the same data if not
     */
    public function escape($data, $escaped = true) {
        $data = trim($data);
        if ($escaped) {
        return $this->pdo->quote($data);
        }
        return $data;  
    }


    /**
     * Return the current SQL query string
     * @return string
     */
    public function getQuery() {
        //INSERT, UPDATE, DELETE already set it, if is the SELECT we need set it now
        if (empty($this->query)) {
            $query = 'SELECT ' . $this->select . ' FROM ' . $this->from;
            if (!empty($this->join)) {
            $query .= $this->join;
            }
  		  
            if (!empty($this->where)) {
            $query .= ' WHERE ' . $this->where;
            }

            if (!empty($this->groupBy)) {
            $query .= ' GROUP BY ' . $this->groupBy;
            }

            if (!empty($this->having)) {
            $query .= ' HAVING ' . $this->having;
            }

            if (!empty($this->orderBy)) {
                $query .= ' ORDER BY ' . $this->orderBy;
            }

            if (!empty($this->limit)) {
            $query .= ' LIMIT ' . $this->limit;
            }
            $this->query = $query;
        }
        return $this->query;
    }

	
        /**
         * Return the PDO instance
         * @return object
         */
    public function getPdo() {
        return $this->pdo;
    }

    /**
     * Set the PDO instance
     * @param PDO $pdo the pdo object
     * @return object DatabaseQueryBuilder
     */
    public function setPdo(PDO $pdo = null) {
        $this->pdo = $pdo;
        return $this;
    }
	
    /**
     * Return the database table prefix
     * @return string
     */
    public function getPrefix() {
        return $this->prefix;
    }

    /**
     * Set the database table prefix
     * @param string $prefix the new prefix
     * @return object DatabaseQueryBuilder
     */
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
        return $this;
    }
	
        /**
         * Return the database driver
         * @return string
         */
    public function getDriver() {
        return $this->driver;
    }

    /**
     * Set the database driver
     * @param string $driver the new driver
     * @return object DatabaseQueryBuilder
     */
    public function setDriver($driver) {
        $this->driver = $driver;
        return $this;
    }
	
        /**
         * Reset the DatabaseQueryBuilder class attributs to the initial values before each query.
         * @return object  the current DatabaseQueryBuilder instance 
         */
    public function reset() {
        $this->select   = '*';
        $this->from     = null;
        $this->where    = null;
        $this->limit    = null;
        $this->orderBy  = null;
        $this->groupBy  = null;
        $this->having   = null;
        $this->join     = null;
        $this->query    = null;
        return $this;
    }

        /**
         * Get the SQL HAVING clause when operator argument is an array
         * @see DatabaseQueryBuilder::having
         *
         * @return string
         */
    protected function getHavingStrIfOperatorIsArray($field, $op = null, $escape = true) {
        $x = explode('?', $field);
        $w = '';
        foreach ($x as $k => $v) {
            if (!empty($v)) {
            if (!isset($op[$k])) {
                $op[$k] = '';
            }
                $w .= $v . (isset($op[$k]) ? $this->escape($op[$k], $escape) : '');
            }
            }
        return $w;
    }


    /**
     * Get the SQL WHERE clause using array column => value
     * @see DatabaseQueryBuilder::where
     *
     * @return string
     */
    protected function getWhereStrIfIsArray(array $where, $type = '', $andOr = 'AND', $escape = true) {
        $_where = array();
        foreach ($where as $column => $data) {
        if (is_null($data)) {
            $data = '';
        }
        $_where[] = $type . $column . ' = ' . ($this->escape($data, $escape));
        }
        $where = implode(' ' . $andOr . ' ', $_where);
        return $where;
    }

        /**
         * Get the SQL WHERE clause when operator argument is an array
         * @see DatabaseQueryBuilder::where
         *
         * @return string
         */
    protected function getWhereStrIfOperatorIsArray($where, array $op, $type = '', $escape = true) {
        $x = explode('?', $where);
        $w = '';
        foreach ($x as $k => $v) {
        if (!empty($v)) {
            if (isset($op[$k]) && is_null($op[$k])) {
                $op[$k] = '';
            }
            $w .= $type . $v . (isset($op[$k]) ? ($this->escape($op[$k], $escape)) : '');
        }
        }
        return $w;
    }

    /**
     * Get the default SQL WHERE clause using operator = or the operator argument
     * @see DatabaseQueryBuilder::where
     *
     * @return string
     */
    protected function getWhereStrForOperator($where, $op = null, $val = null, $type = '', $escape = true) {
        $w = '';
        if (!in_array((string) $op, $this->operatorList)) {
            if (is_null($op)) {
            $op = '';
            }
            $w = $type . $where . ' = ' . ($this->escape($op, $escape));
        } else {
            if (is_null($val)) {
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
        protected function setWhereStr($whereStr, $andOr = 'AND') {
        if (empty($this->where)) {
            $this->where = $whereStr;
        } else {
            if (substr(trim($this->where), -1) == '(') {
            $this->where = $this->where . ' ' . $whereStr;
            } else {
            $this->where = $this->where . ' ' . $andOr . ' ' . $whereStr;
            }
        }
        }


        /**
         * Set the SQL SELECT for function MIN, MAX, SUM, AVG, COUNT, AVG
         * @param  string $clause the clause type like MIN, MAX, etc.
         * @see  DatabaseQueryBuilder::min
         * @see  DatabaseQueryBuilder::max
         * @see  DatabaseQueryBuilder::sum
         * @see  DatabaseQueryBuilder::count
         * @see  DatabaseQueryBuilder::avg
         * @return object
         */
    protected function select_min_max_sum_count_avg($clause, $field, $name = null) {
        $clause = strtoupper($clause);
        $func = $clause . '(' . $field . ')' . (!is_null($name) ? ' AS ' . $name : '');
        $this->select = ($this->select == '*' ? $func : $this->select . ', ' . $func);
        return $this;
    }
}
